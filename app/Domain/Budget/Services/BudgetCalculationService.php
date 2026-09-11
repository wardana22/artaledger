<?php

namespace App\Domain\Budget\Services;

use App\Models\Budget;
use App\Models\JournalLine;
use Illuminate\Support\Facades\DB;

class BudgetCalculationService
{
    /**
     * Calculate variance and actual absorption for a given budget, optionally filtered by month, unit, status, and search query.
     *
     * @param  int|null  $month  1-12 or null for full annual year
     * @param  string  $statusFilter  'all', 'terkendali', 'mendekati', 'melampaui'
     */
    public function calculateBudgetComparison(
        Budget $budget,
        ?int $month = null,
        ?int $unitId = null,
        string $statusFilter = 'all',
        string $search = ''
    ): array {
        $year = $budget->fiscal_year;
        $budgetLinesQuery = $budget->lines()->with(['account', 'unit']);

        if ($unitId) {
            $budgetLinesQuery->where('unit_id', $unitId);
        }

        $budgetLines = $budgetLinesQuery->get();

        // Calculate actual spending from posted JournalLine for this year
        $actualsQuery = JournalLine::query()
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts', 'journal_lines.account_id', '=', 'accounts.id')
            ->where('journal_entries.status', 'posted')
            ->whereYear('journal_entries.entry_date', $year);

        if ($month !== null) {
            $actualsQuery->whereMonth('journal_entries.entry_date', $month);
        }

        if ($unitId) {
            $actualsQuery->where('journal_lines.unit_id', $unitId);
        }

        // Net expense spending for expense/asset accounts is (debit - credit)
        $actuals = $actualsQuery
            ->groupBy('journal_lines.account_id', 'journal_lines.unit_id')
            ->select(
                'journal_lines.account_id',
                'journal_lines.unit_id',
                DB::raw('SUM(journal_lines.debit - journal_lines.credit) as actual_amount')
            )
            ->get()
            ->keyBy(function ($item) {
                return $item->account_id.'_'.($item->unit_id ?? 'all');
            });

        $items = [];
        $totalBudget = 0.0;
        $totalActual = 0.0;

        foreach ($budgetLines as $line) {
            $budgetAmount = $month !== null ? $line->getMonthlyAmount($month) : (float) $line->annual_amount;

            $key = $line->account_id.'_'.($line->unit_id ?? 'all');
            $actualAmount = isset($actuals[$key]) ? (float) $actuals[$key]->actual_amount : 0.0;

            $variance = $budgetAmount - $actualAmount; // positive: surplus/remaining, negative: deficit
            $absorptionRate = $budgetAmount > 0 ? ($actualAmount / $budgetAmount) * 100 : ($actualAmount > 0 ? 100.0 : 0.0);

            // Status category: 'terkendali' (<80%), 'mendekati' (80-99.9%), 'melampaui' (>=100%)
            $status = 'terkendali';
            if ($absorptionRate >= 100.0) {
                $status = 'melampaui';
            } elseif ($absorptionRate >= (float) $budget->warning_threshold_pct) {
                $status = 'mendekati';
            }

            // Apply search filter if provided
            $accountCode = $line->account->code ?? '-';
            $accountName = $line->account->name ?? '-';

            if (! empty($search)) {
                $term = strtolower(trim($search));
                if (! str_contains(strtolower($accountCode), $term) && ! str_contains(strtolower($accountName), $term)) {
                    continue;
                }
            }

            // Apply status filter if provided
            if ($statusFilter !== 'all' && $status !== $statusFilter) {
                continue;
            }

            $items[] = [
                'line_id' => $line->id,
                'account_id' => $line->account_id,
                'account_code' => $accountCode,
                'account_name' => $accountName,
                'unit_id' => $line->unit_id,
                'unit_name' => $line->unit->name ?? 'Semua Unit',
                'budget_amount' => $budgetAmount,
                'actual_amount' => $actualAmount,
                'variance_amount' => $variance,
                'absorption_rate' => round($absorptionRate, 2),
                'status' => $status,
            ];

            $totalBudget += $budgetAmount;
            $totalActual += $actualAmount;
        }

        $totalVariance = $totalBudget - $totalActual;
        $aggregateAbsorption = $totalBudget > 0 ? ($totalActual / $totalBudget) * 100 : 0.0;

        return [
            'budget' => $budget,
            'year' => $year,
            'month' => $month,
            'unit_id' => $unitId,
            'status_filter' => $statusFilter,
            'search' => $search,
            'items' => $items,
            'summary' => [
                'total_budget' => $totalBudget,
                'total_actual' => $totalActual,
                'total_variance' => $totalVariance,
                'aggregate_absorption' => round($aggregateAbsorption, 2),
                'is_over_budget' => $totalActual > $totalBudget,
            ],
        ];
    }

    /**
     * Fetch drill-down journal lines that formed the actual spending for a specific account.
     */
    public function getAccountJournalDetails(int $accountId, int $year, ?int $month = null, ?int $unitId = null): array
    {
        $query = JournalLine::query()
            ->with(['journalEntry', 'unit', 'account'])
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->where('journal_lines.account_id', $accountId)
            ->whereYear('journal_entries.entry_date', $year);

        if ($month !== null) {
            $query->whereMonth('journal_entries.entry_date', $month);
        }

        if ($unitId) {
            $query->where('journal_lines.unit_id', $unitId);
        }

        $lines = $query->orderBy('journal_entries.entry_date', 'asc')
            ->orderBy('journal_entries.entry_number', 'asc')
            ->select('journal_lines.*')
            ->get();

        $details = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($lines as $line) {
            $debit = (float) $line->debit;
            $credit = (float) $line->credit;
            $net = $debit - $credit;

            $details[] = [
                'id' => $line->id,
                'entry_number' => $line->journalEntry->entry_number ?? '-',
                'document_number' => $line->journalEntry->document_number ?? '-',
                'entry_date' => $line->journalEntry->entry_date ? $line->journalEntry->entry_date->format('d/m/Y') : '-',
                'unit_name' => $line->unit->name ?? 'Pusat/Semua',
                'description' => $line->description ?: ($line->journalEntry->description ?? '-'),
                'debit' => $debit,
                'credit' => $credit,
                'net_amount' => $net,
            ];

            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        return [
            'lines' => $details,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'net_actual' => $totalDebit - $totalCredit,
        ];
    }
}
