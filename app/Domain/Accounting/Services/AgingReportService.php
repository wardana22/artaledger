<?php

namespace App\Domain\Accounting\Services;

use App\Models\Account;
use App\Models\ApArInvoice;
use App\Models\JournalLine;
use Carbon\Carbon;

class AgingReportService
{
    /**
     * Hitung laporan aging piutang atau hutang per tanggal acuan (asOfDate).
     *
     * @param  string  $type  'receivable' | 'payable'
     * @param  string  $asOfDate  'Y-m-d'
     * @param  string  $unitFilter  'all' | unit_id
     * @return array<string, mixed>
     */
    public function getAgingReport(string $type, string $asOfDate, string $unitFilter = 'all', bool $hideZeroBalances = true): array
    {
        $cutoffDate = Carbon::parse($asOfDate)->endOfDay();
        $accountTypeMatch = $type === 'receivable' ? 'PIUTANG' : 'HUTANG LANCAR';

        // 1. Ambil akun COA yang relevan
        $accountsQuery = Account::where('is_group', false)
            ->where('is_active', true)
            ->where(function ($q) use ($accountTypeMatch) {
                $q->where('type', $accountTypeMatch)
                    ->orWhere('type', 'like', "%{$accountTypeMatch}%");
            })
            ->orderBy('code', 'asc');

        $accounts = $accountsQuery->get();

        $reportData = [];
        $kpiSummary = [
            'total_outstanding' => 0.0,
            'current' => 0.0,
            'overdue_1_30' => 0.0,
            'overdue_31_60' => 0.0,
            'overdue_61_90' => 0.0,
            'overdue_over_90' => 0.0,
        ];

        foreach ($accounts as $account) {
            $accountRows = $this->calculateAccountAgingRows($account, $type, $cutoffDate, $unitFilter);

            $accountSubtotal = [
                'total_outstanding' => 0.0,
                'current' => 0.0,
                'overdue_1_30' => 0.0,
                'overdue_31_60' => 0.0,
                'overdue_61_90' => 0.0,
                'overdue_over_90' => 0.0,
            ];

            foreach ($accountRows as $row) {
                $accountSubtotal['total_outstanding'] += $row['remaining_amount'];
                $accountSubtotal['current'] += $row['buckets']['current'];
                $accountSubtotal['overdue_1_30'] += $row['buckets']['overdue_1_30'];
                $accountSubtotal['overdue_31_60'] += $row['buckets']['overdue_31_60'];
                $accountSubtotal['overdue_61_90'] += $row['buckets']['overdue_61_90'];
                $accountSubtotal['overdue_over_90'] += $row['buckets']['overdue_over_90'];
            }

            if ($hideZeroBalances && abs($accountSubtotal['total_outstanding']) < 0.01 && count($accountRows) === 0) {
                continue;
            }

            $reportData[] = [
                'account' => [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                ],
                'subtotal' => $accountSubtotal,
                'invoices' => $accountRows,
            ];

            // Akumulasi KPI Total
            $kpiSummary['total_outstanding'] += $accountSubtotal['total_outstanding'];
            $kpiSummary['current'] += $accountSubtotal['current'];
            $kpiSummary['overdue_1_30'] += $accountSubtotal['overdue_1_30'];
            $kpiSummary['overdue_31_60'] += $accountSubtotal['overdue_31_60'];
            $kpiSummary['overdue_61_90'] += $accountSubtotal['overdue_61_90'];
            $kpiSummary['overdue_over_90'] += $accountSubtotal['overdue_over_90'];
        }

        return [
            'type' => $type,
            'as_of_date' => $asOfDate,
            'unit_filter' => $unitFilter,
            'kpi' => $kpiSummary,
            'accounts' => $reportData,
        ];
    }

    /**
     * Hitung rincian baris invoice untuk satu akun COA.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function calculateAccountAgingRows(Account $account, string $type, Carbon $cutoffDate, string $unitFilter): array
    {
        // 1. Ambil invoice terdaftar yang dibuat pada/sebelum cutoff date
        $invoicesQuery = ApArInvoice::with(['settlements' => function ($q) use ($cutoffDate) {
            $q->where('settled_date', '<=', $cutoffDate->format('Y-m-d'));
        }, 'journalLine.journalEntry', 'unit'])
            ->where('account_id', $account->id)
            ->where('type', $type)
            ->where('invoice_date', '<=', $cutoffDate->format('Y-m-d'));

        if ($unitFilter !== 'all') {
            $invoicesQuery->where('unit_id', (int) $unitFilter);
        }

        $invoices = $invoicesQuery->get();
        $processedLineIds = [];
        $rows = [];

        foreach ($invoices as $invoice) {
            $processedLineIds[] = $invoice->journal_line_id;

            $settledSoFar = (float) $invoice->settlements->sum('settled_amount');
            $remaining = max(0.0, (float) $invoice->original_amount - $settledSoFar);

            if ($remaining <= 0.001) {
                continue; // Lunas per tanggal cutoff
            }

            // Hitung hari jatuh tempo terhadap cutoff date
            $dueDate = Carbon::parse($invoice->due_date)->startOfDay();
            $daysOverdue = (int) $dueDate->diffInDays($cutoffDate, false); // Positif jika cutoff > due_date

            $buckets = $this->classifyIntoBuckets($remaining, $daysOverdue);

            $rows[] = [
                'id' => $invoice->id,
                'is_registered_invoice' => true,
                'invoice_number' => $invoice->invoice_number,
                'partner_name' => $invoice->partner_name ?: '-',
                'entry_number' => $invoice->journalLine?->journalEntry?->entry_number ?: '-',
                'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                'due_date' => $invoice->due_date->format('Y-m-d'),
                'unit_code' => $invoice->unit?->code ?: '-',
                'original_amount' => (float) $invoice->original_amount,
                'settled_amount' => $settledSoFar,
                'remaining_amount' => $remaining,
                'days_overdue' => max(0, $daysOverdue),
                'buckets' => $buckets,
            ];
        }

        // 2. Ambil baris jurnal yang BELUM didaftarkan invoice (Unassigned Transactions)
        $unassignedLines = $this->getUnassignedJournalLines($account, $type, $cutoffDate, $unitFilter, $processedLineIds);

        foreach ($unassignedLines as $unassigned) {
            $rows[] = $unassigned;
        }

        return $rows;
    }

    /**
     * Cari baris jurnal piutang/hutang yang belum didaftarkan nomor invoice resminya.
     *
     * @param  array<int, int>  $excludeLineIds
     * @return array<int, array<string, mixed>>
     */
    protected function getUnassignedJournalLines(Account $account, string $type, Carbon $cutoffDate, string $unitFilter, array $excludeLineIds): array
    {
        $linesQuery = JournalLine::with(['journalEntry', 'unit'])
            ->where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($cutoffDate) {
                $q->where('status', 'posted')
                    ->where('entry_date', '<=', $cutoffDate->format('Y-m-d'));
            });

        if ($unitFilter !== 'all') {
            $linesQuery->where('unit_id', (int) $unitFilter);
        }

        if (! empty($excludeLineIds)) {
            $linesQuery->whereNotIn('id', $excludeLineIds);
        }

        // Ambil hanya baris pengakuan tagihan awal (Debit untuk piutang, Kredit untuk hutang)
        if ($type === 'receivable') {
            $linesQuery->where('debit', '>', 0);
        } else {
            $linesQuery->where('credit', '>', 0);
        }

        $lines = $linesQuery->get();
        $unassignedRows = [];

        foreach ($lines as $line) {
            $amount = $type === 'receivable' ? (float) $line->debit : (float) $line->credit;
            if ($amount <= 0.01) {
                continue;
            }

            $entryDate = Carbon::parse($line->journalEntry->entry_date)->startOfDay();
            $days = (int) $entryDate->diffInDays($cutoffDate, false);
            $buckets = $this->classifyIntoBuckets($amount, $days);

            $unassignedRows[] = [
                'id' => $line->id,
                'is_registered_invoice' => false,
                'invoice_number' => '(Belum Bernomor Invoice)',
                'partner_name' => $line->description ?: '-',
                'entry_number' => $line->journalEntry->entry_number,
                'invoice_date' => $entryDate->format('Y-m-d'),
                'due_date' => $entryDate->format('Y-m-d'),
                'unit_code' => $line->unit?->code ?: '-',
                'original_amount' => $amount,
                'settled_amount' => 0.0,
                'remaining_amount' => $amount,
                'days_overdue' => max(0, $days),
                'buckets' => $buckets,
            ];
        }

        return $unassignedRows;
    }

    /**
     * Kelompokkan saldo ke dalam bucket umur.
     *
     * @return array<string, float>
     */
    protected function classifyIntoBuckets(float $amount, int $daysOverdue): array
    {
        $buckets = [
            'current' => 0.0,
            'overdue_1_30' => 0.0,
            'overdue_31_60' => 0.0,
            'overdue_61_90' => 0.0,
            'overdue_over_90' => 0.0,
        ];

        if ($daysOverdue <= 0) {
            $buckets['current'] = $amount;
        } elseif ($daysOverdue <= 30) {
            $buckets['overdue_1_30'] = $amount;
        } elseif ($daysOverdue <= 60) {
            $buckets['overdue_31_60'] = $amount;
        } elseif ($daysOverdue <= 90) {
            $buckets['overdue_61_90'] = $amount;
        } else {
            $buckets['overdue_over_90'] = $amount;
        }

        return $buckets;
    }
}
