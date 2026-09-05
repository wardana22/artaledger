<?php

use App\Domain\Accounting\Services\AccountSeederService;
use App\Domain\Banking\Services\BankReconciliationService;
use App\Livewire\Accounting\Reconciliation\BankReconciliationDetail;
use App\Models\Account;
use App\Models\BankReconciliationMatchGroup;
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
        'closing_balance' => 150000000.00,
        'total_debit' => 50000000.00,
        'total_credit' => 100000000.00,
        'status' => 'draft',
    ]);
});

test('service allows 1 bank line to match 2 journal lines when amounts balance perfectly', function () {
    // 1 mutasi bank keluar: Rp 15.000.000
    $stLine = BankStatementLine::create([
        'bank_statement_id' => $this->statement->id,
        'transaction_date' => '2025-01-15',
        'description' => 'Transfer Pembayaran Vendor Batch',
        'debit' => 15000000.00,
        'credit' => 0.00,
        'balance' => 85000000.00,
        'match_status' => 'unmatched',
    ]);

    // 2 baris jurnal pengeluaran (kredit kas/bank): Rp 10.000.000 dan Rp 5.000.000
    $jEntry = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-TEST-MULTI-01',
        'entry_date' => '2025-01-15',
        'description' => 'Pembayaran Vendor 1 & 2',
        'status' => 'posted',
    ]);

    $jLine1 = JournalLine::create([
        'journal_entry_id' => $jEntry->id,
        'account_id' => $this->bankAccount->id,
        'unit_id' => $this->unit->id,
        'debit' => 0,
        'credit' => 10000000.00,
        'description' => 'Bayar Vendor 1',
    ]);

    $jLine2 = JournalLine::create([
        'journal_entry_id' => $jEntry->id,
        'account_id' => $this->bankAccount->id,
        'unit_id' => $this->unit->id,
        'debit' => 0,
        'credit' => 5000000.00,
        'description' => 'Bayar Vendor 2',
    ]);

    $service = app(BankReconciliationService::class);
    $group = $service->multiMatch([$stLine->id], [$jLine1->id, $jLine2->id], $this->user);

    expect($group)->toBeInstanceOf(BankReconciliationMatchGroup::class);
    expect((float) $group->total_bank_amount)->toEqualWithDelta(15000000.00, 0.01);
    expect((float) $group->total_book_amount)->toEqualWithDelta(15000000.00, 0.01);
    expect((float) $group->difference)->toEqualWithDelta(0.00, 0.01);
    expect($group->match_type)->toBe('manual_multi');

    // Status bank line harus manual_matched
    $stLine->refresh();
    expect($stLine->match_status)->toBe('manual_matched');
});

test('service allows 2 bank lines to match 1 journal line when amounts balance perfectly', function () {
    // 2 mutasi bank masuk: Rp 20.000.000 + Rp 30.000.000
    $stLine1 = BankStatementLine::create([
        'bank_statement_id' => $this->statement->id,
        'transaction_date' => '2025-01-20',
        'description' => 'Penerimaan Tagihan Bagian 1',
        'debit' => 0.00,
        'credit' => 20000000.00,
        'balance' => 120000000.00,
        'match_status' => 'unmatched',
    ]);

    $stLine2 = BankStatementLine::create([
        'bank_statement_id' => $this->statement->id,
        'transaction_date' => '2025-01-20',
        'description' => 'Penerimaan Tagihan Bagian 2',
        'debit' => 0.00,
        'credit' => 30000000.00,
        'balance' => 150000000.00,
        'match_status' => 'unmatched',
    ]);

    // 1 baris jurnal penerimaan (debit kas/bank): Rp 50.000.000
    $jEntry = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-TEST-MULTI-02',
        'entry_date' => '2025-01-20',
        'description' => 'Penerimaan Total Piutang',
        'status' => 'posted',
    ]);

    $jLine = JournalLine::create([
        'journal_entry_id' => $jEntry->id,
        'account_id' => $this->bankAccount->id,
        'unit_id' => $this->unit->id,
        'debit' => 50000000.00,
        'credit' => 0,
        'description' => 'Penerimaan Piutang Total',
    ]);

    $service = app(BankReconciliationService::class);
    $group = $service->multiMatch([$stLine1->id, $stLine2->id], [$jLine->id], $this->user);

    expect($group)->toBeInstanceOf(BankReconciliationMatchGroup::class);
    expect((float) $group->total_bank_amount)->toEqualWithDelta(50000000.00, 0.01);
    expect((float) $group->total_book_amount)->toEqualWithDelta(50000000.00, 0.01);
    expect((float) $group->difference)->toEqualWithDelta(0.00, 0.01);

    $stLine1->refresh();
    $stLine2->refresh();
    expect($stLine1->match_status)->toBe('manual_matched');
    expect($stLine2->match_status)->toBe('manual_matched');
});

test('service strictly rejects matching if amounts do not balance', function () {
    // 1 mutasi bank keluar: Rp 10.000.000
    $stLine = BankStatementLine::create([
        'bank_statement_id' => $this->statement->id,
        'transaction_date' => '2025-01-10',
        'description' => 'Pengeluaran',
        'debit' => 10000000.00,
        'credit' => 0.00,
        'balance' => 90000000.00,
        'match_status' => 'unmatched',
    ]);

    // 1 baris jurnal yang nilainya berbeda: Rp 8.000.000 (Selisih Rp 2.000.000)
    $jEntry = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-TEST-DIFF-01',
        'entry_date' => '2025-01-10',
        'description' => 'Pengeluaran',
        'status' => 'posted',
    ]);

    $jLine = JournalLine::create([
        'journal_entry_id' => $jEntry->id,
        'account_id' => $this->bankAccount->id,
        'unit_id' => $this->unit->id,
        'debit' => 0,
        'credit' => 8000000.00,
        'description' => 'Pengeluaran Rp 8jt',
    ]);

    $service = app(BankReconciliationService::class);

    // Harus melempar InvalidArgumentException karena ada selisih Rp 2.000.000
    expect(fn () => $service->multiMatch([$stLine->id], [$jLine->id], $this->user))
        ->toThrow(InvalidArgumentException::class);

    // Verifikasi status tidak berubah
    $stLine->refresh();
    expect($stLine->match_status)->toBe('unmatched');
});

test('service allows matching within tolerance of Rp 2', function () {
    $stLine = BankStatementLine::create([
        'bank_statement_id' => $this->statement->id,
        'transaction_date' => '2025-01-10',
        'description' => 'Pengeluaran Bank',
        'debit' => 10000000.00,
        'credit' => 0.00,
        'balance' => 90000000.00,
        'match_status' => 'unmatched',
    ]);

    $jEntry = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-TEST-TOLERANCE-2',
        'entry_date' => '2025-01-10',
        'description' => 'Pengeluaran Jurnal',
        'status' => 'posted',
    ]);

    $jLine = JournalLine::create([
        'journal_entry_id' => $jEntry->id,
        'account_id' => $this->bankAccount->id,
        'unit_id' => $this->unit->id,
        'debit' => 0,
        'credit' => 10000002.00,
        'description' => 'Jurnal Rp 10.000.002',
    ]);

    $service = app(BankReconciliationService::class);
    $group = $service->multiMatch([$stLine->id], [$jLine->id], $this->user);

    expect($group)->toBeInstanceOf(BankReconciliationMatchGroup::class);
    expect((float) $group->difference)->toEqualWithDelta(2.00, 0.01);

    $stLine->refresh();
    expect($stLine->match_status)->toBe('manual_matched');
});

test('service rejects matching when difference exceeds Rp 2 tolerance', function () {
    $stLine = BankStatementLine::create([
        'bank_statement_id' => $this->statement->id,
        'transaction_date' => '2025-01-10',
        'description' => 'Pengeluaran Bank',
        'debit' => 10000000.00,
        'credit' => 0.00,
        'balance' => 90000000.00,
        'match_status' => 'unmatched',
    ]);

    $jEntry = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-TEST-TOLERANCE-EXCEED',
        'entry_date' => '2025-01-10',
        'description' => 'Pengeluaran Jurnal',
        'status' => 'posted',
    ]);

    $jLine = JournalLine::create([
        'journal_entry_id' => $jEntry->id,
        'account_id' => $this->bankAccount->id,
        'unit_id' => $this->unit->id,
        'debit' => 0,
        'credit' => 10000002.50,
        'description' => 'Jurnal Rp 10.000.002,50',
    ]);

    $service = app(BankReconciliationService::class);

    expect(fn () => $service->multiMatch([$stLine->id], [$jLine->id], $this->user))
        ->toThrow(InvalidArgumentException::class);

    $stLine->refresh();
    expect($stLine->match_status)->toBe('unmatched');
});

test('unmatch cleanly restores all lines in a multi-match group', function () {
    $stLine1 = BankStatementLine::create([
        'bank_statement_id' => $this->statement->id,
        'transaction_date' => '2025-01-20',
        'description' => 'Line 1',
        'debit' => 5000000.00,
        'credit' => 0.00,
        'balance' => 95000000.00,
        'match_status' => 'unmatched',
    ]);
    $stLine2 = BankStatementLine::create([
        'bank_statement_id' => $this->statement->id,
        'transaction_date' => '2025-01-20',
        'description' => 'Line 2',
        'debit' => 5000000.00,
        'credit' => 0.00,
        'balance' => 90000000.00,
        'match_status' => 'unmatched',
    ]);

    $jEntry = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-TEST-UNMATCH',
        'entry_date' => '2025-01-20',
        'description' => 'Test',
        'status' => 'posted',
    ]);
    $jLine = JournalLine::create([
        'journal_entry_id' => $jEntry->id,
        'account_id' => $this->bankAccount->id,
        'unit_id' => $this->unit->id,
        'debit' => 0,
        'credit' => 10000000.00,
        'description' => 'Total 10jt',
    ]);

    $service = app(BankReconciliationService::class);
    $service->multiMatch([$stLine1->id, $stLine2->id], [$jLine->id], $this->user);

    expect(BankReconciliationMatchGroup::count())->toBeGreaterThan(0);

    // Batalkan dari stLine1
    $service->unmatch($stLine1->id);

    $stLine1->refresh();
    $stLine2->refresh();
    expect($stLine1->match_status)->toBe('unmatched');
    expect($stLine2->match_status)->toBe('unmatched');
});

test('livewire component calculates difference in real-time and blocks matching when difference exists', function () {
    $stLine = BankStatementLine::create([
        'bank_statement_id' => $this->statement->id,
        'transaction_date' => '2025-01-10',
        'description' => 'Bank Line Rp 10jt',
        'debit' => 10000000.00,
        'credit' => 0.00,
        'balance' => 90000000.00,
        'match_status' => 'unmatched',
    ]);

    $jEntry = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-LIVEWIRE-DIFF',
        'entry_date' => '2025-01-10',
        'description' => 'Book Line Rp 7jt',
        'status' => 'posted',
    ]);
    $jLine = JournalLine::create([
        'journal_entry_id' => $jEntry->id,
        'account_id' => $this->bankAccount->id,
        'unit_id' => $this->unit->id,
        'debit' => 0,
        'credit' => 7000000.00,
        'description' => 'Book Line Rp 7jt',
    ]);

    Livewire::actingAs($this->user)
        ->test(BankReconciliationDetail::class, ['statement' => $this->statement])
        ->call('toggleBankLine', $stLine->id)
        ->call('toggleBookLine', $jLine->id)
        ->assertSet('difference', 3000000.00)
        ->assertSet('isMatchValid', false)
        ->call('executeManualMatch')
        ->assertSee('Pencocokan ditolak: Masih terdapat selisih');
});
