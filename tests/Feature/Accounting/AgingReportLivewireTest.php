<?php

use App\Domain\Accounting\Services\AccountSeederService;
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
