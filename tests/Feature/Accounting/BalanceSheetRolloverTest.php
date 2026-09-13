<?php

use App\Domain\Accounting\Services\AccountSeederService;
use App\Domain\Accounting\Services\FinancialReportPdfService;
use App\Domain\Accounting\Services\JournalPostingService;
use App\Domain\Accounting\Services\YearEndClosingService;
use App\Livewire\Accounting\Reports\BalanceSheet;
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

test('balance sheet is balanced before and after year-end closing rollover without double counting', function () {
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

    // 3. Transaksi Beban 2025: Beban 1.000.000 / Kas 1.000.000 -> Laba Bersih = 2.000.000
    $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2025-08-20',
        'document_number' => 'DOC-EXP-2025',
        'description' => 'Beban Operasional',
    ], [
        ['account_id' => $bebanAccount->id, 'description' => 'Beban', 'debit' => 1000000, 'credit' => 0],
        ['account_id' => $kasAccount->id, 'description' => 'Kas Keluar', 'debit' => 0, 'credit' => 1000000],
    ], $this->user->id);

    // Verifikasi Neraca 2025 (Per 31 Des 2025): Total Aset Kas = 12.000.000, Liab+Eq = 10.000.000 + Laba 2.000.000 = 12.000.000
    $comp2025 = Livewire::actingAs($this->user)->test(BalanceSheet::class, ['asOfDate' => '2025-12-31']);
    expect((float) $comp2025->get('totalAssets'))->toBe(12000000.0);
    expect((float) $comp2025->get('totalLiabilitiesAndEquity'))->toBe(12000000.0);
    expect($comp2025->get('isBalanced'))->toBeTrue();

    // 4. Eksekusi Tutup Buku 2025
    YearEndClosingService::rolloverYearEndOpeningBalance(2025, $this->user->id);

    // 5. Verifikasi Neraca 2026 (Per 31 Jan 2026): Aset TIDAK boleh double counting menjadi 24.000.000
    $comp2026 = Livewire::actingAs($this->user)->test(BalanceSheet::class, ['asOfDate' => '2026-01-31']);
    expect((float) $comp2026->get('totalAssets'))->toBe(12000000.0);
    expect((float) $comp2026->get('totalLiabilitiesAndEquity'))->toBe(12000000.0);
    expect((float) $comp2026->get('currentNetProfit'))->toBe(0.0); // Laba 2025 sudah masuk ke Saldo Laba
    expect($comp2026->get('isBalanced'))->toBeTrue();

    // 6. Verifikasi PDF Service juga seimbang
    $pdfService = new FinancialReportPdfService;
    $pdfData = $pdfService->getBalanceSheetData('2026-01-31', 'all', $this->user);
    expect((float) $pdfData['totalAssets'])->toBe(12000000.0);
    expect((float) $pdfData['totalLiabilitiesAndEquity'])->toBe(12000000.0);
    expect($pdfData['isBalanced'])->toBeTrue();
});
