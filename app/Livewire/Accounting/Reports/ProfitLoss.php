<?php

namespace App\Livewire\Accounting\Reports;

use App\Models\Account;
use App\Models\JournalLine;
use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan Laba Rugi (Profit & Loss) - ArtaLedger')]
class ProfitLoss extends Component
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
            $this->expandedAccountIds = array_diff($this->expandedAccountIds, [$accountId]);
        } else {
            $this->expandedAccountIds[] = $accountId;
        }
    }

    public function render()
    {
        $accounts = Account::where('report_type', 'laba_rugi')
            ->active()
            ->orderBy('code', 'asc')
            ->get();

        $accountData = [];
        $childCounts = [];

        foreach ($accounts as $acc) {
            $accountData[$acc->id] = [
                'account' => $acc,
                'id' => $acc->id,
                'parent_id' => $acc->parent_id,
                'level' => $acc->level ?? 1,
                'amount' => 0.0,
            ];

            if ($acc->parent_id) {
                $childCounts[$acc->parent_id] = ($childCounts[$acc->parent_id] ?? 0) + 1;
            }
        }

        // 1. Fetch Mutations for Period
        $mutResults = JournalLine::whereHas('journalEntry', function ($q) {
            $q->where('status', 'posted')
                ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
        })
            ->when($this->unitFilter !== 'all', function ($q) {
                $q->where('unit_id', $this->unitFilter);
            })
            ->whereIn('account_id', $accounts->pluck('id'))
            ->select('account_id')
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->groupBy('account_id')
            ->get();

        foreach ($mutResults as $res) {
            if (isset($accountData[$res->account_id])) {
                $acc = $accountData[$res->account_id]['account'];
                $d = (float) $res->total_debit;
                $c = (float) $res->total_credit;

                if ($acc->normal_balance === 'credit') {
                    $accountData[$res->account_id]['amount'] += ($c - $d);
                } else {
                    $accountData[$res->account_id]['amount'] += ($d - $c);
                }
            }
        }

        // 2. Hierarchical Rollup: Walk up parent chain for each posting account
        $leafAmounts = [];
        foreach ($accountData as $id => $item) {
            $leafAmounts[$id] = $item['amount'];
        }

        foreach ($leafAmounts as $id => $amt) {
            if ($amt == 0.0) {
                continue;
            }
            $currParentId = $accountData[$id]['parent_id'];
            $visited = [$id];
            while ($currParentId && isset($accountData[$currParentId]) && ! in_array($currParentId, $visited)) {
                $accountData[$currParentId]['amount'] += $amt;
                $visited[] = $currParentId;
                $currParentId = $accountData[$currParentId]['parent_id'];
            }
        }

        // 3. Section Totals based on Category Headers (Level 1)
        $totalRevenue = 0.0;
        $totalHpp = 0.0;
        $totalOperatingExpenses = 0.0;
        $otherRevenue = 0.0;
        $otherExpense = 0.0;
        $taxExpense = 0.0;

        foreach ($accountData as $id => $item) {
            $acc = $item['account'];
            if ($item['level'] === 1) {
                $codePrefix = substr($acc->code, 0, 1);
                if ($codePrefix === '4') {
                    $totalRevenue += $item['amount'];
                } elseif ($codePrefix === '5') {
                    $totalHpp += $item['amount'];
                } elseif ($codePrefix === '6') {
                    $totalOperatingExpenses += $item['amount'];
                } elseif ($codePrefix === '7') {
                    $otherRevenue += $item['amount'];
                } elseif ($codePrefix === '8') {
                    $otherExpense += $item['amount'];
                } elseif ($codePrefix === '9') {
                    $taxExpense += $item['amount'];
                }
            }
        }

        $grossProfit = $totalRevenue - $totalHpp;
        $operatingProfit = $grossProfit - $totalOperatingExpenses;
        $profitBeforeTax = $operatingProfit + $otherRevenue - $otherExpense;
        $netProfit = $profitBeforeTax - $taxExpense;

        // 4. Build Filtered Rows with 4-Column Layout Payload
        $rows = [];
        foreach ($accountData as $id => $item) {
            $acc = $item['account'];
            $level = $item['level'];
            $amount = $item['amount'];
            $hasChildren = ($childCounts[$id] ?? 0) > 0;
            $isExpanded = in_array($id, $this->expandedAccountIds);

            // Filter visibility: Level <= 3 or ancestor expanded
            if ($level > 3 && ! $this->isAncestorExpanded($acc, $accountData)) {
                continue;
            }

            if ($amount == 0 && ! $hasChildren) {
                continue;
            }

            $rows[] = [
                'account' => $acc,
                'level' => $level,
                'amount' => $amount,
                'has_children' => $hasChildren,
                'is_expanded' => $isExpanded,
                'rincian' => ! $hasChildren ? $amount : null,
                'total' => $hasChildren ? $amount : null,
            ];
        }

        $units = Unit::all();

        return view('livewire.accounting.reports.profit-loss', [
            'rows' => $rows,
            'totalRevenue' => $totalRevenue,
            'totalHpp' => $totalHpp,
            'grossProfit' => $grossProfit,
            'totalOperatingExpenses' => $totalOperatingExpenses,
            'operatingProfit' => $operatingProfit,
            'otherRevenue' => $otherRevenue,
            'otherExpense' => $otherExpense,
            'profitBeforeTax' => $profitBeforeTax,
            'taxExpense' => $taxExpense,
            'netProfit' => $netProfit,
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
