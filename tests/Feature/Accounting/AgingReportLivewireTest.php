<?php

use App\Domain\Accounting\Services\AccountSeederService;
use App\Domain\Accounting\Services\AgingInvoiceManagerService;
use App\Domain\Accounting\Services\JournalPostingService;
use App\Livewire\Accounting\Reports\AgingReport;
use App\Models\Account;
use App\Models\Company;
use App\Models\JournalType;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    (new AccountSeederService)->seedFromData();
    $this->company = Company::first();
    $this->journalType = JournalType::firstOrCreate(['code' => 'JU'], ['name' => 'Jurnal Umum']);
});

test('can render aging report page and switch tabs', function () {
    Livewire::actingAs($this->user)
        ->test(AgingReport::class)
        ->assertOk()
        ->assertSee('Laporan Umur Piutang Usaha (AR Aging)')
        ->call('setTab', 'payable')
        ->assertSet('activeTab', 'payable')
        ->assertSee('Laporan Umur Hutang Usaha (AP Aging)');
});

test('can assign single invoice from modal and view in table', function () {
    $piutangAccount = Account::where('type', 'PIUTANG')->where('is_group', false)->first();
    $pendapatanAccount = Account::where('report_type', 'laba_rugi')->where('is_group', false)->first();

    $postingService = new JournalPostingService;
    $journal = $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2026-01-20',
        'document_number' => 'DOC-MODAL-TEST',
        'description' => 'Tagihan Client XYZ',
    ], [
        ['account_id' => $piutangAccount->id, 'description' => 'Piutang XYZ', 'debit' => 175000, 'credit' => 0],
        ['account_id' => $pendapatanAccount->id, 'description' => 'Pendapatan', 'debit' => 0, 'credit' => 175000],
    ], $this->user->id);

    $line = $journal->lines()->where('account_id', $piutangAccount->id)->first();

    Livewire::actingAs($this->user)
        ->test(AgingReport::class)
        ->set('asOfDate', '2026-03-01')
        ->call('openAssignModal', $line->id)
        ->assertSet('showAssignModal', true)
        ->set('singleInvoiceNumber', 'INV-XYZ-999')
        ->set('singleInvoiceDate', '2026-02-01')
        ->set('singleDueDate', '2026-02-28')
        ->set('singlePartnerName', 'Client XYZ Mega')
        ->call('saveSingleInvoice')
        ->assertHasNoErrors()
        ->assertSet('showAssignModal', false)
        ->call('toggleAccount', $piutangAccount->id)
        ->assertSee('INV-XYZ-999')
        ->assertSee('Client XYZ Mega');
});

test('can merge multiple journal lines into single invoice from livewire modal', function () {
    $piutangAccount = Account::where('type', 'PIUTANG')->where('is_group', false)->first();
    $pendapatanAccount = Account::where('report_type', 'laba_rugi')->where('is_group', false)->first();

    $postingService = new JournalPostingService;
    $j1 = $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2026-01-10',
        'document_number' => 'DOC-MERGE-1',
        'description' => 'Tagihan 1',
    ], [
        ['account_id' => $piutangAccount->id, 'description' => 'Jurnal 1', 'debit' => 100000, 'credit' => 0],
        ['account_id' => $pendapatanAccount->id, 'description' => 'Pendapatan', 'debit' => 0, 'credit' => 100000],
    ], $this->user->id);

    $j2 = $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2026-01-15',
        'document_number' => 'DOC-MERGE-2',
        'description' => 'Tagihan 2',
    ], [
        ['account_id' => $piutangAccount->id, 'description' => 'Jurnal 2', 'debit' => 200000, 'credit' => 0],
        ['account_id' => $pendapatanAccount->id, 'description' => 'Pendapatan', 'debit' => 0, 'credit' => 200000],
    ], $this->user->id);

    $line1 = $j1->lines()->where('account_id', $piutangAccount->id)->first();
    $line2 = $j2->lines()->where('account_id', $piutangAccount->id)->first();

    Livewire::actingAs($this->user)
        ->test(AgingReport::class)
        ->set("selectedLineIds.{$line1->id}", true)
        ->set("selectedLineIds.{$line2->id}", true)
        ->call('openMergeModal')
        ->assertSet('showAssignModal', true)
        ->assertSet('modalMode', 'merge')
        ->set('mergeInvoiceNumber', 'INV-MERGE-888')
        ->set('mergeInvoiceDate', '2026-02-01')
        ->set('mergeDueDate', '2026-02-28')
        ->set('mergePartnerName', 'Client Konsolidasi Livewire')
        ->call('saveMergedInvoice')
        ->assertHasNoErrors()
        ->assertSet('showAssignModal', false)
        ->call('toggleAccount', $piutangAccount->id)
        ->assertSee('INV-MERGE-888')
        ->assertSee('Client Konsolidasi Livewire');
});

test('can export aging report to pdf and excel', function () {
    $this->actingAs($this->user);

    $pdfResponse = $this->get(route('accounting.reports.export.pdf', [
        'type' => 'aging-receivable',
        'as_of_date' => '2026-03-01',
        'unit' => 'all',
    ]));
    $pdfResponse->assertOk();

    $excelResponse = $this->get(route('accounting.reports.export.excel', [
        'type' => 'aging-receivable',
        'as_of_date' => '2026-03-01',
        'unit' => 'all',
    ]));
    $excelResponse->assertOk();
});

test('can search and link payment line using smart payment line picker in settlement modal', function () {
    $piutangAccount = Account::where('type', 'PIUTANG')->where('is_group', false)->first();
    $pendapatanAccount = Account::where('report_type', 'laba_rugi')->where('is_group', false)->first();
    $kasAccount = Account::where('type', 'KAS')->where('is_group', false)->first();

    $postingService = new JournalPostingService;

    // 1. Jurnal Penjualan / Tagihan (Debit Piutang Rp 1.500.000)
    $journalTagihan = $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2026-01-10',
        'document_number' => 'DO-SMART-PICKER',
        'description' => 'Tagihan Penjualan Jasa',
    ], [
        ['account_id' => $piutangAccount->id, 'description' => 'Piutang Jasa', 'debit' => 1500000, 'credit' => 0],
        ['account_id' => $pendapatanAccount->id, 'description' => 'Pendapatan Jasa', 'debit' => 0, 'credit' => 1500000],
    ], $this->user->id);

    $lineTagihan = $journalTagihan->lines()->where('account_id', $piutangAccount->id)->first();
    $managerService = new AgingInvoiceManagerService;
    $invoice = $managerService->assignSingleInvoice($lineTagihan, [
        'invoice_number' => 'INV-PICKER-001',
        'invoice_date' => '2026-01-10',
        'due_date' => '2026-01-30',
        'partner_name' => 'RS Citra Medika',
    ], $this->user->id);

    // 2. Jurnal Pembayaran Penerimaan Bank (Kredit Piutang Rp 1.500.000)
    $journalBayar = $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2026-01-25',
        'document_number' => 'JU-2026-PAY-888',
        'description' => 'Penerimaan Transfer Bank Rek RS Citra Medika',
    ], [
        ['account_id' => $kasAccount->id, 'description' => 'Kas Masuk Bank', 'debit' => 1500000, 'credit' => 0],
        ['account_id' => $piutangAccount->id, 'description' => 'Pelunasan Transfer RS Citra', 'debit' => 0, 'credit' => 1500000],
    ], $this->user->id);

    $lineBayar = $journalBayar->lines()->where('account_id', $piutangAccount->id)->first();

    // 3. Test Livewire: Buka Modal Pelunasan, cari via search query, pilih, dan simpan
    Livewire::actingAs($this->user)
        ->test(AgingReport::class)
        ->call('openSettleModal', $invoice->id)
        ->assertSet('showSettleModal', true)
        ->set('settleMode', 'existing')
        ->set('settleSearchQuery', 'PAY-888')
        ->assertSee('JU-2026-PAY-888')
        ->call('selectPaymentLine', $lineBayar->id, 1500000)
        ->assertSet('settlePaymentLineId', $lineBayar->id)
        ->assertSet('settleAmount', 1500000.0)
        ->call('saveSettlement')
        ->assertHasNoErrors()
        ->assertSet('showSettleModal', false);

    // Verifikasi invoice lunas
    expect($invoice->fresh()->status)->toBe('paid');
    expect((float) $invoice->fresh()->remaining_amount)->toBe(0.0);
});
