<?php

namespace App\Domain\Accounting\Services;

use App\Models\Company;
use App\Models\Unit;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FinancialReportExcelService
{
    public function __construct(
        protected FinancialReportPdfService $pdfService
    ) {}

    /**
     * Terapkan header perusahaan dan judul laporan.
     */
    protected function applyReportHeader(
        Spreadsheet $spreadsheet,
        mixed $company,
        string $title,
        string $period,
        string $unitName,
        string $lastCol = 'E'
    ): int {
        $sheet = $spreadsheet->getActiveSheet();

        $companyName = $company->name ?? 'PT ArtaLedger Enterprise';

        $sheet->setCellValue('A1', $companyName);
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->setCellValue('A2', strtoupper($title));
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->getStyle('A2')->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->setCellValue('A3', 'Periode: '.$period.' | Unit: '.$unitName);
        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->getStyle('A3')->getFont()->setSize(10)->setItalic(true)->getColor()->setRGB('64748B');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        return 5; // Baris awal tabel data
    }

    /**
     * Terapkan blok tanda tangan di bagian bawah lembar kerja Excel.
     */
    protected function applySignatures(
        Spreadsheet $spreadsheet,
        mixed $company,
        int $startRow,
        string $leftCol = 'A',
        string $midCol = 'C',
        string $rightCol = 'E'
    ): void {
        $sheet = $spreadsheet->getActiveSheet();

        $prepName = $company?->prepared_by_name ?: 'Staff Akuntansi';
        $prepTitle = $company?->prepared_by_title ?: 'Bagian Keuangan & Akuntansi';
        $revName = $company?->reviewed_by_name ?: 'Manager Akuntansi';
        $revTitle = $company?->reviewed_by_title ?: 'Accounting & Tax Lead';
        $appName = $company?->approved_by_name ?: 'Direktur Keuangan';
        $appTitle = $company?->approved_by_title ?: 'Chief Financial Officer (CFO)';

        $row = $startRow + 2;

        $sheet->setCellValue("{$leftCol}{$row}", 'Disusun Oleh:');
        $sheet->setCellValue("{$midCol}{$row}", 'Diperiksa Oleh:');
        $sheet->setCellValue("{$rightCol}{$row}", 'Disetujui Oleh:');
        $sheet->getStyle("{$leftCol}{$row}:{$rightCol}{$row}")->getFont()->setSize(9)->setBold(true)->getColor()->setRGB('475569');

        $row += 4;

        $sheet->setCellValue("{$leftCol}{$row}", $prepName);
        $sheet->setCellValue("{$midCol}{$row}", $revName);
        $sheet->setCellValue("{$rightCol}{$row}", $appName);
        $sheet->getStyle("{$leftCol}{$row}:{$rightCol}{$row}")->getFont()->setSize(10)->setBold(true)->setUnderline(true);

        $row += 1;

        $sheet->setCellValue("{$leftCol}{$row}", $prepTitle);
        $sheet->setCellValue("{$midCol}{$row}", $revTitle);
        $sheet->setCellValue("{$rightCol}{$row}", $appTitle);
        $sheet->getStyle("{$leftCol}{$row}:{$rightCol}{$row}")->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');
    }

    /**
     * Auto size kolom lembar kerja.
     *
     * @param  array<int, string>  $columns
     */
    protected function autoSizeColumns(Spreadsheet $spreadsheet, array $columns): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($columns as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * 1. Export Laporan Laba Rugi
     */
    public function exportProfitLoss(string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): Spreadsheet
    {
        $data = $this->pdfService->getProfitLossData($startDate, $endDate, $unitFilter, $user);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laba Rugi');

        $periodStr = $data['startDate'].' - '.$data['endDate'];
        $row = $this->applyReportHeader($spreadsheet, $data['company'], 'Laporan Laba Rugi', $periodStr, $data['unitName'], 'D');

        // Header Table
        $sheet->setCellValue("A{$row}", 'Kode');
        $sheet->setCellValue("B{$row}", 'Nama Akun');
        $sheet->setCellValue("C{$row}", 'Rincian');
        $sheet->setCellValue("D{$row}", 'Total');

        $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $sheet->getStyle("A{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
        $currencyFormat = '#,##0.00;(#,##0.00);"-"';

        foreach ($data['rows'] as $r) {
            $acc = $r['account'];
            $indent = str_repeat('    ', (int) max(0, $r['level'] - 1));

            $sheet->setCellValue("A{$row}", $acc->code);
            $sheet->setCellValue("B{$row}", $indent.$acc->name);

            if ($r['rincian'] !== null) {
                $sheet->setCellValue("C{$row}", $r['rincian']);
                $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            }
            if ($r['total'] !== null) {
                $sheet->setCellValue("D{$row}", $r['total']);
                $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            }

            if ($r['has_children']) {
                $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
            }

            $row++;
        }

        // Summary Lines
        $summaries = [
            ['Laba / (Rugi) Kotor', $data['grossProfit']],
            ['Laba / (Rugi) Operasional', $data['operatingProfit']],
            ['Laba / (Rugi) Sebelum Pajak', $data['profitBeforeTax']],
            ['Laba / (Rugi) Setelah Pajak', $data['netProfitAfterTax'] ?? $data['netProfit']],
        ];

        if (abs($data['comprehensiveIncome'] ?? 0) > 0.001) {
            $summaries[] = ['Pendapatan / (Beban) Komprehensif Lain', $data['comprehensiveIncome']];
            $summaries[] = ['Total Laba / (Rugi) Komprehensif Periode Berjalan', $data['totalComprehensiveIncome']];
        }

        $row++;
        foreach ($summaries as $sum) {
            $sheet->setCellValue("B{$row}", $sum[0]);
            $sheet->setCellValue("D{$row}", $sum[1]);
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $sheet->getStyle("B{$row}:D{$row}")->getFont()->setBold(true);
            $sheet->getStyle("B{$row}:D{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("B{$row}:D{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
            $row++;
        }

        $this->applySignatures($spreadsheet, $data['company'], $row, 'A', 'B', 'D');
        $this->autoSizeColumns($spreadsheet, ['A', 'B', 'C', 'D']);

        return $spreadsheet;
    }

    /**
     * 2. Export Neraca (Balance Sheet)
     */
    public function exportBalanceSheet(string $asOfDate, string $unitFilter = 'all', ?User $user = null): Spreadsheet
    {
        $data = $this->pdfService->getBalanceSheetData($asOfDate, $unitFilter, $user);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Neraca Keuangan');

        $row = $this->applyReportHeader($spreadsheet, $data['company'], 'Laporan Posisi Keuangan (Neraca)', 'Per '.$data['asOfDate'], $data['unitName'], 'D');

        $sheet->setCellValue("A{$row}", 'Kode');
        $sheet->setCellValue("B{$row}", 'Nama Akun');
        $sheet->setCellValue("C{$row}", 'Rincian');
        $sheet->setCellValue("D{$row}", 'Total');

        $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $sheet->getStyle("A{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
        $currencyFormat = '#,##0.00;(#,##0.00);"-"';

        // Aset
        $sheet->setCellValue("B{$row}", 'ASET');
        $sheet->getStyle("B{$row}")->getFont()->setBold(true);
        $row++;

        foreach ($data['assetRows'] as $r) {
            $acc = $r['account'];
            $indent = str_repeat('    ', (int) max(0, $r['level'] - 1));

            $sheet->setCellValue("A{$row}", $acc->code);
            $sheet->setCellValue("B{$row}", $indent.$acc->name);
            if (! $r['has_children']) {
                $sheet->setCellValue("C{$row}", $r['amount']);
                $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            } else {
                $sheet->setCellValue("D{$row}", $r['amount']);
                $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
                $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
            }
            $row++;
        }

        $sheet->setCellValue("B{$row}", 'TOTAL ASET');
        $sheet->setCellValue("D{$row}", $data['totalAssets']);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("B{$row}:D{$row}")->getFont()->setBold(true);
        $sheet->getStyle("B{$row}:D{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("B{$row}:D{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        $row += 2;

        // Kewajiban
        $sheet->setCellValue("B{$row}", 'KEWAJIBAN (LIABILITAS)');
        $sheet->getStyle("B{$row}")->getFont()->setBold(true);
        $row++;

        foreach ($data['liabilityRows'] as $r) {
            $acc = $r['account'];
            $indent = str_repeat('    ', (int) max(0, $r['level'] - 1));

            $sheet->setCellValue("A{$row}", $acc->code);
            $sheet->setCellValue("B{$row}", $indent.$acc->name);
            if (! $r['has_children']) {
                $sheet->setCellValue("C{$row}", $r['amount']);
                $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            } else {
                $sheet->setCellValue("D{$row}", $r['amount']);
                $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
                $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
            }
            $row++;
        }

        $sheet->setCellValue("B{$row}", 'TOTAL KEWAJIBAN');
        $sheet->setCellValue("D{$row}", $data['totalLiabilities']);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("B{$row}:D{$row}")->getFont()->setBold(true);
        $row += 2;

        // Ekuitas
        $sheet->setCellValue("B{$row}", 'EKUITAS');
        $sheet->getStyle("B{$row}")->getFont()->setBold(true);
        $row++;

        foreach ($data['equityRows'] as $r) {
            $acc = $r['account'];
            $indent = str_repeat('    ', (int) max(0, $r['level'] - 1));

            $sheet->setCellValue("A{$row}", $acc->code);
            $sheet->setCellValue("B{$row}", $indent.$acc->name);
            if (! $r['has_children']) {
                $sheet->setCellValue("C{$row}", $r['amount']);
                $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            } else {
                $sheet->setCellValue("D{$row}", $r['amount']);
                $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
                $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
            }
            $row++;
        }

        $sheet->setCellValue("B{$row}", 'TOTAL EKUITAS');
        $sheet->setCellValue("D{$row}", $data['totalEquity']);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("B{$row}:D{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("B{$row}", 'TOTAL KEWAJIBAN & EKUITAS');
        $sheet->setCellValue("D{$row}", $data['totalLiabilitiesAndEquity']);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("B{$row}:D{$row}")->getFont()->setBold(true);
        $sheet->getStyle("B{$row}:D{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("B{$row}:D{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        $row++;

        $this->applySignatures($spreadsheet, $data['company'], $row, 'A', 'B', 'D');
        $this->autoSizeColumns($spreadsheet, ['A', 'B', 'C', 'D']);

        return $spreadsheet;
    }

    /**
     * 3. Export Neraca Saldo (Trial Balance)
     */
    public function exportTrialBalance(string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): Spreadsheet
    {
        $data = $this->pdfService->getTrialBalanceData($startDate, $endDate, $unitFilter, $user);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Neraca Saldo');

        $periodStr = $data['startDate'].' - '.$data['endDate'];
        $row = $this->applyReportHeader($spreadsheet, $data['company'], 'Laporan Neraca Saldo (Trial Balance)', $periodStr, $data['unitName'], 'G');

        $headers = ['Kode Akun', 'Nama Akun', 'Saldo Awal', 'Mutasi Debet', 'Mutasi Kredit', 'Saldo Akhir Debet', 'Saldo Akhir Kredit'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

        foreach ($headers as $idx => $h) {
            $sheet->setCellValue("{$cols[$idx]}{$row}", $h);
        }

        $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $sheet->getStyle("A{$row}:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
        $currencyFormat = '#,##0.00;(#,##0.00);"-"';

        foreach ($data['rows'] as $r) {
            $acc = $r['account'];
            $indent = str_repeat('    ', (int) max(0, $r['level'] - 1));

            $sheet->setCellValue("A{$row}", $acc->code);
            $sheet->setCellValue("B{$row}", $indent.$acc->name);
            $sheet->setCellValue("C{$row}", $r['opening_balance']);
            $sheet->setCellValue("D{$row}", $r['debit_mutation']);
            $sheet->setCellValue("E{$row}", $r['credit_mutation']);
            $sheet->setCellValue("F{$row}", $r['end_debit']);
            $sheet->setCellValue("G{$row}", $r['end_credit']);

            $sheet->getStyle("C{$row}:G{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            if ($r['has_children']) {
                $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
            }
            $row++;
        }

        $sheet->setCellValue("B{$row}", 'TOTAL SALDO AKHIR');
        $sheet->setCellValue("F{$row}", $data['totalDebit']);
        $sheet->setCellValue("G{$row}", $data['totalCredit']);

        $sheet->getStyle("F{$row}:G{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("B{$row}:G{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:G{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$row}:G{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        $row++;

        $this->applySignatures($spreadsheet, $data['company'], $row, 'A', 'D', 'G');
        $this->autoSizeColumns($spreadsheet, $cols);

        return $spreadsheet;
    }

    /**
     * 4. Export Buku Besar (General Ledger)
     */
    public function exportGeneralLedger(int $accountId, string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): Spreadsheet
    {
        $data = $this->pdfService->getGeneralLedgerData($accountId, $startDate, $endDate, $unitFilter, $user);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Buku Besar');

        $account = $data['account'];
        $periodStr = $data['startDate'].' - '.$data['endDate'];
        $row = $this->applyReportHeader($spreadsheet, $data['company'], "Buku Besar: {$account->code} - {$account->name}", $periodStr, $data['unitName'], 'G');

        // Saldo Awal Info
        $sheet->setCellValue("A{$row}", 'Saldo Awal:');
        $sheet->setCellValue("B{$row}", $data['openingBalance']);
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('#,##0.00;(#,##0.00);"-"');
        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $row += 2;

        // Table Headers
        $headers = ['Tanggal', 'No. Transaksi', 'Keterangan', 'Unit', 'Debit', 'Kredit', 'Saldo'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

        foreach ($headers as $idx => $h) {
            $sheet->setCellValue("{$cols[$idx]}{$row}", $h);
        }

        $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $sheet->getStyle("A{$row}:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
        $currencyFormat = '#,##0.00;(#,##0.00);"-"';

        foreach ($data['reportLines'] as $line) {
            $sheet->setCellValue("A{$row}", $line['date']);
            $sheet->setCellValue("B{$row}", $line['entry_number']);
            $sheet->setCellValue("C{$row}", $line['description']);
            $sheet->setCellValue("D{$row}", $line['unit_code']);
            $sheet->setCellValue("E{$row}", $line['debit']);
            $sheet->setCellValue("F{$row}", $line['credit']);
            $sheet->setCellValue("G{$row}", $line['balance']);

            $sheet->getStyle("E{$row}:G{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $row++;
        }

        // Totals
        $sheet->setCellValue("C{$row}", 'TOTAL MUTASI');
        $sheet->setCellValue("E{$row}", $data['totalDebit']);
        $sheet->setCellValue("F{$row}", $data['totalCredit']);
        $sheet->setCellValue("G{$row}", $data['closingBalance']);

        $sheet->getStyle("E{$row}:G{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:G{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$row}:G{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        $row++;

        $this->applySignatures($spreadsheet, $data['company'], $row, 'A', 'D', 'G');
        $this->autoSizeColumns($spreadsheet, $cols);

        return $spreadsheet;
    }

    /**
     * 5. Export Buku Besar Pembantu (Subsidiary Ledger)
     */
    public function exportSubsidiaryLedger(int $accountId, string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): Spreadsheet
    {
        $data = $this->pdfService->getSubsidiaryLedgerData($accountId, $startDate, $endDate, $unitFilter, $user);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Buku Besar Pembantu');

        $account = $data['account'];
        $periodStr = $data['startDate'].' - '.$data['endDate'];
        $row = $this->applyReportHeader($spreadsheet, $data['company'], "Buku Besar Pembantu: {$account->code} - {$account->name}", $periodStr, $data['unitName'], 'G');

        $sheet->setCellValue("A{$row}", 'Saldo Awal:');
        $sheet->setCellValue("B{$row}", $data['openingBalance']);
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('#,##0.00;(#,##0.00);"-"');
        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $row += 2;

        $headers = ['Tanggal', 'No. Transaksi', 'Keterangan', 'Unit', 'Debit', 'Kredit', 'Saldo'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

        foreach ($headers as $idx => $h) {
            $sheet->setCellValue("{$cols[$idx]}{$row}", $h);
        }

        $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $sheet->getStyle("A{$row}:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
        $currencyFormat = '#,##0.00;(#,##0.00);"-"';

        foreach ($data['reportLines'] as $line) {
            $sheet->setCellValue("A{$row}", $line['date']);
            $sheet->setCellValue("B{$row}", $line['entry_number']);
            $sheet->setCellValue("C{$row}", $line['description']);
            $sheet->setCellValue("D{$row}", $line['unit_code']);
            $sheet->setCellValue("E{$row}", $line['debit']);
            $sheet->setCellValue("F{$row}", $line['credit']);
            $sheet->setCellValue("G{$row}", $line['balance']);

            $sheet->getStyle("E{$row}:G{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $row++;
        }

        $sheet->setCellValue("C{$row}", 'TOTAL MUTASI');
        $sheet->setCellValue("E{$row}", $data['totalDebit']);
        $sheet->setCellValue("F{$row}", $data['totalCredit']);
        $sheet->setCellValue("G{$row}", $data['closingBalance']);

        $sheet->getStyle("E{$row}:G{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:G{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$row}:G{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        $row++;

        $this->applySignatures($spreadsheet, $data['company'], $row, 'A', 'D', 'G');
        $this->autoSizeColumns($spreadsheet, $cols);

        return $spreadsheet;
    }

    /**
     * 6. Export Laporan Arus Kas (Cash Flow)
     */
    public function exportCashFlow(string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): Spreadsheet
    {
        $data = $this->pdfService->getCashFlowData($startDate, $endDate, $unitFilter, $user);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Arus Kas');

        $periodStr = ($data['startDateFormatted'] ?? $data['startDate']).' - '.($data['endDateFormatted'] ?? $data['endDate']);
        $row = $this->applyReportHeader($spreadsheet, $data['company'], 'Laporan Arus Kas (Metode Langsung)', $periodStr, $data['unitName'], 'B');

        $sheet->setCellValue("A{$row}", 'URAIAN / KETERANGAN');
        $sheet->setCellValue("B{$row}", 'REALISASI (RP)');

        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:B{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row++;

        $currencyFormat = '#,##0.00;(#,##0.00);"-"';

        // SECTION A: OPERASI
        $sheet->setCellValue("A{$row}", 'A. ARUS KAS DARI KEGIATAN OPERASI');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:B{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
        $row++;

        foreach ($data['sections']['operating']['rows'] as $r) {
            $sheet->setCellValue("A{$row}", '    '.$r['label']);
            $sheet->setCellValue("B{$row}", $r['value']);
            $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $row++;
        }

        $sheet->setCellValue("A{$row}", 'Jumlah Arus Kas dari Kegiatan Operasi');
        $sheet->setCellValue("B{$row}", $data['totalOperating']);
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:B{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $row += 2;

        // SECTION B: INVESTASI
        $sheet->setCellValue("A{$row}", 'B. ARUS KAS UNTUK KEGIATAN INVESTASI');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:B{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
        $row++;

        foreach ($data['sections']['investing']['rows'] as $r) {
            $sheet->setCellValue("A{$row}", '    '.$r['label']);
            $sheet->setCellValue("B{$row}", $r['value']);
            $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $row++;
        }

        $sheet->setCellValue("A{$row}", 'Jumlah Arus Kas untuk Kegiatan Investasi');
        $sheet->setCellValue("B{$row}", $data['totalInvesting']);
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:B{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $row += 2;

        // SECTION C: PEMBIAYAAN
        $sheet->setCellValue("A{$row}", 'C. ARUS KAS PEMBIAYAAN');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:B{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
        $row++;

        foreach ($data['sections']['financing']['rows'] as $r) {
            $sheet->setCellValue("A{$row}", '    '.$r['label']);
            $sheet->setCellValue("B{$row}", $r['value']);
            $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $row++;
        }

        $sheet->setCellValue("A{$row}", 'Jumlah Arus Kas Pembiayaan');
        $sheet->setCellValue("B{$row}", $data['totalFinancing']);
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:B{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $row += 2;

        // RECONCILIATION SUMMARY
        $sheet->setCellValue("A{$row}", 'KENAIKAN BERSIH KAS (A + B + C)');
        $sheet->setCellValue("B{$row}", $data['netCashFlow']);
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:B{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $row++;

        $sheet->setCellValue("A{$row}", 'Saldo Kas Awal Periode');
        $sheet->setCellValue("B{$row}", $data['openingCash']);
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("A{$row}", 'SALDO KAS, AKHIR PERIODE');
        $sheet->setCellValue("B{$row}", $data['endingCash']);
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:B{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$row}:B{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        $row += 2;

        $this->applySignatures($spreadsheet, $data['company'], $row, 'A', 'A', 'B');
        $this->autoSizeColumns($spreadsheet, ['A', 'B']);

        return $spreadsheet;
    }

    /**
     * 7. Export Laporan Perubahan Ekuitas (Changes in Equity)
     */
    public function exportChangesInEquity(string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): Spreadsheet
    {
        $data = $this->pdfService->getChangesInEquityData($startDate, $endDate, $unitFilter, $user);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Perubahan Ekuitas');

        $periodStr = $data['startDate'].' - '.$data['endDate'];
        $row = $this->applyReportHeader($spreadsheet, $data['company'], 'Laporan Perubahan Ekuitas', $periodStr, $data['unitName'], 'C');

        $sheet->setCellValue("A{$row}", 'Keterangan');
        $sheet->setCellValue("B{$row}", 'Rincian');
        $sheet->setCellValue("C{$row}", 'Total Ekuitas');

        $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:C{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $row++;

        $currencyFormat = '#,##0.00;(#,##0.00);"-"';

        $sheet->setCellValue("A{$row}", 'Saldo Modal / Ekuitas Awal');
        $sheet->setCellValue("C{$row}", $data['initialEquity']);
        $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
        $row += 2;

        $sheet->setCellValue("A{$row}", 'Penambahan / (Pengurangan):');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("A{$row}", '    Laba / (Rugi) Bersih Periode Berjalan');
        $sheet->setCellValue("B{$row}", $data['netProfit']);
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $row += 2;

        $sheet->setCellValue("A{$row}", 'Saldo Modal / Ekuitas Akhir');
        $sheet->setCellValue("C{$row}", $data['endingEquity']);
        $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:C{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$row}:C{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        $row++;

        $this->applySignatures($spreadsheet, $data['company'], $row, 'A', 'B', 'C');
        $this->autoSizeColumns($spreadsheet, ['A', 'B', 'C']);

        return $spreadsheet;
    }

    /**
     * 8. Export Neraca Lajur 10 Kolom (Worksheet)
     */
    public function exportWorksheet(string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): Spreadsheet
    {
        $data = $this->pdfService->getWorksheetData($startDate, $endDate, $unitFilter, $user);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Neraca Lajur');

        $periodStr = $data['startDate'].' - '.$data['endDate'];
        $row = $this->applyReportHeader($spreadsheet, $data['company'], 'Neraca Lajur 10 Kolom (Worksheet)', $periodStr, $data['unitName'], 'L');

        // Level 1 Header
        $sheet->setCellValue("A{$row}", 'Kode');
        $sheet->mergeCells("A{$row}:A".($row + 1));
        $sheet->setCellValue("B{$row}", 'Nama Akun');
        $sheet->mergeCells("B{$row}:B".($row + 1));

        $sheet->setCellValue("C{$row}", 'Neraca Saldo');
        $sheet->mergeCells("C{$row}:D{$row}");
        $sheet->setCellValue("E{$row}", 'Penyesuaian');
        $sheet->mergeCells("E{$row}:F{$row}");
        $sheet->setCellValue("G{$row}", 'NS Disesuaikan');
        $sheet->mergeCells("G{$row}:H{$row}");
        $sheet->setCellValue("I{$row}", 'Laba Rugi');
        $sheet->mergeCells("I{$row}:J{$row}");
        $sheet->setCellValue("K{$row}", 'Neraca');
        $sheet->mergeCells("K{$row}:L{$row}");

        $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:L{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
        // Level 2 Subheaders
        $subheaders = ['Debit', 'Kredit', 'Debit', 'Kredit', 'Debit', 'Kredit', 'Debit', 'Kredit', 'Debit', 'Kredit'];
        $subCols = ['C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
        foreach ($subheaders as $idx => $sh) {
            $sheet->setCellValue("{$subCols[$idx]}{$row}", $sh);
        }

        $sheet->getStyle('A'.($row - 1).":L{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $sheet->getStyle('A'.($row - 1).":L{$row}")->getFont()->setBold(true);
        $sheet->getStyle("C{$row}:L{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
        $currencyFormat = '#,##0.00;(#,##0.00);"-"';

        foreach ($data['rows'] as $r) {
            $acc = $r['account'];
            $indent = str_repeat('    ', (int) max(0, $r['level'] - 1));

            $sheet->setCellValue("A{$row}", $acc->code);
            $sheet->setCellValue("B{$row}", $indent.$acc->name);
            $sheet->setCellValue("C{$row}", $r['tb_debit']);
            $sheet->setCellValue("D{$row}", $r['tb_credit']);
            $sheet->setCellValue("E{$row}", $r['adj_debit']);
            $sheet->setCellValue("F{$row}", $r['adj_credit']);
            $sheet->setCellValue("G{$row}", $r['atb_debit']);
            $sheet->setCellValue("H{$row}", $r['atb_credit']);
            $sheet->setCellValue("I{$row}", $r['is_debit']);
            $sheet->setCellValue("J{$row}", $r['is_credit']);
            $sheet->setCellValue("K{$row}", $r['bs_debit']);
            $sheet->setCellValue("L{$row}", $r['bs_credit']);

            $sheet->getStyle("C{$row}:L{$row}")->getNumberFormat()->setFormatCode($currencyFormat);

            if ($r['has_children']) {
                $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true);
            }
            $row++;
        }

        // Totals
        $sheet->setCellValue("B{$row}", 'TOTAL');
        $sheet->setCellValue("C{$row}", $data['totTbDebit']);
        $sheet->setCellValue("D{$row}", $data['totTbCredit']);
        $sheet->setCellValue("E{$row}", $data['totAdjDebit']);
        $sheet->setCellValue("F{$row}", $data['totAdjCredit']);
        $sheet->setCellValue("G{$row}", $data['totAtbDebit']);
        $sheet->setCellValue("H{$row}", $data['totAtbCredit']);
        $sheet->setCellValue("I{$row}", $data['totIsDebit']);
        $sheet->setCellValue("J{$row}", $data['totIsCredit']);
        $sheet->setCellValue("K{$row}", $data['totBsDebit']);
        $sheet->setCellValue("L{$row}", $data['totBsCredit']);

        $sheet->getStyle("C{$row}:L{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:L{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$row}:L{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        $row++;

        $this->applySignatures($spreadsheet, $data['company'], $row, 'B', 'F', 'K');
        $this->autoSizeColumns($spreadsheet, ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L']);

        return $spreadsheet;
    }

    /**
     * 9. Export Laporan Saldo Awal (Opening Balance)
     */
    public function exportOpeningBalance(?int $periodId = null, string $unitFilter = 'all', ?User $user = null, string $mode = 'balance_sheet'): Spreadsheet
    {
        $data = $this->pdfService->getOpeningBalanceData($periodId, $unitFilter, $user, $mode);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Saldo Awal');

        $modeTitle = $mode === 'balance_sheet' ? 'Saldo Awal Neraca Murni' : 'Neraca Saldo Kumulatif';
        $row = $this->applyReportHeader($spreadsheet, $data['company'], "Laporan {$modeTitle}", 'Periode '.$data['periodName'], $data['unitName'], 'D');

        $sheet->setCellValue("A{$row}", 'Kode');
        $sheet->setCellValue("B{$row}", 'Nama Akun');
        $sheet->setCellValue("C{$row}", 'Debit');
        $sheet->setCellValue("D{$row}", 'Kredit');

        $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $sheet->getStyle("A{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
        $currencyFormat = '#,##0.00;(#,##0.00);"-"';

        foreach ($data['lines'] as $l) {
            $acc = $l['account'];
            $sheet->setCellValue("A{$row}", $acc->code);
            $sheet->setCellValue("B{$row}", $acc->name);
            $sheet->setCellValue("C{$row}", $l['debit']);
            $sheet->setCellValue("D{$row}", $l['credit']);

            $sheet->getStyle("C{$row}:D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $row++;
        }

        $sheet->setCellValue("B{$row}", 'TOTAL');
        $sheet->setCellValue("C{$row}", $data['totalDebit']);
        $sheet->setCellValue("D{$row}", $data['totalCredit']);

        $sheet->getStyle("C{$row}:D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:D{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$row}:D{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        $row++;

        $this->applySignatures($spreadsheet, $data['company'], $row, 'A', 'B', 'D');
        $this->autoSizeColumns($spreadsheet, ['A', 'B', 'C', 'D']);

        return $spreadsheet;
    }

    /**
     * Export Laporan Aging Hutang / Piutang ke Spreadsheet Excel.
     */
    public function exportAging(string $type, string $asOfDate, string $unitFilter = 'all', ?User $user = null, ?string $startDate = null): Spreadsheet
    {
        $reportService = new AgingReportService;
        $reportData = $reportService->getAgingReport($type, $asOfDate, $unitFilter, true, $startDate);

        $company = Company::first();
        $targetUnit = $unitFilter !== 'all' ? Unit::find($unitFilter) : null;
        $unitName = $unitFilter === 'all' ? 'Konsolidasi (Seluruh Unit)' : ($targetUnit ? $targetUnit->name : 'Unit');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $titleLabel = $type === 'receivable' ? 'PIUTANG USAHA (AR AGING)' : 'HUTANG USAHA (AP AGING)';
        $sheet->setTitle('Aging Report');

        $row = $this->applyReportHeader($spreadsheet, $company, 'LAPORAN UMUR '.$titleLabel, $asOfDate, $unitName, 'I');

        // Headers
        $headers = [
            'A' => 'NO. INVOICE / DOKUMEN',
            'B' => 'REKANAN / DESKRIPSI',
            'C' => 'JATUH TEMPO',
            'D' => 'SALDO TERBUKA',
            'E' => 'LANCAR',
            'F' => '1-30 HARI',
            'G' => '31-60 HARI',
            'H' => '61-90 HARI',
            'I' => '> 90 HARI',
        ];

        foreach ($headers as $col => $lbl) {
            $sheet->setCellValue("{$col}{$row}", $lbl);
        }

        $sheet->getStyle("A{$row}:I{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');
        $sheet->getStyle("A{$row}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
        $currencyFormat = '#,##0.00;(#,##0.00);"-"';

        foreach ($reportData['accounts'] as $accItem) {
            // Account header row
            $sheet->setCellValue("A{$row}", $accItem['account']['code'].' - '.$accItem['account']['name']);
            $sheet->setCellValue("D{$row}", $accItem['subtotal']['total_outstanding']);
            $sheet->setCellValue("E{$row}", $accItem['subtotal']['current']);
            $sheet->setCellValue("F{$row}", $accItem['subtotal']['overdue_1_30']);
            $sheet->setCellValue("G{$row}", $accItem['subtotal']['overdue_31_60']);
            $sheet->setCellValue("H{$row}", $accItem['subtotal']['overdue_61_90']);
            $sheet->setCellValue("I{$row}", $accItem['subtotal']['overdue_over_90']);

            $sheet->getStyle("A{$row}:I{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
            $sheet->getStyle("D{$row}:I{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $row++;

            // Invoices row
            foreach ($accItem['invoices'] as $inv) {
                $sheet->setCellValue("A{$row}", '   '.$inv['invoice_number']);
                $sheet->setCellValue("B{$row}", $inv['partner_name']);
                $sheet->setCellValue("C{$row}", $inv['due_date']);
                $sheet->setCellValue("D{$row}", $inv['remaining_amount']);
                $sheet->setCellValue("E{$row}", $inv['buckets']['current']);
                $sheet->setCellValue("F{$row}", $inv['buckets']['overdue_1_30']);
                $sheet->setCellValue("G{$row}", $inv['buckets']['overdue_31_60']);
                $sheet->setCellValue("H{$row}", $inv['buckets']['overdue_61_90']);
                $sheet->setCellValue("I{$row}", $inv['buckets']['overdue_over_90']);

                $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D{$row}:I{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
                $row++;
            }
        }

        // Grand Total
        $sheet->setCellValue("A{$row}", 'TOTAL KESELURUHAN (GRAND TOTAL)');
        $sheet->setCellValue("D{$row}", $reportData['kpi']['total_outstanding']);
        $sheet->setCellValue("E{$row}", $reportData['kpi']['current']);
        $sheet->setCellValue("F{$row}", $reportData['kpi']['overdue_1_30']);
        $sheet->setCellValue("G{$row}", $reportData['kpi']['overdue_31_60']);
        $sheet->setCellValue("H{$row}", $reportData['kpi']['overdue_61_90']);
        $sheet->setCellValue("I{$row}", $reportData['kpi']['overdue_over_90']);

        $sheet->getStyle("A{$row}:I{$row}")->getFont()->setBold(true);
        $sheet->getStyle("D{$row}:I{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("A{$row}:I{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$row}:I{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        $row++;

        $this->applySignatures($spreadsheet, $company, $row, 'A', 'C', 'I');
        $this->autoSizeColumns($spreadsheet, ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']);

        return $spreadsheet;
    }
}
