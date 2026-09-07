<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use NXP\MathExecutor;

class CashFlowRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'section',
        'label',
        'source_type',
        'account_id',
        'account_group_id',
        'counter_account_group_id',
        'calculation_type',
        'formula_expression',
        'operator_sign',
        'order_index',
        'is_active',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'is_active' => 'boolean',
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

    public function counterAccountGroup(): BelongsTo
    {
        return $this->belongsTo(AccountGroup::class, 'counter_account_group_id');
    }

    /**
     * Calculate financial value for this Cash Flow Row.
     */
    public function calculateValue(string $startDate, string $endDate, ?string $unitId = null): float
    {
        if ($this->source_type === 'formula' && ! empty($this->formula_expression)) {
            $value = $this->evaluateFormula($startDate, $endDate, $unitId);
        } elseif ($this->source_type === 'account_group' && $this->account_group_id) {
            $value = $this->calculateGroupValue($this->account_group_id, $startDate, $endDate, $unitId);
        } elseif ($this->source_type === 'account' && $this->account_id) {
            $value = $this->calculateSingleAccountValue($this->account_id, $startDate, $endDate, $unitId);
        } else {
            $value = 0.0;
        }

        return $value;
    }

    /**
     * Calculate group value.
     */
    public function calculateGroupValue(int $groupId, string $startDate, string $endDate, ?string $unitId = null): float
    {
        $group = AccountGroup::find($groupId);
        if (! $group) {
            return 0.0;
        }

        $accountIds = DB::table('account_group_members')
            ->where('account_group_id', $groupId)
            ->pluck('account_id')
            ->filter()
            ->unique()
            ->toArray();

        if (empty($accountIds)) {
            return 0.0;
        }

        $query = DB::table('journal_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->whereIn('journal_lines.account_id', $accountIds)
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_number', 'not like', 'SA%')
            ->whereBetween('journal_entries.entry_date', [$startDate, $endDate]);

        if ($unitId && $unitId !== 'all') {
            $query->where('journal_lines.unit_id', $unitId);
        }

        if ($this->calculation_type === 'debit_only') {
            return (float) $query->sum('journal_lines.debit');
        } elseif ($this->calculation_type === 'credit_only') {
            return (float) $query->sum('journal_lines.credit');
        }

        // Default net mutation: determine based on normal balance of first account
        $firstAcc = Account::find($accountIds[0]);
        $isDebitNormal = $firstAcc ? ($firstAcc->normal_balance === 'debit') : true;

        $debit = (float) $query->sum('journal_lines.debit');
        $credit = (float) $query->sum('journal_lines.credit');

        return $isDebitNormal ? ($debit - $credit) : ($credit - $debit);
    }

    /**
     * Calculate single account value.
     */
    public function calculateSingleAccountValue(int $accountId, string $startDate, string $endDate, ?string $unitId = null): float
    {
        $acc = Account::find($accountId);
        if (! $acc) {
            return 0.0;
        }

        $query = DB::table('journal_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_lines.account_id', $accountId)
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_number', 'not like', 'SA%')
            ->whereBetween('journal_entries.entry_date', [$startDate, $endDate]);

        if ($unitId && $unitId !== 'all') {
            $query->where('journal_lines.unit_id', $unitId);
        }

        if ($this->calculation_type === 'debit_only') {
            return (float) $query->sum('journal_lines.debit');
        } elseif ($this->calculation_type === 'credit_only') {
            return (float) $query->sum('journal_lines.credit');
        }

        $debit = (float) $query->sum('journal_lines.debit');
        $credit = (float) $query->sum('journal_lines.credit');

        return ($acc->normal_balance === 'debit') ? ($debit - $credit) : ($credit - $debit);
    }

    /**
     * Evaluate dynamic mathematical formula expression like `[GROUP:4] - [GROUP:5]`.
     */
    public function evaluateFormula(string $startDate, string $endDate, ?string $unitId = null): float
    {
        $expression = $this->formula_expression;
        if (empty($expression)) {
            return 0.0;
        }

        $executor = new MathExecutor;
        $cleanExpression = $expression;

        // 1. Replace [GROUP:id] tags
        preg_match_all('/\[GROUP:(\d+)\]/', $expression, $matchesGroup);
        if (! empty($matchesGroup[1])) {
            foreach ($matchesGroup[1] as $idx => $groupId) {
                $varName = 'group_var_'.$groupId;
                $val = $this->calculateGroupValue((int) $groupId, $startDate, $endDate, $unitId);
                $executor->setVar($varName, $val);
                $cleanExpression = str_replace($matchesGroup[0][$idx], $varName, $cleanExpression);
            }
        }

        // 2. Replace [ACCOUNT:id] tags
        preg_match_all('/\[ACCOUNT:(\d+)\]/', $expression, $matchesAcc);
        if (! empty($matchesAcc[1])) {
            foreach ($matchesAcc[1] as $idx => $accId) {
                $varName = 'acc_var_'.$accId;
                $val = $this->calculateSingleAccountValue((int) $accId, $startDate, $endDate, $unitId);
                $executor->setVar($varName, $val);
                $cleanExpression = str_replace($matchesAcc[0][$idx], $varName, $cleanExpression);
            }
        }

        try {
            return (float) $executor->execute($cleanExpression);
        } catch (\Throwable) {
            return 0.0;
        }
    }

    /**
     * Get constituent accounts breakdown for interactive drilldown.
     */
    public function getBreakdownAccounts(string $startDate, string $endDate, ?string $unitId = null): array
    {
        $accountIds = [];

        if ($this->source_type === 'formula' && ! empty($this->formula_expression)) {
            // Extract from groups and accounts in formula
            preg_match_all('/\[GROUP:(\d+)\]/', $this->formula_expression, $mg);
            if (! empty($mg[1])) {
                foreach ($mg[1] as $gid) {
                    $gAccs = DB::table('account_group_members')
                        ->where('account_group_id', (int) $gid)
                        ->pluck('account_id')
                        ->filter()
                        ->toArray();
                    $accountIds = array_merge($accountIds, $gAccs);
                }
            }
            preg_match_all('/\[ACCOUNT:(\d+)\]/', $this->formula_expression, $ma);
            if (! empty($ma[1])) {
                $accountIds = array_merge($accountIds, array_map('intval', $ma[1]));
            }
        } elseif ($this->source_type === 'account_group' && $this->account_group_id) {
            $accountIds = DB::table('account_group_members')
                ->where('account_group_id', $this->account_group_id)
                ->pluck('account_id')
                ->filter()
                ->toArray();
        } elseif ($this->source_type === 'account' && $this->account_id) {
            $accountIds = [$this->account_id];
        }

        $accountIds = array_unique($accountIds);
        if (empty($accountIds)) {
            return [];
        }

        $accounts = Account::whereIn('id', $accountIds)->orderBy('code')->get();

        $query = DB::table('journal_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->whereIn('journal_lines.account_id', $accountIds)
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_number', 'not like', 'SA%')
            ->whereBetween('journal_entries.entry_date', [$startDate, $endDate]);

        if ($unitId && $unitId !== 'all') {
            $query->where('journal_lines.unit_id', $unitId);
        }

        $results = $query->select(
            'journal_lines.account_id',
            DB::raw('SUM(journal_lines.debit) as total_debit'),
            DB::raw('SUM(journal_lines.credit) as total_credit')
        )->groupBy('journal_lines.account_id')->get()->keyBy('account_id');

        $breakdown = [];
        foreach ($accounts as $acc) {
            $res = $results->get($acc->id);
            $debit = (float) ($res->total_debit ?? 0);
            $credit = (float) ($res->total_credit ?? 0);
            $net = ($acc->normal_balance === 'debit') ? ($debit - $credit) : ($credit - $debit);

            if ($debit > 0 || $credit > 0) {
                $breakdown[] = [
                    'id' => $acc->id,
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'debit' => $debit,
                    'credit' => $credit,
                    'net' => $net,
                ];
            }
        }

        return $breakdown;
    }
}
