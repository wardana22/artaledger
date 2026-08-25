<?php

namespace App\Livewire\Accounting\Reports;

use App\Models\Account;
use App\Models\JournalLine;
use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Neraca Lajur (Worksheet) - ArtaLedger')]
class Worksheet extends Component
{
    public string $startDate = '';

    public string $endDate = '';

    public string $unitFilter = 'all';

    public array $expandedAccountIds = [];

    public function mount(): void
    {
        $this->startDate = date('Y-01-01');
        $this->endDate = date('Y-12-31');
    }

    public function toggleExpand(int $accountId): void
    {
        if (in_array($accountId, $this->expandedAccountIds)) {
            $this->expandedAccountIds = array_values(array_diff($this->expandedAccountIds, [$accountId]));
        } else {
            $this->expandedAccountIds[] = $accountId;
        }
    }

    public function render()
    {
        $accounts = Account::orderBy('code', 'asc')->get();

        // 1. Build calculation map for each account
        $accountData = [];
        $childCounts = [];

        foreach ($accounts as $acc) {
            $accountData[$acc->id] = [
                'account' => $acc,
                'id' => $acc->id,
                'parent_id' => $acc->parent_id,
                'level' => $acc->level ?? 1,
                'opening_balance' => (float) $acc->opening_balance,
                'debit_mutation' => 0.0,
                'credit_mutation' => 0.0,
                'adj_debit' => 0.0,
                'adj_credit' => 0.0,
            ];

            if ($acc->parent_id) {
                $childCounts[$acc->parent_id] = ($childCounts[$acc->parent_id] ?? 0) + 1;
            }
        }

        // 2. Fetch General Mutations (posted, non-adjustment entries) grouped by account_id
        $genQuery = JournalLine::whereHas('journalEntry', function ($q) {
            $q->where('status', 'posted')
                ->where('entry_type', '!=', 'adjustment')
                ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
        });

        if ($this->unitFilter !== 'all') {
            $genQuery->where('unit_id', $this->unitFilter);
        }

        $genResults = $genQuery->select('account_id')
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->groupBy('account_id')
            ->get();

        foreach ($genResults as $res) {
            if (isset($accountData[$res->account_id])) {
                $accountData[$res->account_id]['debit_mutation'] = (float) $res->total_debit;
                $accountData[$res->account_id]['credit_mutation'] = (float) $res->total_credit;
            }
        }

        // 3. Fetch Adjustment Mutations (posted, entry_type = adjustment) grouped by account_id
        $adjQuery = JournalLine::whereHas('journalEntry', function ($q) {
            $q->where('status', 'posted')
                ->where('entry_type', 'adjustment')
                ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
        });

        if ($this->unitFilter !== 'all') {
            $adjQuery->where('unit_id', $this->unitFilter);
        }

        $adjResults = $adjQuery->select('account_id')
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->groupBy('account_id')
            ->get();

        foreach ($adjResults as $res) {
            if (isset($accountData[$res->account_id])) {
                $accountData[$res->account_id]['adj_debit'] = (float) $res->total_debit;
                $accountData[$res->account_id]['adj_credit'] = (float) $res->total_credit;
            }
        }

        // 4. Hierarchical Rollup: Iterate level by level from highest to lowest (e.g. Level 5, 4, 3, 2)
        $maxLevel = collect($accountData)->max('level') ?: 4;
        for ($lvl = $maxLevel; $lvl > 1; $lvl--) {
            foreach ($accountData as $id => $item) {
                if ($item['level'] == $lvl && $item['parent_id'] && isset($accountData[$item['parent_id']])) {
                    $pId = $item['parent_id'];
                    $accountData[$pId]['opening_balance'] += $accountData[$id]['opening_balance'];
                    $accountData[$pId]['debit_mutation'] += $accountData[$id]['debit_mutation'];
                    $accountData[$pId]['credit_mutation'] += $accountData[$id]['credit_mutation'];
                    $accountData[$pId]['adj_debit'] += $accountData[$id]['adj_debit'];
                    $accountData[$pId]['adj_credit'] += $accountData[$id]['adj_credit'];
                }
            }
        }

        // 5. Calculate 10-column values for each account
        $totTbDebit = 0.0;
        $totTbCredit = 0.0;
        $totAdjDebit = 0.0;
        $totAdjCredit = 0.0;
        $totAtbDebit = 0.0;
        $totAtbCredit = 0.0;
        $totIsDebit = 0.0;
        $totIsCredit = 0.0;
        $totBsDebit = 0.0;
        $totBsCredit = 0.0;

        foreach ($accountData as $id => &$item) {
            $acc = $item['account'];
            $opBal = $item['opening_balance'];
            $debMut = $item['debit_mutation'];
            $credMut = $item['credit_mutation'];
            $adjDeb = $item['adj_debit'];
            $adjCred = $item['adj_credit'];

            if ($acc->normal_balance === 'debit') {
                $tbBal = $opBal + ($debMut - $credMut);
                $tbDebit = $tbBal > 0 ? $tbBal : 0.0;
                $tbCredit = $tbBal < 0 ? abs($tbBal) : 0.0;

                $atbBal = $tbBal + ($adjDeb - $adjCred);
                $atbDebit = $atbBal > 0 ? $atbBal : 0.0;
                $atbCredit = $atbBal < 0 ? abs($atbBal) : 0.0;
            } else {
                $tbBal = $opBal + ($credMut - $debMut);
                $tbCredit = $tbBal > 0 ? $tbBal : 0.0;
                $tbDebit = $tbBal < 0 ? abs($tbBal) : 0.0;

                $atbBal = $tbBal + ($adjCred - $adjDeb);
                $atbCredit = $atbBal > 0 ? $atbBal : 0.0;
                $atbDebit = $atbBal < 0 ? abs($atbBal) : 0.0;
            }

            $isDebit = $acc->report_type === 'laba_rugi' ? $atbDebit : 0.0;
            $isCredit = $acc->report_type === 'laba_rugi' ? $atbCredit : 0.0;
            $bsDebit = $acc->report_type !== 'laba_rugi' ? $atbDebit : 0.0;
            $bsCredit = $acc->report_type !== 'laba_rugi' ? $atbCredit : 0.0;

            $item['tb_debit'] = $tbDebit;
            $item['tb_credit'] = $tbCredit;
            $item['adj_debit'] = $adjDeb;
            $item['adj_credit'] = $adjCred;
            $item['atb_debit'] = $atbDebit;
            $item['atb_credit'] = $atbCredit;
            $item['is_debit'] = $isDebit;
            $item['is_credit'] = $isCredit;
            $item['bs_debit'] = $bsDebit;
            $item['bs_credit'] = $bsCredit;
            $item['has_activity'] = ($opBal != 0 || $debMut != 0 || $credMut != 0 || $adjDeb != 0 || $adjCred != 0 || $tbBal != 0 || $atbBal != 0);

            // Accumulate grand totals ONLY from posting (leaf) accounts to avoid double counting
            if (! $acc->is_group) {
                $totTbDebit += $tbDebit;
                $totTbCredit += $tbCredit;
                $totAdjDebit += $adjDeb;
                $totAdjCredit += $adjCred;
                $totAtbDebit += $atbDebit;
                $totAtbCredit += $atbCredit;
                $totIsDebit += $isDebit;
                $totIsCredit += $isCredit;
                $totBsDebit += $bsDebit;
                $totBsCredit += $bsCredit;
            }
        }
        unset($item);

        // 6. Build visible rows for the template according to Level <= 3 and Expand state
        $rows = [];
        foreach ($accountData as $id => $item) {
            $acc = $item['account'];
            $level = $item['level'];
            $hasChildren = ($childCounts[$id] ?? 0) > 0;
            $isExpanded = in_array($id, $this->expandedAccountIds);

            // Skip zero activity accounts
            if (! $item['has_activity']) {
                continue;
            }

            // By default, display Level 1, 2, and 3
            if ($level <= 3) {
                $rows[] = array_merge($item, [
                    'has_children' => $hasChildren,
                    'is_expanded' => $isExpanded,
                ]);
            } else {
                // For Level 4+, show if its direct parent_id is expanded
                if ($acc->parent_id && in_array($acc->parent_id, $this->expandedAccountIds)) {
                    $rows[] = array_merge($item, [
                        'has_children' => $hasChildren,
                        'is_expanded' => $isExpanded,
                    ]);
                }
            }
        }

        $netProfitFromIs = $totIsCredit - $totIsDebit;
        $netProfitFromBs = $totBsDebit - $totBsCredit;
        $units = Unit::all();

        return view('livewire.accounting.reports.worksheet', [
            'rows' => $rows,
            'totTbDebit' => $totTbDebit,
            'totTbCredit' => $totTbCredit,
            'totAdjDebit' => $totAdjDebit,
            'totAdjCredit' => $totAdjCredit,
            'totAtbDebit' => $totAtbDebit,
            'totAtbCredit' => $totAtbCredit,
            'totIsDebit' => $totIsDebit,
            'totIsCredit' => $totIsCredit,
            'totBsDebit' => $totBsDebit,
            'totBsCredit' => $totBsCredit,
            'netProfitFromIs' => $netProfitFromIs,
            'netProfitFromBs' => $netProfitFromBs,
            'units' => $units,
        ]);
    }
}
