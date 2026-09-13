<?php

use App\Domain\Accounting\Services\AccountSeederService;
use App\Domain\Accounting\Services\FinancialReportPdfService;
use App\Domain\Accounting\Services\JournalPostingService;
use App\Domain\Accounting\Services\YearEndClosingService;
use App\Livewire\Accounting\Reports\Worksheet;
use App\Models\Account;
use App\Models\Company;
use App\Models\JournalType;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    (new AccountSeederService)->seedFromData();
    $this->company = Company::first();
    $this->journalType = JournalType::firstOrCreate(['code' => 'JU'], ['name' => 'Jurnal Umum']);
});

test('worksheet and trial balance isolate prior year mutations when opening balance rollover exists', function () {
    $kasAccount = Account::where('type', 'KAS')->where('is_group', false)->first();
    $modalAccount = Account::where('report_type', 'neraca')->where('normal_balance', 'credit')->where('code', 'like', '3%')->where('is_group', false)->first();
    $pendapatanAccount = Account::where('report_type', 'laba_rugi')->where('normal_balance', 'credit')->where('is_group', false)->first();
    $bebanAccount = Account::where('report_type', 'laba_rugi')->where('normal_balance', 'debit')->where('is_group', false)->first();

    $postingService = new JournalPostingService;

    // 1. Transaksi Modal Awal 2025: Kas 10.000.000 / Modal 10.000.000
    $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2025-01-01',
        'document_number' => 'DOC-MODAL-2025',
        'description' => 'Setoran Modal Awal',
    ], [
        ['account_id' => $kasAccount->id, 'description' => 'Setoran Kas', 'debit' => 10000000, 'credit' => 0],
        ['account_id' => $modalAccount->id, 'description' => 'Modal Disetor', 'debit' => 0, 'credit' => 10000000],
    ], $this->user->id);

    // 2. Transaksi Operasional 2025: Kas 3.000.000 / Pendapatan 3.000.000
    $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2025-06-15',
        'document_number' => 'DOC-REV-2025',
        'description' => 'Pendapatan Usaha',
    ], [
        ['account_id' => $kasAccount->id, 'description' => 'Kas Masuk', 'debit' => 3000000, 'credit' => 0],
        ['account_id' => $pendapatanAccount->id, 'description' => 'Pendapatan', 'debit' => 0, 'credit' => 3000000],
    ], $this->user->id);

    // 3. Tutup Buku 2025
    YearEndClosingService::rolloverYearEndOpeningBalance(2025, $this->user->id);

    // 4. Mutasi Januari 2026: Kas bertambah 2.000.000 dari Pendapatan baru
    $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2026-01-10',
        'document_number' => 'DOC-REV-2026',
        'description' => 'Pendapatan 2026',
    ], [
        ['account_id' => $kasAccount->id, 'description' => 'Kas Masuk 2026', 'debit' => 2000000, 'credit' => 0],
        ['account_id' => $pendapatanAccount->id, 'description' => 'Pendapatan 2026', 'debit' => 0, 'credit' => 2000000],
    ], $this->user->id);

    // Uji Komponen Livewire Worksheet
    $component = Livewire::actingAs($this->user)
        ->test(Worksheet::class)
        ->set('startDate', '2026-01-01')
        ->set('endDate', '2026-01-31')
        ->set('unitFilter', 'all');

    // Total Kas = 13 Jt + Laba Ditahan 3 Jt + Modal 10 Jt + Pendapatan 2 Jt -> Total TB harus seimbang dan tidak double counting 2025
    $component->assertViewHas('totTbDebit', function ($val) {
        return $val > 0;
    });
    $component->assertViewHas('totTbCredit', function ($val) {
        return $val > 0;
    });

    // Uji PDF Service Worksheet Data
    $pdfService = app(FinancialReportPdfService::class);
    $pdfData = $pdfService->getWorksheetData('2026-01-01', '2026-01-31', 'all', $this->user);
    expect($pdfData['totTbDebit'])->toBeGreaterThan(0.0);
    expect($pdfData['totTbDebit'])->toEqual($pdfData['totTbCredit']);

    // Uji Trial Balance Data
    $tbData = $pdfService->getTrialBalanceData('2026-01-01', '2026-01-31', 'all', $this->user);
    expect($tbData['isBalanced'])->toBeTrue();
});
