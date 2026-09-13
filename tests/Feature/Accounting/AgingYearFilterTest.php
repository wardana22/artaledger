<?php

use App\Domain\Accounting\Services\AccountSeederService;
use App\Domain\Accounting\Services\AgingReportService;
use App\Domain\Accounting\Services\JournalPostingService;
use App\Models\Account;
use App\Models\Company;
use App\Models\JournalType;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    (new AccountSeederService)->seedFromData();
    $this->company = Company::first();
    $this->journalTypeJU = JournalType::firstOrCreate(['code' => 'JU'], ['name' => 'Jurnal Umum']);
    $this->journalTypeSA = JournalType::firstOrCreate(['code' => 'SA'], ['name' => 'Saldo Awal']);
});

test('aging report filters out unassigned transactions prior to startDate', function () {
    $piutangAccount = Account::where('type', 'PIUTANG')->where('is_group', false)->first();
    $balancingAccount = Account::where('report_type', 'laba_rugi')->where('is_group', false)->first()
        ?? Account::where('is_group', false)->where('id', '!=', $piutangAccount->id)->first();

    $postingService = new JournalPostingService;
    $reportService = new AgingReportService;

    // 1. Transaksi masa lalu tahun 2025 (tanpa invoice terdaftar)
    $journal2025 = $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2025-06-15',
        'document_number' => 'DOC-2025-OLD',
        'description' => 'Tagihan Masa Lalu 2025',
    ], [
        ['account_id' => $piutangAccount->id, 'description' => 'Piutang Lama 2025', 'debit' => 15000000, 'credit' => 0],
        ['account_id' => $balancingAccount->id, 'description' => 'Pendapatan Lama', 'debit' => 0, 'credit' => 15000000],
    ], $this->user->id);

    // 2. Transaksi tahun berjalan 2026 (tanpa invoice terdaftar)
    $journal2026 = $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2026-01-10',
        'document_number' => 'DOC-2026-NEW',
        'description' => 'Tagihan Baru Januari 2026',
    ], [
        ['account_id' => $piutangAccount->id, 'description' => 'Piutang Baru 2026', 'debit' => 25000000, 'credit' => 0],
        ['account_id' => $balancingAccount->id, 'description' => 'Pendapatan Baru', 'debit' => 0, 'credit' => 25000000],
    ], $this->user->id);

    // KASUS A: Tanpa filter startDate (Mencakup seluruh transaksi <= 31 Jan 2026)
    // Total = 15.000.000 (2025) + 25.000.000 (2026) = 40.000.000
    $reportAll = $reportService->getAgingReport('receivable', '2026-01-31', 'all', true, null);
    expect($reportAll['kpi']['total_outstanding'])->toBe(40000000.0);

    // KASUS B: Dengan filter startDate = '2026-01-01' (Hanya tahun berjalan 2026)
    // Transaksi 2025 otomatis tersaring/dieliminasi!
    // Total hanya = 25.000.000 (2026)
    $report2026 = $reportService->getAgingReport('receivable', '2026-01-31', 'all', true, '2026-01-01');
    expect($report2026['kpi']['total_outstanding'])->toBe(25000000.0);

    $accData = collect($report2026['accounts'])->firstWhere('account.id', $piutangAccount->id);
    expect($accData)->not->toBeNull();
    $invoices = collect($accData['invoices']);

    // Pastikan baris 2025 tidak muncul
    expect($invoices->firstWhere('entry_number', $journal2025->entry_number))->toBeNull();
    // Pastikan baris 2026 tetap muncul
    expect($invoices->firstWhere('entry_number', $journal2026->entry_number))->not->toBeNull();
});
