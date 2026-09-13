<?php

use App\Domain\Accounting\Services\AccountSeederService;
use App\Domain\Accounting\Services\AgingInvoiceManagerService;
use App\Domain\Accounting\Services\AgingReportService;
use App\Domain\Accounting\Services\JournalPostingService;
use App\Models\Account;
use App\Models\Company;
use App\Models\JournalLine;
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

test('simulasi cut-off aging 31 Januari 2026 atas tagihan tahun 2025 di saldo awal dan pelunasan Januari 2026', function () {
    $piutangAccount = Account::where('type', 'PIUTANG')->where('is_group', false)->first();
    $balancingAccount = Account::where('report_type', 'laba_rugi')->where('is_group', false)->first()
        ?? Account::where('is_group', false)->where('id', '!=', $piutangAccount->id)->first();
    $kasAccount = Account::where('code', '11.01.01')->first()
        ?? Account::where('type', 'like', '%KAS%')->where('is_group', false)->first()
        ?? Account::where('is_group', false)->where('id', '!=', $piutangAccount->id)->first();

    $postingService = new JournalPostingService;
    $managerService = new AgingInvoiceManagerService;
    $reportService = new AgingReportService;

    // 1. SETUP SALDO AWAL (01 Januari 2026): Saldo Piutang Rp 50.000.000 berasal dari akumulasi tagihan tahun 2025
    $saJournal = $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2026-01-01',
        'entry_type' => 'saldo_awal',
        'document_number' => 'SA-2026-001',
        'description' => 'Saldo Awal Neraca 1 Januari 2026',
    ], [
        ['account_id' => $piutangAccount->id, 'description' => 'Saldo Awal Piutang Usaha 2025', 'debit' => 50000000, 'credit' => 0],
        ['account_id' => $balancingAccount->id, 'description' => 'Laba Ditahan / Ekuitas', 'debit' => 0, 'credit' => 50000000],
    ], $this->user->id);

    $saPiutangLine = $saJournal->lines()->where('account_id', $piutangAccount->id)->first();
    expect($saPiutangLine)->not->toBeNull();

    // 2. PENDAFTARAN RINCIAN INVOICE TAHUN 2025:
    // Pecah baris saldo awal Rp 50.000.000 menjadi 2 faktur 2025:
    // - INV-2025-089: Rp 30.000.000 (Faktur 10 Nov 2025, Jatuh Tempo 10 Des 2025) -> per 31 Jan 2026 overdue 52 hari (Bucket 31-60 hari)
    // - INV-2025-104: Rp 20.000.000 (Faktur 20 Des 2025, Jatuh Tempo 20 Jan 2026) -> per 31 Jan 2026 overdue 11 hari (Bucket 1-30 hari)
    $createdInvoices = $managerService->splitJournalLineIntoInvoices($saPiutangLine, [
        [
            'invoice_number' => 'INV-2025-089',
            'original_amount' => 30000000,
            'invoice_date' => '2025-11-10',
            'due_date' => '2025-12-10',
            'partner_name' => 'PT Sumber Makmur 2025',
            'notes' => 'Tagihan Pengadaan November 2025',
        ],
        [
            'invoice_number' => 'INV-2025-104',
            'original_amount' => 20000000,
            'invoice_date' => '2025-12-20',
            'due_date' => '2026-01-20',
            'partner_name' => 'CV Berkah Abadi 2025',
            'notes' => 'Tagihan Jasa Desember 2025',
        ],
    ], $this->user->id);

    expect(count($createdInvoices))->toBe(2);
    $inv89 = $createdInvoices[0];
    $inv104 = $createdInvoices[1];

    // Cek posisi Aging SEBELUM ada pelunasan per 15 Januari 2026:
    // Total Outstanding = Rp 50.000.000
    $report15Jan = $reportService->getAgingReport('receivable', '2026-01-15');
    expect($report15Jan['kpi']['total_outstanding'])->toBe(50000000.0);

    // 3. TRANSAKSI PELUNASAN KAS DI BULAN JANUARI 2026:
    // Terjadi penerimaan kas pada 25 Januari 2026 sebesar Rp 30.000.000 untuk melunasi INV-2025-089
    $payJournal = $postingService->postManualEntry([
        'company_id' => $this->company->id,
        'entry_date' => '2026-01-25',
        'document_number' => 'BKM-2026-001',
        'description' => 'Penerimaan Kas Pelunasan Tagihan INV-2025-089',
    ], [
        ['account_id' => $kasAccount->id, 'description' => 'Penerimaan Kas Bank', 'debit' => 30000000, 'credit' => 0],
        ['account_id' => $piutangAccount->id, 'description' => 'Pelunasan Piutang PT Sumber Makmur', 'debit' => 0, 'credit' => 30000000],
    ], $this->user->id);

    $payPiutangLine = $payJournal->lines()->where('account_id', $piutangAccount->id)->first();
    expect($payPiutangLine)->not->toBeNull();

    // 4. SMART SETTLEMENT (Alokasikan pembayaran 25 Jan 2026 ke faktur saldo awal INV-2025-089)
    $managerService->settleInvoice(
        $inv89,
        $payPiutangLine,
        30000000,
        $this->user->id
    );

    // 5. EVALUASI LAPORAN AGING PER CUT-OFF 31 JANUARI 2026:
    $report31Jan = $reportService->getAgingReport('receivable', '2026-01-31');

    // A. Total Saldo Terbuka per 31 Jan 2026 harus Rp 20.000.000 (hanya sisa INV-2025-104)
    expect($report31Jan['kpi']['total_outstanding'])->toBe(20000000.0);

    // B. INV-2025-089 (Rp 30 Jt) sudah lunas, jadi tidak boleh muncul sebagai saldo terbuka
    $accountData = collect($report31Jan['accounts'])->firstWhere('account.id', $piutangAccount->id);
    expect($accountData)->not->toBeNull();
    $invoicesList = collect($accountData['invoices']);

    $rowInv89 = $invoicesList->firstWhere('invoice_number', 'INV-2025-089');
    expect($rowInv89)->toBeNull(); // Sudah lunas dan terfilter

    $rowInv104 = $invoicesList->firstWhere('invoice_number', 'INV-2025-104');
    expect($rowInv104)->not->toBeNull();
    expect($rowInv104['remaining_amount'])->toBe(20000000.0);
    // Due date 20 Jan 2026 per 31 Jan 2026 terlambat 11 hari -> masuk bucket overdue_1_30
    expect($rowInv104['buckets']['overdue_1_30'])->toBe(20000000.0);
    expect($report31Jan['kpi']['overdue_1_30'])->toBe(20000000.0);

    // C. KONSISTENSI BUKU BESAR (Saldo Buku Besar Piutang per 31 Jan 2026 harus klop Rp 20.000.000)
    $glDebit = (float) JournalLine::where('account_id', $piutangAccount->id)
        ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->where('entry_date', '<=', '2026-01-31'))
        ->sum('debit');
    $glCredit = (float) JournalLine::where('account_id', $piutangAccount->id)
        ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->where('entry_date', '<=', '2026-01-31'))
        ->sum('credit');
    $glBalance = $glDebit - $glCredit;

    expect($glBalance)->toBe(20000000.0);
    expect($report31Jan['kpi']['total_outstanding'])->toBe($glBalance);

    // 6. TIME-TRAVELING CUT-OFF TEST:
    // Jika cut-off dilihat per 20 Januari 2026 (sebelum tanggal bayar 25 Jan 2026):
    // Total outstanding harus tetap utuh Rp 50.000.000 karena pembayaran belum terjadi
    $report20Jan = $reportService->getAgingReport('receivable', '2026-01-20');
    expect($report20Jan['kpi']['total_outstanding'])->toBe(50000000.0);
});
