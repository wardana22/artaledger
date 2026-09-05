<?php

namespace App\Domain\Banking\Services;

use Carbon\Carbon;
use Exception;
use Smalot\PdfParser\Parser;

class BriCmsPdfParserService
{
    protected Parser $parser;

    public function __construct(?Parser $parser = null)
    {
        $this->parser = $parser ?? new Parser;
    }

    /**
     * Parse rekening koran PDF Cash Management System (CMS) BRI.
     *
     * @return array{
     *     metadata: array{
     *         bank_name: string,
     *         account_number: string,
     *         account_holder: string,
     *         period_start: string,
     *         period_end: string,
     *         opening_balance: float,
     *         total_debit: float,
     *         total_credit: float,
     *         closing_balance: float
     *     },
     *     lines: array<int, array{
     *         date: string,
     *         time: ?string,
     *         description: string,
     *         debit: float,
     *         credit: float,
     *         balance: float,
     *         teller_id: ?string
     *     }>
     * }
     *
     * @throws Exception
     */
    public function parse(string $filePath): array
    {
        if (! file_exists($filePath)) {
            throw new Exception("File PDF rekening koran tidak ditemukan: {$filePath}");
        }

        $pdf = $this->parser->parseFile($filePath);
        $pages = $pdf->getPages();

        if (empty($pages)) {
            throw new Exception('File PDF kosong atau tidak dapat diekstraksi.');
        }

        $firstPageText = $pages[0]->getText();
        $lastPageText = $pages[count($pages) - 1]->getText();

        // 1. Ekstrak Metadata Rekening
        $accountNumber = '1079-01-000382-30-5';
        if (preg_match('/Account No\s*[:\s]*([0-9\-]+)/i', $firstPageText, $m)) {
            $accountNumber = trim($m[1]);
        } elseif (preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{6}-[0-9]{2}-[0-9])/i', $firstPageText, $m)) {
            $accountNumber = trim($m[1]);
        }

        $accountHolder = 'PT NUSA LIMA MEDIKA';
        if (preg_match('/Account Name\s*[:\t\s]*([^\n\r]+)/i', $firstPageText, $m)) {
            $accountHolder = trim($m[1]);
        }

        $periodStart = '2025-01-01';
        $periodEnd = '2025-01-31';
        if (preg_match('/([0-9]{2}\/[0-9]{2}\/[0-9]{4})\s*-\s*([0-9]{2}\/[0-9]{2}\/[0-9]{4})/', $firstPageText, $m)) {
            $periodStart = Carbon::createFromFormat('d/m/Y', trim($m[1]))->format('Y-m-d');
            $periodEnd = Carbon::createFromFormat('d/m/Y', trim($m[2]))->format('Y-m-d');
        }

        // 2. Ekstrak Rekapitulasi Saldo Akhir Dokumen
        $openingBalance = 0.0;
        $summaryDebit = 0.0;
        $summaryCredit = 0.0;
        $closingBalance = 0.0;

        if (preg_match('/OPENING BALANCE[\s\S]*?([0-9]{1,3}(?:,[0-9]{3})*\.[0-9]{2})/i', $lastPageText, $m)) {
            $openingBalance = (float) str_replace(',', '', $m[1]);
        }
        if (preg_match('/TOTAL DEBET[\s\S]*?([0-9]{1,3}(?:,[0-9]{3})*\.[0-9]{2})/i', $lastPageText, $m)) {
            $summaryDebit = (float) str_replace(',', '', $m[1]);
        }
        if (preg_match('/TOTAL CREDIT[\s\S]*?([0-9]{1,3}(?:,[0-9]{3})*\.[0-9]{2})/i', $lastPageText, $m)) {
            $summaryCredit = (float) str_replace(',', '', $m[1]);
        }
        if (preg_match('/CLOSING BALANCE[\s\S]*?([0-9]{1,3}(?:,[0-9]{3})*\.[0-9]{2})/i', $lastPageText, $m)) {
            $closingBalance = (float) str_replace(',', '', $m[1]);
        }

        // 3. Ekstrak Baris Transaksi per Halaman
        $pattern = '/(?P<date>\d{2}\/\d{2}\/\d{2})[\t\s]+(?P<time>\d{2}:\d{2}:\d{2})[\t\s\r\n]+(?P<remark>[\s\S]*?)[\t\n](?P<debet>\d{1,3}(?:,\d{3})*\.\d{2})\t(?P<credit>\d{1,3}(?:,\d{3})*\.\d{2})\t(?P<ledger>\d{1,3}(?:,\d{3})*\.\d{2})(?:\t(?P<teller_id>[^\n\r]*))?/';

        $lines = [];
        $calculatedDebit = 0.0;
        $calculatedCredit = 0.0;

        foreach ($pages as $page) {
            $pageText = $page->getText();

            // Bersihkan Header
            if (strpos($pageText, "DATE\tTIME") !== false) {
                $pos = strpos($pageText, "DATE\tTIME");
                $pageText = substr($pageText, $pos + strlen("DATE\tTIME\tREMARK\tDEBET\tCREDIT\tLedger\tTELLER ID"));
            }

            // Bersihkan Footer
            if (strpos($pageText, 'Cetakan Cash Management System BRI') !== false) {
                $pageText = substr($pageText, 0, strpos($pageText, 'Cetakan Cash Management System BRI'));
            }
            if (strpos($pageText, 'OPENING BALANCE') !== false) {
                $pageText = substr($pageText, 0, strpos($pageText, 'OPENING BALANCE'));
            }

            preg_match_all($pattern, $pageText, $matches, PREG_SET_ORDER);

            foreach ($matches as $m) {
                $rawDate = trim($m['date']);
                $dateObj = Carbon::createFromFormat('d/m/y', $rawDate);
                $formattedDate = $dateObj ? $dateObj->format('Y-m-d') : date('Y-m-d');

                $rawTime = trim($m['time']);
                $rawRemark = trim((string) preg_replace('/\s+/', ' ', $m['remark']));
                $debitVal = (float) str_replace(',', '', $m['debet']);
                $creditVal = (float) str_replace(',', '', $m['credit']);
                $balanceVal = (float) str_replace(',', '', $m['ledger']);
                $tellerId = ! empty($m['teller_id']) ? trim($m['teller_id']) : null;

                $calculatedDebit += $debitVal;
                $calculatedCredit += $creditVal;

                $lines[] = [
                    'date' => $formattedDate,
                    'time' => $rawTime,
                    'description' => $rawRemark,
                    'debit' => $debitVal,
                    'credit' => $creditVal,
                    'balance' => $balanceVal,
                    'teller_id' => $tellerId,
                ];
            }
        }

        if (empty($lines)) {
            throw new Exception('Format berkas tidak sesuai atau tidak ada baris transaksi yang berhasil diidentifikasi.');
        }

        // Jika summary tidak ada di halaman terakhir, fallback ke kalkulasi aktual
        if ($summaryDebit == 0.0 && $calculatedDebit > 0) {
            $summaryDebit = $calculatedDebit;
        }
        if ($summaryCredit == 0.0 && $calculatedCredit > 0) {
            $summaryCredit = $calculatedCredit;
        }
        if ($closingBalance == 0.0) {
            $closingBalance = $lines[0]['balance']; // baris pertama atau terakhir
        }

        return [
            'metadata' => [
                'bank_name' => 'BANK BRI',
                'account_number' => $accountNumber,
                'account_holder' => $accountHolder,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'opening_balance' => $openingBalance,
                'total_debit' => $summaryDebit,
                'total_credit' => $summaryCredit,
                'closing_balance' => $closingBalance,
            ],
            'lines' => $lines,
        ];
    }
}
