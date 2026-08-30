<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class DashboardKpi extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'title',
        'source_type',
        'account_id',
        'account_type',
        'calculation_type',
        'color_theme',
        'icon',
        'order_index',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_index' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Calculate live financial value for this KPI card.
     */
    public function calculateValue(?int $unitId = null, int $month = 0, int $year = 0): float
    {
        $query = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts', 'journal_lines.account_id', '=', 'accounts.id')
            ->where('journal_entries.status', 'posted');

        if ($this->company_id) {
            $query->where('accounts.company_id', $this->company_id);
        }

        if ($unitId) {
            $query->where('journal_lines.unit_id', $unitId);
        }

        if ($this->source_type === 'account' && $this->account_id) {
            $query->where('journal_lines.account_id', $this->account_id);
        } elseif ($this->source_type === 'account_type' && $this->account_type) {
            $query->where('accounts.type', $this->account_type);
        }

        if ($year > 0) {
            if ($this->calculation_type === 'ending_balance') {
                if ($month > 0) {
                    $endDate = date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $year, $month)));
                    $query->whereDate('journal_entries.entry_date', '<=', $endDate);
                } else {
                    $query->whereDate('journal_entries.entry_date', '<=', "{$year}-12-31");
                }
            } else {
                $query->whereYear('journal_entries.entry_date', $year);
                if ($month > 0) {
                    $query->whereMonth('journal_entries.entry_date', $month);
                }
            }
        }

        $totalDebit = (float) (clone $query)->sum('journal_lines.debit');
        $totalCredit = (float) (clone $query)->sum('journal_lines.credit');

        return match ($this->calculation_type) {
            'debit_sum' => $totalDebit,
            'credit_sum' => $totalCredit,
            'period_mutation' => abs($totalDebit - $totalCredit),
            default => ($this->account_type === 'asset' || $this->account_type === 'expense' || ($this->account?->normal_balance === 'debit'))
                ? ($totalDebit - $totalCredit)
                : ($totalCredit - $totalDebit),
        };
    }
}
