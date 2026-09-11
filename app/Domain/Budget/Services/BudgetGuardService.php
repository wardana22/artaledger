<?php

namespace App\Domain\Budget\Services;

use App\Models\Budget;
use App\Models\BudgetLine;
use App\Models\JournalLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BudgetGuardService
{
    /**
     * Check whether a planned expense amount will trigger a budget warning.
     * Mode: Warning Only (user specified).
     *
     * @param  string|Carbon  $entryDate
     */
    public function evaluateSpending(int $accountId, float $amount, $entryDate, ?int $unitId = null): array
    {
        $date = $entryDate instanceof Carbon ? $entryDate : Carbon::parse($entryDate);
        $year = (int) $date->format('Y');
        $month = (int) $date->format('n');

        // Find active budget for the year
        $budget = Budget::query()
            ->active()
            ->forYear($year)
            ->first();

        if (! $budget) {
            return [
                'has_budget' => false,
                'status' => 'no_budget',
                'message' => 'Tidak ada anggaran aktif untuk tahun buku '.$year,
            ];
        }

        // Find matching budget line (first try exact unit, if null try company-wide null unit)
        $budgetLine = BudgetLine::query()
            ->where('budget_id', $budget->id)
            ->where('account_id', $accountId)
            ->where(function ($q) use ($unitId) {
                if ($unitId) {
                    $q->where('unit_id', $unitId)->orWhereNull('unit_id');
                } else {
                    $q->whereNull('unit_id');
                }
            })
            ->first();

        if (! $budgetLine) {
            return [
                'has_budget' => false,
                'status' => 'no_budget_line',
                'message' => 'Akun ini tidak memiliki plafon anggaran.',
            ];
        }

        // Calculate YTD or MTD actual spent
        $currentActual = (float) JournalLine::query()
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->whereYear('journal_entries.entry_date', $year)
            ->where('journal_lines.account_id', $accountId)
            ->when($budgetLine->unit_id, function ($q, $uId) {
                $q->where('journal_lines.unit_id', $uId);
            })
            ->sum(DB::raw('journal_lines.debit - journal_lines.credit'));

        $allocatedAnnual = (float) $budgetLine->annual_amount;
        $projectedTotal = $currentActual + $amount;
        $remainingBudget = $allocatedAnnual - $currentActual;
        $projectedRemaining = $allocatedAnnual - $projectedTotal;

        $projectedAbsorption = $allocatedAnnual > 0 ? ($projectedTotal / $allocatedAnnual) * 100 : 100.0;
        $threshold = (float) $budget->warning_threshold_pct;

        $isOverBudget = $projectedRemaining < 0;
        $isWarning = $projectedAbsorption >= $threshold && ! $isOverBudget;

        $status = 'safe';
        $warningMessage = null;

        if ($isOverBudget) {
            $status = 'exceeded';
            $overAmount = abs($projectedRemaining);
            $warningMessage = sprintf(
                'Perhatian: Transaksi ini melampaui sisa pagu anggaran tahunan sebesar Rp %s (Sisa pagu: Rp %s, Diajukan: Rp %s).',
                number_format($overAmount, 0, ',', '.'),
                number_format(max(0, $remainingBudget), 0, ',', '.'),
                number_format($amount, 0, ',', '.')
            );
        } elseif ($isWarning) {
            $status = 'warning';
            $warningMessage = sprintf(
                'Peringatan Serapan: Transaksi ini akan meningkatkan serapan anggaran menjadi %s%% (Ambang batas peringatan: %s%%). Sisa anggaran: Rp %s.',
                number_format($projectedAbsorption, 1, ',', '.'),
                number_format($threshold, 1, ',', '.'),
                number_format($remainingBudget, 0, ',', '.')
            );
        }

        return [
            'has_budget' => true,
            'budget_id' => $budget->id,
            'budget_name' => $budget->name,
            'annual_budget' => $allocatedAnnual,
            'current_spent' => $currentActual,
            'remaining_before' => $remainingBudget,
            'projected_spent' => $projectedTotal,
            'projected_remaining' => $projectedRemaining,
            'projected_absorption' => round($projectedAbsorption, 2),
            'status' => $status,
            'warning_message' => $warningMessage,
            'can_proceed' => true, // Warning only policy allows proceeding
        ];
    }
}
