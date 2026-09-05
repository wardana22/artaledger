<?php

use App\Domain\Accounting\Services\AccountSeederService;
use App\Domain\Banking\Services\BankReconciliationService;
use App\Livewire\Accounting\Reconciliation\BankReconciliationDetail;
use App\Models\Account;
use App\Models\BankStatement;
use App\Models\BankStatementLine;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;
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

    $this->bankAccount = Account::where('code', '11.02.03')->first();
    $this->statement = BankStatement::create([
        'account_id' => $this->bankAccount->id,
        'account_number' => '1079-01-000382-30-5',
        'account_holder' => 'PT NUSA LIMA MEDIKA',
        'period_start' => '2025-01-01',
        'period_end' => '2025-01-31',
        'opening_balance' => 100000000.00,
        'closing_balance' => 90000000.00,
        'total_debit' => 10000000.00,
        'total_credit' => 0.00,
        'status' => 'draft',
    ]);
});

test('bank line can be marked as opening outstanding without creating any new journal entry in GL', function () {
    $stLine = BankStatementLine::create([
        'bank_statement_id' => $this->statement->id,
        'transaction_date' => '2025-01-05',
        'description' => 'Penarikan Cek Des 2024 oleh Vendor',
        'debit' => 10000000.00,
        'credit' => 0.00,
        'balance' => 90000000.00,
        'match_status' => 'unmatched',
    ]);

    $initialJournalCount = JournalEntry::count();

    $service = app(BankReconciliationService::class);
    $service->markAsOpeningOutstanding($stLine->id, 'Cek Beredar Saldo Awal Des 2024', $this->user);

    $stLine->refresh();
    expect($stLine->match_status)->toBe('opening_reconciled');
    expect($stLine->notes)->toBe('Cek Beredar Saldo Awal Des 2024');

    // Pastikan 100% TIDAK ADA jurnal baru yang dibuat di buku besar
    expect(JournalEntry::count())->toBe($initialJournalCount);

    // Pastikan dihitung sebagai matched pada summary
    $summary = $service->calculateSummary($this->statement);
    expect($summary['total_matched_count'])->toBe(1);
    expect($summary['total_unmatched_count'])->toBe(0);
    expect($summary['reconciled_percentage'])->toBe(100.0);
});

test('unmatching an opening reconciled line cleanly reverts it back to unmatched', function () {
    $stLine = BankStatementLine::create([
        'bank_statement_id' => $this->statement->id,
        'transaction_date' => '2025-01-05',
        'description' => 'Penarikan Cek Des 2024',
        'debit' => 5000000.00,
        'credit' => 0.00,
        'balance' => 95000000.00,
        'match_status' => 'unmatched',
    ]);

    $service = app(BankReconciliationService::class);
    $service->markAsOpeningOutstanding($stLine->id, null, $this->user);

    $stLine->refresh();
    expect($stLine->match_status)->toBe('opening_reconciled');

    $service->unmatch($stLine->id);

    $stLine->refresh();
    expect($stLine->match_status)->toBe('unmatched');
});

test('unreconciled journal from previous month automatically carries over to next month statement workspace', function () {
    // 1. Buat Jurnal di Bulan Januari 2025 (Kredit Kas/Bank Rp 7.500.000)
    $janEntry = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-JAN-OUTSTANDING',
        'entry_date' => '2025-01-28',
        'description' => 'Pembayaran Tagihan Akhir Januari',
        'status' => 'posted',
    ]);

    $janLine = JournalLine::create([
        'journal_entry_id' => $janEntry->id,
        'account_id' => $this->bankAccount->id,
        'unit_id' => $this->unit->id,
        'debit' => 0,
        'credit' => 7500000.00,
        'description' => 'Pembayaran Tagihan Akhir Januari',
    ]);

    // 2. Buat Rekening Koran Bulan Februari 2025
    $febStatement = BankStatement::create([
        'account_id' => $this->bankAccount->id,
        'account_number' => '1079-01-000382-30-5',
        'account_holder' => 'PT NUSA LIMA MEDIKA',
        'period_start' => '2025-02-01',
        'period_end' => '2025-02-28',
        'opening_balance' => 90000000.00,
        'closing_balance' => 82500000.00,
        'total_debit' => 7500000.00,
        'total_credit' => 0.00,
        'status' => 'draft',
    ]);

    $febBankLine = BankStatementLine::create([
        'bank_statement_id' => $febStatement->id,
        'transaction_date' => '2025-02-03',
        'description' => 'Kliring Cek Pembayaran Akhir Jan',
        'debit' => 7500000.00,
        'credit' => 0.00,
        'balance' => 82500000.00,
        'match_status' => 'unmatched',
    ]);

    // 3. Uji tampilan lembar kerja Februari: Jurnal Januari HARUS muncul di daftar buku besar Februari!
    Livewire::actingAs($this->user)
        ->test(BankReconciliationDetail::class, ['statement' => $febStatement])
        ->assertSee('JU-JAN-OUTSTANDING')
        ->assertSee('Periode Lalu')
        ->call('toggleBankLine', $febBankLine->id)
        ->call('toggleBookLine', $janLine->id)
        ->assertSet('difference', 0.00)
        ->assertSet('isMatchValid', true)
        ->call('executeManualMatch')
        ->assertSee('Transaksi berhasil dicocokkan secara sempurna');

    $febBankLine->refresh();
    expect($febBankLine->match_status)->toBe('manual_matched');
});
