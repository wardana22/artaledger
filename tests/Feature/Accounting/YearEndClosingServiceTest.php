<?php

use App\Domain\Accounting\Services\YearEndClosingService;
use App\Livewire\Accounting\Periods\PeriodIndex;
use App\Livewire\Accounting\Reports\AgingReport;
use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->company = Company::firstOrCreate(['id' => 1], ['name' => 'PT Arta Ledger Test', 'code' => 'ALT']);

    $this->adminUser = User::factory()->create(['name' => 'Admin Keuangan']);
    $adminRole = Role::where('name', 'Super Admin')->first();
    if ($adminRole) {
        $this->adminUser->assignRole($adminRole);
    }

    // Buat Akun Kas, Piutang, Pendapatan, dan Saldo Laba
    $this->kasAccount = Account::firstOrCreate(['code' => '11.01'], [
        'company_id' => $this->company->id,
        'name' => 'Kas Operasional',
        'type' => 'asset',
        'normal_balance' => 'debit',
        'report_type' => 'neraca',
        'level' => 3,
        'status' => 'active',
    ]);

    $this->piutangAccount = Account::firstOrCreate(['code' => '11.04'], [
        'company_id' => $this->company->id,
        'name' => 'Piutang Usaha',
        'type' => 'PIUTANG',
        'normal_balance' => 'debit',
        'report_type' => 'neraca',
        'is_group' => false,
        'level' => 3,
        'status' => 'active',
    ]);

    $this->pendapatanAccount = Account::firstOrCreate(['code' => '41.01'], [
        'company_id' => $this->company->id,
        'name' => 'Pendapatan Jasa',
        'type' => 'revenue',
        'normal_balance' => 'credit',
        'report_type' => 'laba_rugi',
        'level' => 3,
        'status' => 'active',
    ]);

    $this->retainedEarningsAccount = Account::firstOrCreate(['code' => '31.02'], [
        'company_id' => $this->company->id,
        'name' => 'Saldo Laba / Laba Ditahan',
        'type' => 'equity',
        'normal_balance' => 'credit',
        'report_type' => 'neraca',
        'level' => 3,
        'status' => 'active',
    ]);
});

test('YearEndClosingService rolls over 2025 net profit and balance sheet into SA-2026-001', function () {
    // 1. Setup transaksi tahun 2025
    // Jurnal Pendapatan Piutang 2025: Piutang (Debit 5,000,000), Pendapatan (Kredit 5,000,000)
    $entry2025 = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-2025-001',
        'entry_date' => '2025-05-10',
        'description' => 'Pendapatan Jasa Piutang 2025',
        'status' => 'posted',
        'source_type' => 'general',
        'entry_type' => 'general',
    ]);

    JournalLine::create([
        'journal_entry_id' => $entry2025->id,
        'line_no' => 1,
        'account_id' => $this->piutangAccount->id,
        'unit_id' => null,
        'description' => 'Piutang Jasa',
        'debit' => 5000000,
        'credit' => 0,
    ]);

    JournalLine::create([
        'journal_entry_id' => $entry2025->id,
        'line_no' => 2,
        'account_id' => $this->pendapatanAccount->id,
        'unit_id' => null,
        'description' => 'Pendapatan Jasa',
        'debit' => 0,
        'credit' => 5000000,
    ]);

    // 2. Jalankan YearEndClosingService untuk tahun 2025
    $saEntry = YearEndClosingService::rolloverYearEndOpeningBalance(2025, $this->adminUser->id);

    expect($saEntry)->not->toBeNull();
    expect($saEntry->entry_number)->toBe('SA-2026-001');
    expect($saEntry->entry_type)->toBe('general');
    expect($saEntry->source_type)->toBe('opening_balance');
    expect($saEntry->status)->toBe('posted');

    // 3. Verifikasi baris-baris jurnal Saldo Awal 2026
    $lines = $saEntry->lines()->get();
    expect($lines->count())->toBe(2); // Piutang Usaha & Retained Earnings

    $piutangLine = $lines->where('account_id', $this->piutangAccount->id)->first();
    expect($piutangLine)->not->toBeNull();
    expect((float) $piutangLine->debit)->toBe(5000000.0);
    expect((float) $piutangLine->credit)->toBe(0.0);

    $retainedLine = $lines->where('account_id', $this->retainedEarningsAccount->id)->first();
    expect($retainedLine)->not->toBeNull();
    expect((float) $retainedLine->debit)->toBe(0.0);
    expect((float) $retainedLine->credit)->toBe(5000000.0); // Laba bersih 2025 dialokasikan ke modal/laba ditahan

    // Jurnal harus seimbang (Balanced)
    $totDebit = (float) $lines->sum('debit');
    $totCredit = (float) $lines->sum('credit');
    expect($totDebit)->toBe($totCredit);
    expect($totDebit)->toBe(5000000.0);

    // 4. Verifikasi pada Aging Report per 31 Januari 2026
    // Saldo awal 5,000,000 harus muncul di aging report dengan format Saldo Awal Piutang Usaha
    Livewire::actingAs($this->adminUser)
        ->test(AgingReport::class)
        ->set('activeTab', 'receivable')
        ->set('asOfDate', '2026-01-31')
        ->assertSee('Piutang Usaha')
        ->assertSee('5.000.000');
});

test('Closing December period automatically triggers year-end rollover in PeriodIndex component', function () {
    $decPeriod = AccountingPeriod::create([
        'company_id' => $this->company->id,
        'year' => 2025,
        'month' => 12,
        'start_date' => '2025-12-01',
        'end_date' => '2025-12-31',
        'status' => 'open',
    ]);

    // Transaksi Kas di 2025
    $entry2025 = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-2025-KAS',
        'entry_date' => '2025-12-15',
        'description' => 'Penerimaan Kas 2025',
        'status' => 'posted',
        'source_type' => 'general',
        'entry_type' => 'general',
    ]);

    JournalLine::create([
        'journal_entry_id' => $entry2025->id,
        'line_no' => 1,
        'account_id' => $this->kasAccount->id,
        'unit_id' => null,
        'description' => 'Kas Masuk',
        'debit' => 10000000,
        'credit' => 0,
    ]);

    JournalLine::create([
        'journal_entry_id' => $entry2025->id,
        'line_no' => 2,
        'account_id' => $this->pendapatanAccount->id,
        'unit_id' => null,
        'description' => 'Pendapatan',
        'debit' => 0,
        'credit' => 10000000,
    ]);

    // Tutup periode Desember 2025 melalui Livewire
    Livewire::actingAs($this->adminUser)
        ->test(PeriodIndex::class)
        ->call('closePeriod', $decPeriod->id)
        ->assertHasNoErrors();

    $decPeriod->refresh();
    expect($decPeriod->status)->toBe('closed');

    // Cek bahwa SA-2026-001 tercipta
    $saEntry = JournalEntry::where('entry_number', 'SA-2026-001')->first();
    expect($saEntry)->not->toBeNull();
    expect($saEntry->status)->toBe('posted');

    $kasLine = $saEntry->lines()->where('account_id', $this->kasAccount->id)->first();
    expect($kasLine)->not->toBeNull();
    expect((float) $kasLine->debit)->toBe(10000000.0);
});
