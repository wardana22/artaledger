<?php

use App\Domain\Accounting\Services\AccountSeederService;
use App\Domain\Banking\Services\BankReconciliationService;
use App\Domain\Banking\Services\BriCmsPdfParserService;
use App\Models\Account;
use App\Models\BankStatement;
use App\Models\BankStatementLine;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->company = Company::firstOrCreate(['code' => 'ALI'], ['name' => 'PT ARTA LEDGER INDONESIA']);
    $this->unit = Unit::firstOrCreate(['code' => 'KP'], ['name' => 'Kantor Pusat']);
    $this->user = User::factory()->create();
    $adminRole = Role::where('name', 'Super Admin')->first();
    if ($adminRole) {
        $this->user->assignRole($adminRole);
    }
    (new AccountSeederService)->seedFromData();
});

test('bri cms pdf parser extracts 191 lines with exact matching debit and credit totals', function () {
    $filePath = base_path('scratch/01. JANUARI 2025.pdf');
    if (! file_exists($filePath)) {
        $this->markTestSkipped('Sample file scratch/01. JANUARI 2025.pdf not found.');
    }

    $parser = new BriCmsPdfParserService;
    $result = $parser->parse($filePath);

    expect($result)->toHaveKeys(['metadata', 'lines']);
    expect($result['metadata']['account_number'])->toBe('1079-01-000382-30-5');
    expect($result['metadata']['account_holder'])->toBe('PT NUSA LIMA MEDIKA');
    expect($result['metadata']['period_start'])->toBe('2025-01-01');
    expect($result['metadata']['period_end'])->toBe('2025-01-31');

    // 191 baris transaksi
    expect(count($result['lines']))->toBe(191);

    $sumDebit = array_sum(array_column($result['lines'], 'debit'));
    $sumCredit = array_sum(array_column($result['lines'], 'credit'));

    expect($sumDebit)->toEqualWithDelta(10312478427.00, 0.01);
    expect($sumCredit)->toEqualWithDelta(10310079885.00, 0.01);
});

test('bank reconciliation service imports statement and saves lines to database', function () {
    $filePath = base_path('scratch/01. JANUARI 2025.pdf');
    if (! file_exists($filePath)) {
        $this->markTestSkipped('Sample file scratch/01. JANUARI 2025.pdf not found.');
    }

    $bankAccount = Account::where('code', '11.02.03')->first();
    if (! $bankAccount) {
        $bankAccount = Account::where('type', 'BANK')->first();
    }

    $service = app(BankReconciliationService::class);
    $statement = $service->importStatement($bankAccount->id, $filePath, '01. JANUARI 2025.pdf', $this->user);

    expect($statement)->toBeInstanceOf(BankStatement::class);
    expect($statement->lines()->count())->toBe(191);
    expect($statement->account_number)->toBe('1079-01-000382-30-5');
});

test('auto matching matches journal line with bank statement line', function () {
    $bankAccount = Account::where('code', '11.02.03')->first() ?? Account::first();
    $unit = Unit::first();

    $statement = BankStatement::create([
        'account_id' => $bankAccount->id,
        'bank_name' => 'BANK BRI',
        'account_number' => '1079-01-000382-30-5',
        'account_holder' => 'PT NUSA LIMA MEDIKA',
        'period_start' => '2025-01-01',
        'period_end' => '2025-01-31',
        'opening_balance' => 10000000,
        'total_debit' => 500000,
        'total_credit' => 1200000,
        'closing_balance' => 10700000,
        'status' => 'pending',
    ]);

    $stLine = BankStatementLine::create([
        'bank_statement_id' => $statement->id,
        'transaction_date' => '2025-01-15',
        'description' => 'TEST PENERIMAAN PASIEN VIA QRIS',
        'debit' => 0,
        'credit' => 750000, // Uang masuk di bank
        'balance' => 10750000,
        'match_status' => 'unmatched',
    ]);

    $company = Company::first();
    // Buat jurnal yang cocok di ArtaLedger: Uang masuk di bank = Debit Akun Bank
    $entry = JournalEntry::create([
        'company_id' => $company ? $company->id : 1,
        'entry_number' => 'JU-TEST-001',
        'entry_date' => '2025-01-15',
        'entry_type' => 'general',
        'status' => 'posted',
        'description' => 'Penerimaan QRIS Pasien',
    ]);

    $jLine = JournalLine::create([
        'journal_entry_id' => $entry->id,
        'account_id' => $bankAccount->id,
        'unit_id' => $unit ? $unit->id : 1,
        'debit' => 750000,
        'credit' => 0,
        'description' => 'Penerimaan QRIS',
    ]);

    $service = app(BankReconciliationService::class);
    $res = $service->autoMatch($statement, $this->user);

    expect($res['matched_count'])->toBe(1);
    $stLine->refresh();
    expect($stLine->match_status)->toBe('matched');
    expect($stLine->matched_journal_line_id)->toBe($jLine->id);
});

test('quick adjustment entry creates balanced journal and marks statement line adjusted', function () {
    $bankAccount = Account::where('code', '11.02.03')->first() ?? Account::first();
    $expenseAccount = Account::where('code', 'like', '5%')->first() ?? Account::first();

    $statement = BankStatement::create([
        'account_id' => $bankAccount->id,
        'bank_name' => 'BANK BRI',
        'account_number' => '1079-01-000382-30-5',
        'account_holder' => 'PT NUSA LIMA MEDIKA',
        'period_start' => '2025-01-01',
        'period_end' => '2025-01-31',
        'opening_balance' => 10000000,
        'total_debit' => 25000,
        'total_credit' => 0,
        'closing_balance' => 9975000,
        'status' => 'pending',
    ]);

    $stLine = BankStatementLine::create([
        'bank_statement_id' => $statement->id,
        'transaction_date' => '2025-01-31',
        'description' => 'Biaya Administrasi Buku Tabungan',
        'debit' => 25000, // Uang keluar di bank
        'credit' => 0,
        'balance' => 9975000,
        'match_status' => 'unmatched',
    ]);

    $service = app(BankReconciliationService::class);
    $adjEntry = $service->createQuickAdjustment($stLine->id, $expenseAccount->id, 'Beban Administrasi Bank Jan 2025', $this->user);

    expect($adjEntry)->toBeInstanceOf(JournalEntry::class);
    expect($adjEntry->status)->toBe('posted');

    $stLine->refresh();
    expect($stLine->match_status)->toBe('adjusted');
    expect($stLine->matched_journal_line_id)->not->toBeNull();
});

test('authenticated user can access bank reconciliation index page', function () {
    $this->actingAs($this->user)
        ->get(route('accounting.reconciliation.index'))
        ->assertOk()
        ->assertSee('Rekonsiliasi Bank Otomatis');
});

test('authenticated user can access bank reconciliation detail page', function () {
    $bankAccount = Account::where('code', '11.02.03')->first() ?? Account::first();

    $statement = BankStatement::create([
        'account_id' => $bankAccount->id,
        'bank_name' => 'BANK BRI',
        'account_number' => '1079-01-000382-30-5',
        'account_holder' => 'PT NUSA LIMA MEDIKA',
        'period_start' => '2025-01-01',
        'period_end' => '2025-01-31',
        'opening_balance' => 10000000,
        'total_debit' => 25000,
        'total_credit' => 0,
        'closing_balance' => 9975000,
        'status' => 'pending',
    ]);

    $this->actingAs($this->user)
        ->get(route('accounting.reconciliation.detail', $statement->id))
        ->assertOk()
        ->assertSee('Lembar Kerja Rekonsiliasi Bank');
});

test('authenticated user can export bank reconciliation pdf report', function () {
    $bankAccount = Account::where('code', '11.02.03')->first() ?? Account::first();

    $statement = BankStatement::create([
        'account_id' => $bankAccount->id,
        'bank_name' => 'BANK BRI',
        'account_number' => '1079-01-000382-30-5',
        'account_holder' => 'PT NUSA LIMA MEDIKA',
        'period_start' => '2025-01-01',
        'period_end' => '2025-01-31',
        'opening_balance' => 10000000,
        'total_debit' => 25000,
        'total_credit' => 0,
        'closing_balance' => 9975000,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('accounting.reconciliation.pdf', $statement->id));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    expect(strlen($response->getContent()))->toBeGreaterThan(500);
});
