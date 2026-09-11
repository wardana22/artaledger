<?php

use App\Domain\Budget\Services\BudgetCalculationService;
use App\Domain\Budget\Services\BudgetGuardService;
use App\Models\Account;
use App\Models\Budget;
use App\Models\BudgetLine;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::create([
        'code' => 'PTALT',
        'name' => 'PT Arta Ledger Test',
        'fiscal_year_start' => 1,
    ]);

    $this->account = Account::create([
        'company_id' => $this->company->id,
        'code' => '5101',
        'name' => 'Beban Operasional Kantor',
        'type' => 'BEBAN',
        'normal_balance' => 'debit',
        'report_type' => 'laba_rugi',
        'is_group' => false,
        'is_active' => true,
    ]);

    $this->user = User::factory()->create();

    $this->budget = Budget::create([
        'fiscal_year' => 2027,
        'name' => 'Rencana Anggaran Biaya 2027',
        'status' => 'active',
        'enforcement_mode' => 'warning_only',
        'warning_threshold_pct' => 80.00,
        'created_by' => $this->user->id,
    ]);

    $this->budgetLine = BudgetLine::create([
        'budget_id' => $this->budget->id,
        'account_id' => $this->account->id,
        'unit_id' => null,
        'annual_amount' => 120000000, // 120 Juta / tahun = 10 Juta / bulan
        'm01_amount' => 10000000,
        'm02_amount' => 10000000,
        'm03_amount' => 10000000,
        'm04_amount' => 10000000,
        'm05_amount' => 10000000,
        'm06_amount' => 10000000,
        'm07_amount' => 10000000,
        'm08_amount' => 10000000,
        'm09_amount' => 10000000,
        'm10_amount' => 10000000,
        'm11_amount' => 10000000,
        'm12_amount' => 10000000,
    ]);
});

test('budget calculation service accurately compares budget and posted actual journals', function () {
    $service = new BudgetCalculationService;

    // 1. Initial state (no journals posted)
    $res = $service->calculateBudgetComparison($this->budget);
    expect($res['summary']['total_budget'])->toEqual(120000000.0);
    expect($res['summary']['total_actual'])->toEqual(0.0);
    expect($res['summary']['total_variance'])->toEqual(120000000.0);
    expect($res['items'][0]['status'])->toEqual('terkendali');

    // 2. Post a journal entry for Beban Operasional Kantor
    $entry = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-2027-001',
        'entry_date' => '2027-02-15',
        'description' => 'Pembayaran Sewa Kantor Februari',
        'status' => 'posted',
        'entry_type' => 'GENERAL',
    ]);

    JournalLine::create([
        'journal_entry_id' => $entry->id,
        'line_no' => 1,
        'account_id' => $this->account->id,
        'debit' => 85000000, // 85 Juta (70.83% of annual)
        'credit' => 0,
    ]);

    // Test annual calculation
    $resAnnual = $service->calculateBudgetComparison($this->budget);
    expect($resAnnual['summary']['total_actual'])->toEqual(85000000.0);
    expect($resAnnual['summary']['total_variance'])->toEqual(35000000.0);
    expect($resAnnual['items'][0]['absorption_rate'])->toEqual(70.83);
    expect($resAnnual['items'][0]['status'])->toEqual('terkendali');

    // Test month 2 calculation (Budget = 10 Juta, Actual = 85 Juta -> Over budget)
    $resFeb = $service->calculateBudgetComparison($this->budget, 2);
    expect($resFeb['summary']['total_budget'])->toEqual(10000000.0);
    expect($resFeb['summary']['total_actual'])->toEqual(85000000.0);
    expect($resFeb['summary']['total_variance'])->toEqual(-75000000.0);
    expect($resFeb['items'][0]['status'])->toEqual('melampaui');

    // Test Drill-down details
    $drillDown = $service->getAccountJournalDetails($this->account->id, 2027);
    expect($drillDown['lines'])->toHaveCount(1);
    expect($drillDown['net_actual'])->toEqual(85000000.0);
    expect($drillDown['lines'][0]['entry_number'])->toEqual('JU-2027-001');

    // Test status 'anomali' — ketika realisasi bernilai negatif (posting kredit lebih besar dari debit)
    // Simulasi: ada koreksi kredit besar sehingga net debit-credit menjadi negatif
    JournalLine::create([
        'journal_entry_id' => $entry->id,
        'line_no' => 2,
        'account_id' => $this->account->id,
        'debit' => 0,
        'credit' => 200000000, // Kredit 200 Juta > Debit 85 Juta → net negatif
    ]);

    $resAnomali = $service->calculateBudgetComparison($this->budget);
    // Net = 85_000_000 debit - 200_000_000 credit = -115_000_000 (negatif)
    expect($resAnomali['items'][0]['actual_amount'])->toBeLessThan(0);
    expect($resAnomali['items'][0]['absorption_rate'])->toBeLessThanOrEqual(0.0);
    expect($resAnomali['items'][0]['status'])->toEqual('anomali');
});

test('budget guard service evaluates spending and warns when threshold or limit is exceeded', function () {
    $guard = new BudgetGuardService;

    // 1. Spending safely within budget
    $evalSafe = $guard->evaluateSpending($this->account->id, 50000000, '2027-03-01');
    expect($evalSafe['has_budget'])->toBeTrue();
    expect($evalSafe['status'])->toEqual('safe');
    expect($evalSafe['can_proceed'])->toBeTrue();

    // 2. Spending that crosses warning threshold (>= 80%)
    // 100 Juta of 120 Juta is 83.33%
    $evalWarn = $guard->evaluateSpending($this->account->id, 100000000, '2027-03-01');
    expect($evalWarn['status'])->toEqual('warning');
    expect($evalWarn['warning_message'])->toContain('Peringatan Serapan');
    expect($evalWarn['can_proceed'])->toBeTrue();

    // 3. Spending that exceeds annual budget
    // 130 Juta of 120 Juta
    $evalOver = $guard->evaluateSpending($this->account->id, 130000000, '2027-03-01');
    expect($evalOver['status'])->toEqual('exceeded');
    expect($evalOver['warning_message'])->toContain('melampaui sisa pagu anggaran');
    expect($evalOver['can_proceed'])->toBeTrue(); // Warning only policy allows proceeding
});
