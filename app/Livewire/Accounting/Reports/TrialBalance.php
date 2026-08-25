<?php

namespace App\Livewire\Accounting\Reports;

use App\Models\Account;
use App\Models\JournalLine;
use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Neraca Saldo (Trial Balance) - ArtaLedger')]
class TrialBalance extends Component
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

    public function toggleAccount(int $accountId): void
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

        $accountData = [];
        $childCounts = [];

        foreach ($accounts as $acc) {
            $accountData[$acc->id] = [
                'account' => $acc,
                'id' => $acc->id,
                'parent_id' => $acc->parent_id,
                'level' => $acc->level ?? 1,
                'opening_balance' => 0.0,
                'debit_mutation' => 0.0,
                'credit_mutation' => 0.0,
            ];

            if ($acc->parent_id) {
                $childCounts[$acc->parent_id] = ($childCounts[$acc->parent_id] ?? 0) + 1;
            }
        }

        // 1. Fetch Opening Balances from Journal Lines
        $opResults = JournalLine::whereHas('journalEntry', function ($q) {
            $q->where('status', 'posted')
                ->where(function ($query) {
                    $query->where('entry_type', 'opening_balance')
                        ->orWhere('entry_date', '<', $this->startDate);
                });
        })
            ->when($this->unitFilter !== 'all', function ($q) {
                $q->where('unit_id', $this->unitFilter);
            })
            ->select('account_id')
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->groupBy('account_id')
            ->get();

        foreach ($opResults as $res) {
            if (isset($accountData[$res->account_id])) {
                $acc = $accountData[$res->account_id]['account'];
                $d = (float) $res->total_debit;
                $c = (float) $res->total_credit;
                if ($acc->normal_balance === 'debit') {
                    $accountData[$res->account_id]['opening_balance'] = $d - $c;
                } else {
                    $accountData[$res->account_id]['opening_balance'] = $c - $d;
                }
            }
        }

        // 2. Fetch Period Mutations
        $mutQuery = JournalLine::whereHas('journalEntry', function ($q) {
            $q->where('status', 'posted')
                ->where('entry_type', '!=', 'opening_balance')
                ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
        });

        if ($this->unitFilter !== 'all') {
            $mutQuery->where('unit_id', $this->unitFilter);
        }

        $mutResults = $mutQuery->select('account_id')
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->groupBy('account_id')
            ->get();

        foreach ($mutResults as $res) {
            if (isset($accountData[$res->account_id])) {
                $accountData[$res->account_id]['debit_mutation'] = (float) $res->total_debit;
                $accountData[$res->account_id]['credit_mutation'] = (float) $res->total_credit;
            }
        }

        // 3. Hierarchical Rollup: Iterate level by level from max level down to 2
        $maxLevel = collect($accountData)->max('level') ?: 4;
        for ($lvl = $maxLevel; $lvl > 1; $lvl--) {
            foreach ($accountData as $id => $item) {
                if ($item['level'] == $lvl && $item['parent_id'] && isset($accountData[$item['parent_id']])) {
                    $pId = $item['parent_id'];
                    $accountData[$pId]['opening_balance'] += $accountData[$id]['opening_balance'];
                    $accountData[$pId]['debit_mutation'] += $accountData[$id]['debit_mutation'];
                    $accountData[$pId]['credit_mutation'] += $accountData[$id]['credit_mutation'];
                }
            }
        }

        // 4. Calculate Final Balances and Accumulate Grand Totals
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($accountData as $id => &$item) {
            $acc = $item['account'];
            $opBal = $item['opening_balance'];
            $debMut = $item['debit_mutation'];
            $credMut = $item['credit_mutation'];

            if ($acc->normal_balance === 'debit') {
                $endingBalance = $opBal + ($debMut - $credMut);
                $finalDebit = $endingBalance > 0 ? $endingBalance : 0.0;
                $finalCredit = $endingBalance < 0 ? abs($endingBalance) : 0.0;
            } else {
                $endingBalance = $opBal + ($credMut - $debMut);
                $finalCredit = $endingBalance > 0 ? $endingBalance : 0.0;
                $finalDebit = $endingBalance < 0 ? abs($endingBalance) : 0.0;
            }

            $item['final_debit'] = $finalDebit;
            $item['final_credit'] = $finalCredit;
            $item['has_activity'] = ($opBal != 0 || $debMut != 0 || $credMut != 0 || $endingBalance != 0);

            $hasChildren = ($childCounts[$id] ?? 0) > 0;

            if (! $hasChildren) {
                $totalDebit += $finalDebit;
                $totalCredit += $finalCredit;
            }
        }
        unset($item);

        // 5. Build Visible Rows based on Level <= 3 and Expand state
        $rows = [];
        foreach ($accountData as $id => $item) {
            $acc = $item['account'];
            $level = $item['level'];
            $hasChildren = ($childCounts[$id] ?? 0) > 0;
            $isExpanded = in_array($id, $this->expandedAccountIds);

            if ($level > 3 && ! $this->isAncestorExpanded($acc, $accountData)) {
                continue;
            }

            if (! $item['has_activity']) {
                continue;
            }

            $rows[] = array_merge($item, [
                'has_children' => $hasChildren,
                'is_expanded' => $isExpanded,
            ]);
        }

        $isBalanced = abs($totalDebit - $totalCredit) < 0.01;
        $units = Unit::all();

        return view('livewire.accounting.reports.trial-balance', [
            'rows' => $rows,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'isBalanced' => $isBalanced,
            'units' => $units,
        ]);
    }

    private function isAncestorExpanded(Account $account, array $accountData): bool
    {
        $currentParentId = $account->parent_id;

        while ($currentParentId) {
            if (! isset($accountData[$currentParentId])) {
                break;
            }

            $parentItem = $accountData[$currentParentId];
            if ($parentItem['level'] >= 3) {
                if (! in_array($currentParentId, $this->expandedAccountIds)) {
                    return false;
                }
            }

            $currentParentId = $parentItem['parent_id'];
        }

        return true;
    }
}
