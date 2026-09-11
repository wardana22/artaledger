<?php

namespace App\Domain\Budget\Services;

use App\Models\Budget;
use App\Models\JournalLine;
use Illuminate\Support\Facades\DB;

class BudgetCalculationService
{
    /**
     * Calculate variance and actual absorption for a given budget, optionally filtered by month and unit.
     *
     * @param  int|null  $month  1-12 or null for full annual year
     */
    public function calculateBudgetComparison(Budget $budget, ?int $month = null, ?int $unitId = null): array
    {
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

        // Net expense spending for expense/asset accounts is typically (debit - credit)
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

            $variance = $budgetAmount - $actualAmount; // positive: under budget, negative: over budget
            $absorptionRate = $budgetAmount > 0 ? ($actualAmount / $budgetAmount) * 100 : ($actualAmount > 0 ? 100.0 : 0.0);

            // Status category
            $status = 'safe'; // green
            if ($absorptionRate >= 100.0) {
                $status = 'exceeded'; // red
            } elseif ($absorptionRate >= (float) $budget->warning_threshold_pct) {
                $status = 'warning'; // yellow
            }

            $items[] = [
                'line_id' => $line->id,
                'account_id' => $line->account_id,
                'account_code' => $line->account->code ?? '-',
                'account_name' => $line->account->name ?? '-',
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
}
