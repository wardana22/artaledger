<?php

namespace App\Domain\Accounting\Services;

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

        $companyName = $company?->name ?? 'PT ArtaLedger Enterprise';

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
            $indent = str_repeat('    ', max(0, $r['level'] - 1));

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
            ['Laba / (Rugi) Bersih', $data['netProfit']],
        ];

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
            $indent = str_repeat('    ', max(0, $r['level'] - 1));

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
            $indent = str_repeat('    ', max(0, $r['level'] - 1));

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
            $indent = str_repeat('    ', max(0, $r['level'] - 1));

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
            $indent = str_repeat('    ', max(0, $r['level'] - 1));

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

        $periodStr = $data['startDate'].' - '.$data['endDate'];
        $row = $this->applyReportHeader($spreadsheet, $data['company'], 'Laporan Arus Kas (Cash Flow)', $periodStr, $data['unitName'], 'C');

        $sheet->setCellValue("A{$row}", 'Uraian Arus Kas');
        $sheet->setCellValue("B{$row}", 'Rincian');
        $sheet->setCellValue("C{$row}", 'Total');

        $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:C{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $row++;

        $currencyFormat = '#,##0.00;(#,##0.00);"-"';

        $sheet->setCellValue("A{$row}", 'Saldo Kas & Bank Awal Periode');
        $sheet->setCellValue("C{$row}", $data['openingCash']);
        $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
        $row += 2;

        $sheet->setCellValue("A{$row}", 'Arus Kas dari Aktivitas Operasional');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("A{$row}", '    Penerimaan Kas Operasional');
        $sheet->setCellValue("B{$row}", $data['operatingIn']);
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $row++;

        $sheet->setCellValue("A{$row}", '    Pengeluaran Kas Operasional');
        $sheet->setCellValue("B{$row}", -$data['operatingOut']);
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $row++;

        $sheet->setCellValue("A{$row}", 'Arus Kas Bersih dari Aktivitas Operasional');
        $sheet->setCellValue("C{$row}", $data['netOperatingCash']);
        $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:C{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $row += 2;

        $sheet->setCellValue("A{$row}", 'Saldo Kas & Bank Akhir Periode');
        $sheet->setCellValue("C{$row}", $data['endingCash']);
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
            $indent = str_repeat('    ', max(0, $r['level'] - 1));

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
    public function exportOpeningBalance(?int $periodId = null, string $unitFilter = 'all', ?User $user = null): Spreadsheet
    {
        $data = $this->pdfService->getOpeningBalanceData($periodId, $unitFilter, $user);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Saldo Awal');

        $row = $this->applyReportHeader($spreadsheet, $data['company'], 'Laporan Saldo Awal Akun', 'Periode '.$data['periodName'], $data['unitName'], 'D');

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
}
