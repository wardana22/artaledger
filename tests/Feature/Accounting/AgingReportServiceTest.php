<?php

use App\Domain\Accounting\Services\AccountSeederService;
use App\Domain\Accounting\Services\AgingInvoiceManagerService;
use App\Domain\Accounting\Services\AgingReportService;
use App\Domain\Accounting\Services\JournalPostingService;
use App\Models\Account;
use App\Models\ApArInvoice;
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
    $this->journalType = JournalType::firstOrCreate(['code' => 'JU'], ['name' => 'Jurnal Umum']);
});

test('can assign single invoice and calculate aging report correctly', function () {
    $piutangAccount = Account::where('type', 'PIUTANG')->where('is_group', false)->first();
    $pendapatanAccount = Account::where('report_type', 'laba_rugi')->where('is_group', false)->first();

    $postingService = new JournalPostingService;
    $journal = $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2026-01-15',
        'document_number' => 'DOC-001',
        'description' => 'Tagihan Jasa Januari',
    ], [
        ['account_id' => $piutangAccount->id, 'description' => 'Piutang PT ABC', 'debit' => 150000, 'credit' => 0],
        ['account_id' => $pendapatanAccount->id, 'description' => 'Pendapatan Jasa', 'debit' => 0, 'credit' => 150000],
    ], $this->user->id);

    $piutangLine = $journal->lines()->where('account_id', $piutangAccount->id)->first();

    // Assign invoice menyusul
    $managerService = new AgingInvoiceManagerService;
    $invoice = $managerService->assignSingleInvoice($piutangLine, [
        'invoice_number' => 'INV-2026-001',
        'invoice_date' => '2026-02-01',
        'due_date' => '2026-02-15',
        'partner_name' => 'PT ABC Sukses',
    ], $this->user->id);

    expect($invoice->invoice_number)->toBe('INV-2026-001');
    expect((float) $invoice->original_amount)->toBe(150000.0);

    // Aging report per 2026-03-01 (14 hari setelah due date 2026-02-15 -> bucket overdue_1_30)
    $reportService = new AgingReportService;
    $report = $reportService->getAgingReport('receivable', '2026-03-01');

    expect($report['kpi']['total_outstanding'])->toBe(150000.0);
    expect($report['kpi']['overdue_1_30'])->toBe(150000.0);
});

test('can split single journal line into multiple invoices and settle specific invoice', function () {
    $piutangAccount = Account::where('type', 'PIUTANG')->where('is_group', false)->first();
    $pendapatanAccount = Account::where('report_type', 'laba_rugi')->where('is_group', false)->first();
    $kasAccount = Account::where('code', '11.01.01')->first() ?? Account::where('type', 'like', '%KAS%')->where('is_group', false)->first();

    $postingService = new JournalPostingService;
    $journal = $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2026-01-31',
        'document_number' => 'DOC-SPLIT',
        'description' => 'Tagihan Gabungan 400.000',
    ], [
        ['account_id' => $piutangAccount->id, 'description' => 'Piutang Gabungan', 'debit' => 400000, 'credit' => 0],
        ['account_id' => $pendapatanAccount->id, 'description' => 'Pendapatan', 'debit' => 0, 'credit' => 400000],
    ], $this->user->id);

    $piutangLine = $journal->lines()->where('account_id', $piutangAccount->id)->first();

    // Pecah jadi 2 invoice: INV-A (250.000) & INV-B (150.000)
    $managerService = new AgingInvoiceManagerService;
    $splitInvoices = $managerService->splitJournalLineIntoInvoices($piutangLine, [
        [
            'invoice_number' => 'INV-2026-A',
            'invoice_date' => '2026-02-01',
            'due_date' => '2026-02-10',
            'original_amount' => 250000,
            'partner_name' => 'Klien Alpha',
        ],
        [
            'invoice_number' => 'INV-2026-B',
            'invoice_date' => '2026-02-01',
            'due_date' => '2026-02-28',
            'original_amount' => 150000,
            'partner_name' => 'Klien Beta',
        ],
    ], $this->user->id);

    expect(count($splitInvoices))->toBe(2);

    // Pelunasan khusus INV-B (Rp 150.000) di bulan Maret
    $paymentJournal = $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2026-03-05',
        'document_number' => 'PAY-INV-B',
        'description' => 'Pelunasan Khusus INV-B',
    ], [
        ['account_id' => $kasAccount->id, 'description' => 'Penerimaan Kas', 'debit' => 150000, 'credit' => 0],
        ['account_id' => $piutangAccount->id, 'description' => 'Pelunasan INV-B', 'debit' => 0, 'credit' => 150000],
    ], $this->user->id);

    $paymentLine = $paymentJournal->lines()->where('account_id', $piutangAccount->id)->first();
    $invB = $splitInvoices[1];

    $managerService->settleInvoice($invB, $paymentLine, 150000, $this->user->id);

    expect($invB->fresh()->status)->toBe('paid');
    expect($invB->fresh()->remaining_amount)->toBe(0.0);

    // Verifikasi laporan aging per 31 Maret: Sisa hanya INV-A senilai 250.000
    $reportService = new AgingReportService;
    $report = $reportService->getAgingReport('receivable', '2026-03-31');

    expect($report['kpi']['total_outstanding'])->toBe(250000.0);

    // Pastikan INV-B tidak lagi muncul di outstanding aktif
    $accountData = collect($report['accounts'])->firstWhere('account.id', $piutangAccount->id);
    $activeInvoices = collect($accountData['invoices'])->pluck('invoice_number')->all();
    expect($activeInvoices)->toContain('INV-2026-A');
    expect($activeInvoices)->not->toContain('INV-2026-B');
});

test('can consolidate multiple journal lines into single invoice (Many to 1)', function () {
    $piutangAccount = Account::where('type', 'PIUTANG')->where('is_group', false)->first();
    $pendapatanAccount = Account::where('report_type', 'laba_rugi')->where('is_group', false)->first();

    $postingService = new JournalPostingService;

    // Jurnal 1: DO Tahap 1 (Rp 300.000)
    $journal1 = $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2026-01-10',
        'document_number' => 'DO-01',
        'description' => 'Pengiriman Tahap 1',
    ], [
        ['account_id' => $piutangAccount->id, 'description' => 'Piutang DO 1', 'debit' => 300000, 'credit' => 0],
        ['account_id' => $pendapatanAccount->id, 'description' => 'Pendapatan', 'debit' => 0, 'credit' => 300000],
    ], $this->user->id);

    // Jurnal 2: DO Tahap 2 (Rp 200.000)
    $journal2 = $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2026-01-25',
        'document_number' => 'DO-02',
        'description' => 'Pengiriman Tahap 2',
    ], [
        ['account_id' => $piutangAccount->id, 'description' => 'Piutang DO 2', 'debit' => 200000, 'credit' => 0],
        ['account_id' => $pendapatanAccount->id, 'description' => 'Pendapatan', 'debit' => 0, 'credit' => 200000],
    ], $this->user->id);

    $line1 = $journal1->lines()->where('account_id', $piutangAccount->id)->first();
    $line2 = $journal2->lines()->where('account_id', $piutangAccount->id)->first();

    // Gabungkan kedua jurnal menjadi 1 Invoice Fisik (Rp 500.000)
    $managerService = new AgingInvoiceManagerService;
    $consolidatedInvoice = $managerService->consolidateJournalLinesIntoInvoice(
        [$line1->id, $line2->id],
        [
            'invoice_number' => 'INV-2026-GABUNGAN',
            'invoice_date' => '2026-02-02',
            'due_date' => '2026-02-28',
            'partner_name' => 'Klien Konsolidasi',
            'notes' => 'Gabungan DO-01 & DO-02',
        ],
        $this->user->id
    );

    expect((float) $consolidatedInvoice->original_amount)->toBe(500000.0);
    expect($consolidatedInvoice->journalLines()->count())->toBe(2);

    // Verifikasi laporan aging
    $reportService = new AgingReportService;
    $report = $reportService->getAgingReport('receivable', '2026-03-01');

    expect($report['kpi']['total_outstanding'])->toBe(500000.0);

    $accData = collect($report['accounts'])->firstWhere('account.id', $piutangAccount->id);
    $invoices = collect($accData['invoices']);
    $item = $invoices->firstWhere('invoice_number', 'INV-2026-GABUNGAN');

    expect($item)->not->toBeNull();
    expect($item['remaining_amount'])->toBe(500000.0);
    expect($item['entry_number'])->toContain('JU-'); // Memuat nomor jurnal terkait
});

test('can consolidate multiple journal lines into multiple invoices (Many to Many / M:N)', function () {
    $piutangAccount = Account::where('type', 'PIUTANG')->where('is_group', false)->first();
    $pendapatanAccount = Account::where('report_type', 'laba_rugi')->where('is_group', false)->first();

    $postingService = new JournalPostingService;

    // Jurnal 1: DO Tahap 1 (Rp 300.000)
    $journal1 = $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2026-01-10',
        'document_number' => 'DO-M1',
        'description' => 'Pengiriman Tahap 1 Multi',
    ], [
        ['account_id' => $piutangAccount->id, 'description' => 'Piutang DO 1', 'debit' => 300000, 'credit' => 0],
        ['account_id' => $pendapatanAccount->id, 'description' => 'Pendapatan', 'debit' => 0, 'credit' => 300000],
    ], $this->user->id);

    // Jurnal 2: DO Tahap 2 (Rp 200.000)
    $journal2 = $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2026-01-25',
        'document_number' => 'DO-M2',
        'description' => 'Pengiriman Tahap 2 Multi',
    ], [
        ['account_id' => $piutangAccount->id, 'description' => 'Piutang DO 2', 'debit' => 200000, 'credit' => 0],
        ['account_id' => $pendapatanAccount->id, 'description' => 'Pendapatan', 'debit' => 0, 'credit' => 200000],
    ], $this->user->id);

    $line1 = $journal1->lines()->where('account_id', $piutangAccount->id)->first();
    $line2 = $journal2->lines()->where('account_id', $piutangAccount->id)->first();

    // Gabungkan kedua jurnal (total Rp 500.000) menjadi 2 lembar Invoice:
    // Invoice A: Rp 350.000
    // Invoice B: Rp 150.000
    $managerService = new AgingInvoiceManagerService;
    $createdInvoices = $managerService->consolidateJournalLinesIntoMultipleInvoices(
        [$line1->id, $line2->id],
        [
            [
                'invoice_number' => 'INV-MULTI-A',
                'original_amount' => 350000,
                'invoice_date' => '2026-02-01',
                'due_date' => '2026-02-15',
                'partner_name' => 'Klien M:N',
            ],
            [
                'invoice_number' => 'INV-MULTI-B',
                'original_amount' => 150000,
                'invoice_date' => '2026-02-01',
                'due_date' => '2026-02-28',
                'partner_name' => 'Klien M:N',
            ],
        ],
        $this->user->id
    );

    expect(count($createdInvoices))->toBe(2);
    expect((float) $createdInvoices[0]->original_amount)->toBe(350000.0);
    expect((float) $createdInvoices[1]->original_amount)->toBe(150000.0);

    // Verifikasi alokasi pivot M:N
    $totalAllocatedPivot = DB::table('ap_ar_invoice_journal_lines')
        ->whereIn('ap_ar_invoice_id', collect($createdInvoices)->pluck('id'))
        ->sum('allocated_amount');

    expect((float) $totalAllocatedPivot)->toBe(500000.0);

    // Verifikasi laporan aging
    $reportService = new AgingReportService;
    $report = $reportService->getAgingReport('receivable', '2026-03-01');

    $accData = collect($report['accounts'])->firstWhere('account.id', $piutangAccount->id);
    $invoices = collect($accData['invoices']);

    expect($invoices->firstWhere('invoice_number', 'INV-MULTI-A'))->not->toBeNull();
    expect($invoices->firstWhere('invoice_number', 'INV-MULTI-B'))->not->toBeNull();
});

test('can update invoice details and reclassify aging buckets', function () {
    $piutangAccount = Account::where('type', 'PIUTANG')->where('is_group', false)->first();
    $pendapatanAccount = Account::where('report_type', 'laba_rugi')->where('is_group', false)->first();

    $postingService = new JournalPostingService;
    $journal = $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2026-01-01',
        'document_number' => 'DO-UPD-1',
        'description' => 'Penjualan Edit Test',
    ], [
        ['account_id' => $piutangAccount->id, 'description' => 'Tagihan Awal', 'debit' => 1000000, 'credit' => 0],
        ['account_id' => $pendapatanAccount->id, 'description' => 'Pendapatan', 'debit' => 0, 'credit' => 1000000],
    ], $this->user->id);

    $line = $journal->lines()->where('account_id', $piutangAccount->id)->first();
    $managerService = new AgingInvoiceManagerService;

    $invoice = $managerService->assignSingleInvoice($line, [
        'invoice_number' => 'INV-SALAH-INPUT',
        'invoice_date' => '2026-01-01',
        'due_date' => '2026-01-10',
        'partner_name' => 'PT Mitra Salah',
        'notes' => 'Catatan awal',
    ], $this->user->id);

    expect($invoice->invoice_number)->toBe('INV-SALAH-INPUT');
    expect($invoice->partner_name)->toBe('PT Mitra Salah');

    // Lakukan pembaruan / koreksi
    $updated = $managerService->updateInvoice($invoice, [
        'invoice_number' => 'INV-BENAR-001',
        'partner_name' => 'PT Mitra Sejati',
        'due_date' => '2025-12-01', // Diundur jatuh temponya agar masuk overdue > 60 hari pada cutoff Jan 2026
        'notes' => 'Catatan revisi',
    ], $this->user->id);

    expect($updated->invoice_number)->toBe('INV-BENAR-001');
    expect($updated->partner_name)->toBe('PT Mitra Sejati');
    expect($updated->due_date->format('Y-m-d'))->toBe('2025-12-01');

    // Verifikasi bucket aging otomatis terpengaruh
    $reportService = new AgingReportService;
    $report = $reportService->getAgingReport('receivable', '2026-01-15');
    $accData = collect($report['accounts'])->firstWhere('account.id', $piutangAccount->id);
    $item = collect($accData['invoices'])->firstWhere('invoice_number', 'INV-BENAR-001');

    expect($item)->not->toBeNull();
    expect($item['buckets']['overdue_31_60'])->toBe(1000000.0);
});

test('can unlink invoice to restore journal line as unassigned', function () {
    $piutangAccount = Account::where('type', 'PIUTANG')->where('is_group', false)->first();
    $pendapatanAccount = Account::where('report_type', 'laba_rugi')->where('is_group', false)->first();

    $postingService = new JournalPostingService;
    $journal = $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2026-01-05',
        'document_number' => 'DO-UNLINK',
        'description' => 'Penjualan Unlink Test',
    ], [
        ['account_id' => $piutangAccount->id, 'description' => 'Tagihan Unlink', 'debit' => 750000, 'credit' => 0],
        ['account_id' => $pendapatanAccount->id, 'description' => 'Pendapatan', 'debit' => 0, 'credit' => 750000],
    ], $this->user->id);

    $line = $journal->lines()->where('account_id', $piutangAccount->id)->first();
    $managerService = new AgingInvoiceManagerService;

    $invoice = $managerService->assignSingleInvoice($line, [
        'invoice_number' => 'INV-UNLINK-ME',
        'invoice_date' => '2026-01-05',
        'due_date' => '2026-01-20',
    ], $this->user->id);

    expect(ApArInvoice::where('invoice_number', 'INV-UNLINK-ME')->exists())->toBeTrue();

    // Batalkan penugasan invoice (Unlink)
    $managerService->unlinkInvoice($invoice, $this->user->id);

    expect(ApArInvoice::where('invoice_number', 'INV-UNLINK-ME')->exists())->toBeFalse();

    // Laporan aging harus menunjukkan baris ini kembali ke status belum terdaftar
    $reportService = new AgingReportService;
    $report = $reportService->getAgingReport('receivable', '2026-01-30');
    $accData = collect($report['accounts'])->firstWhere('account.id', $piutangAccount->id);
    $item = collect($accData['invoices'])->firstWhere('id', $line->id);

    expect($item)->not->toBeNull();
    expect($item['is_registered_invoice'])->toBeFalse();
    expect($item['invoice_number'])->toBe('(Belum Bernomor Invoice)');
});
