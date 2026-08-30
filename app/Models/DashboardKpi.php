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
        'account_group_id',
        'account_type',
        'calculation_type',
        'formula_expression',
        'color_theme',
        'display_format',
        'decimal_places',
        'icon',
        'order_index',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_index' => 'integer',
        'decimal_places' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function accountGroup(): BelongsTo
    {
        return $this->belongsTo(AccountGroup::class, 'account_group_id');
    }

    /**
     * Calculate live financial value for this KPI card.
     */
    public function calculateValue(?int $unitId = null, int $month = 0, int $year = 0): float
    {
        if ($this->source_type === 'formula') {
            return $this->evaluateFormula($unitId, $month, $year);
        }

        if ($this->source_type === 'account_group' && $this->account_group_id) {
            return $this->accountGroup?->calculateTotalBalance($unitId, $month, $year) ?? 0.0;
        }

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
            $typeKey = strtolower($this->account_type);
            if ($typeKey === 'revenue' || $typeKey === 'pendapatan') {
                $query->where(function ($q) {
                    $q->whereIn('accounts.type', ['PENDAPATAN', 'PENDAPATAN LAINNYA', 'revenue'])
                        ->orWhere('accounts.code', 'like', '4%');
                });
            } elseif ($typeKey === 'expense' || $typeKey === 'beban') {
                $query->where(function ($q) {
                    $q->whereIn('accounts.type', ['BEBAN', 'BEBAN LAIN-LAIN', 'HPP', 'expense'])
                        ->orWhere('accounts.code', 'like', '5%')
                        ->orWhere('accounts.code', 'like', '6%')
                        ->orWhere('accounts.code', 'like', '7%')
                        ->orWhere('accounts.code', 'like', '8%')
                        ->orWhere('accounts.code', 'like', '9%');
                });
            } elseif ($typeKey === 'asset' || $typeKey === 'aktiva' || $typeKey === 'aset') {
                $query->where(function ($q) {
                    $q->whereIn('accounts.type', ['KAS', 'BANK', 'AKTIVA LANCAR LAINNYA', 'PIUTANG', 'PERSEDIAAN', 'AKTIVA TETAP', 'asset'])
                        ->orWhere('accounts.code', 'like', '1%');
                });
            } elseif ($typeKey === 'liability' || $typeKey === 'kewajiban' || $typeKey === 'hutang') {
                $query->where(function ($q) {
                    $q->whereIn('accounts.type', ['HUTANG LANCAR', 'HUTANG JANGKA PANJANG', 'LIABILITY', 'liability'])
                        ->orWhere('accounts.code', 'like', '2%');
                });
            } elseif ($typeKey === 'equity' || $typeKey === 'ekuitas' || $typeKey === 'modal') {
                $query->where(function ($q) {
                    $q->whereIn('accounts.type', ['MODAL', 'equity'])
                        ->orWhere('accounts.code', 'like', '3%');
                });
            } else {
                $query->where('accounts.type', $this->account_type);
            }
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

    /**
     * Safely evaluate mathematical formula expression (+, -, *, /).
     */
    public function evaluateFormula(?int $unitId = null, int $month = 0, int $year = 0): float
    {
        if (empty($this->formula_expression)) {
            return 0.0;
        }

        $expr = $this->formula_expression;

        // Replace Account Group or Account variables [VAR]
        $expr = preg_replace_callback('/\[([A-Z0-9_\.-]+)\]/i', function ($matches) use ($unitId, $month, $year) {
            $key = $matches[1];

            // Check Account Group
            $group = AccountGroup::where('company_id', $this->company_id)
                ->where('code', $key)
                ->first();

            if ($group) {
                return (string) $group->calculateTotalBalance($unitId, $month, $year);
            }

            // Check Account Code
            $account = Account::where('company_id', $this->company_id)
                ->where('code', $key)
                ->first();

            if ($account) {
                $tempKpi = new self([
                    'company_id' => $this->company_id,
                    'source_type' => 'account',
                    'account_id' => $account->id,
                    'calculation_type' => 'ending_balance',
                ]);

                return (string) $tempKpi->calculateValue($unitId, $month, $year);
            }

            return '0';
        }, $expr);

        // Sanitize formula
        $cleanedExpr = preg_replace('/[^0-9\+\-\*\/\(\)\.\s]/', '', $expr);

        if (empty(trim($cleanedExpr))) {
            return 0.0;
        }

        try {
            $result = 0.0;
            eval('$result = ('.$cleanedExpr.');');

            return is_numeric($result) && ! is_nan($result) && ! is_infinite($result) ? (float) $result : 0.0;
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    /**
     * Format output string based on display_format setting.
     */
    public function formatDisplayValue(float $value): string
    {
        $decimals = $this->decimal_places ?? 0;
        $formattedNum = number_format($value, $decimals, ',', '.');

        return match ($this->display_format) {
            'percentage' => $formattedNum.'%',
            'days' => $formattedNum.' Hari',
            'times' => $formattedNum.'x',
            'number' => $formattedNum,
            default => 'Rp '.$formattedNum,
        };
    }
}
