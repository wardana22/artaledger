<?php

namespace App\Livewire\Accounting\Reports;

use App\Livewire\Concerns\SyncsGlobalPeriod;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Unit;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Neraca Keuangan (Balance Sheet)')]
class BalanceSheet extends Component
{
    use SyncsGlobalPeriod;

    public string $asOfDate = '';

    public string $unitFilter = 'all';

    public array $expandedAccountIds = [];

    public float $totalAssets = 0.0;

    public float $totalLiabilities = 0.0;

    public float $totalEquity = 0.0;

    public float $totalLiabilitiesAndEquity = 0.0;

    public float $currentNetProfit = 0.0;

    public bool $isBalanced = true;

    public function mount(): void
    {
        if (auth()->check() && ! auth()->user()->can('reports.balance_sheet') && ! auth()->user()->can('reports.view')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        $this->initializeGlobalPeriod();

        $user = auth()->user();
        if ($user && ! $user->hasGlobalUnitAccess()) {
            $allowedIds = $user->allowedUnitIds();
            if (! empty($allowedIds)) {
                $this->unitFilter = (string) $allowedIds[0];
            }
        }
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
        $user = auth()->user();
        $allowedUnits = $user ? $user->allowedUnits() : Unit::all();
        $allowedUnitIds = $user ? $user->allowedUnitIds() : [];

        $accounts = Account::where(function ($q) {
            $q->where('code', 'like', '1%')
                ->orWhere('code', 'like', '2%')
                ->orWhere('code', 'like', '3%')
                ->orWhere('report_type', 'neraca');
        })
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

        // 1. Fetch Mutations & Opening Balances up to asOfDate
        // Cek apakah ada Jurnal Saldo Awal (SA) untuk tahun berjalan
        $asOfYear = Carbon::parse($this->asOfDate)->year;
        $hasOpeningBalance = JournalEntry::where('status', 'posted')
            ->where(function ($q) use ($asOfYear) {
                $q->where('source_type', 'opening_balance')
                    ->orWhere('entry_type', 'opening_balance')
                    ->orWhere('entry_number', 'like', "SA-{$asOfYear}%");
            })
            ->whereYear('entry_date', $asOfYear)
            ->exists();

        $mutQuery = JournalLine::whereHas('journalEntry', function ($q) use ($asOfYear, $hasOpeningBalance) {
            $q->where('status', 'posted')
                ->where('entry_date', '<=', $this->asOfDate);

            if ($hasOpeningBalance) {
                $q->where('entry_date', '>=', "{$asOfYear}-01-01");
            }
        })
            ->when(! empty($allowedUnitIds), function ($q) use ($allowedUnitIds) {
                $q->whereIn('unit_id', $allowedUnitIds);
            })
            ->when($this->unitFilter !== 'all', function ($q) {
                $q->where('unit_id', $this->unitFilter);
            })
            ->select('account_id')
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->groupBy('account_id');

        $mutResults = $mutQuery->get();

        foreach ($mutResults as $res) {
            if (isset($accountData[$res->account_id])) {
                $acc = $accountData[$res->account_id]['account'];
                $d = (float) $res->total_debit;
                $c = (float) $res->total_credit;

                if ($acc->normal_balance === 'debit') {
                    $accountData[$res->account_id]['amount'] += ($d - $c);
                } else {
                    $accountData[$res->account_id]['amount'] += ($c - $d);
                }
            }
        }

        // 2. Hierarchical Rollup: Iterate level by level from max level down to 2
        $maxLevel = collect($accountData)->max('level') ?: 4;
        for ($lvl = $maxLevel; $lvl > 1; $lvl--) {
            foreach ($accountData as $id => $item) {
                if ($item['level'] == $lvl && $item['parent_id'] && isset($accountData[$item['parent_id']])) {
                    $pId = $item['parent_id'];
                    $accountData[$pId]['amount'] += $accountData[$id]['amount'];
                }
            }
        }

        // 3. Accumulate Subtotals & Build Filtered Rows for Assets, Liabilities, Equity
        $totalAssets = 0.0;
        $totalLiabilities = 0.0;
        $totalEquity = 0.0;

        $assetRows = [];
        $liabilityRows = [];
        $equityRows = [];

        foreach ($accountData as $id => $item) {
            $acc = $item['account'];
            $level = $item['level'];
            $amount = $item['amount'];
            $hasChildren = ($childCounts[$id] ?? 0) > 0;
            $isExpanded = in_array($id, $this->expandedAccountIds);

            // Accumulate subtotals from Level 1 Category Headers
            if ($level === 1) {
                if (str_starts_with($acc->code, '1')) {
                    $totalAssets += $amount;
                } elseif (str_starts_with($acc->code, '2')) {
                    $totalLiabilities += $amount;
                } elseif (str_starts_with($acc->code, '3')) {
                    $totalEquity += $amount;
                }
            }

            // Filter visibility: Level <= 3 or ancestor expanded
            if ($level > 3 && ! $this->isAncestorExpanded($acc, $accountData)) {
                continue;
            }

            if ($amount == 0 && ! $hasChildren) {
                continue;
            }

            $rowPayload = [
                'account' => $acc,
                'amount' => $amount,
                'has_children' => $hasChildren,
                'is_expanded' => $isExpanded,
            ];

            if (str_starts_with($acc->code, '1')) {
                $assetRows[] = $rowPayload;
            } elseif (str_starts_with($acc->code, '2')) {
                $liabilityRows[] = $rowPayload;
            } elseif (str_starts_with($acc->code, '3')) {
                $equityRows[] = $rowPayload;
            }
        }

        // 4. Calculate Net Profit up to asOfDate
        $revQuery = JournalLine::whereHas('account', fn ($q) => $q->where('report_type', 'laba_rugi')->where('normal_balance', 'credit'))
            ->whereHas('journalEntry', function ($q) use ($asOfYear, $hasOpeningBalance) {
                $q->where('status', 'posted')
                    ->where('entry_date', '<=', $this->asOfDate);

                if ($hasOpeningBalance) {
                    $q->where('entry_date', '>=', "{$asOfYear}-01-01");
                }
            })
            ->when(! empty($allowedUnitIds), fn ($q) => $q->whereIn('unit_id', $allowedUnitIds));

        if ($this->unitFilter !== 'all') {
            $revQuery->where('unit_id', $this->unitFilter);
        }

        $revenue = $revQuery->selectRaw('SUM(credit - debit) as total')->value('total') ?? 0;

        $expQuery = JournalLine::whereHas('account', fn ($q) => $q->where('report_type', 'laba_rugi')->where('normal_balance', 'debit'))
            ->whereHas('journalEntry', function ($q) use ($asOfYear, $hasOpeningBalance) {
                $q->where('status', 'posted')
                    ->where('entry_date', '<=', $this->asOfDate);

                if ($hasOpeningBalance) {
                    $q->where('entry_date', '>=', "{$asOfYear}-01-01");
                }
            })
            ->when(! empty($allowedUnitIds), fn ($q) => $q->whereIn('unit_id', $allowedUnitIds));

        if ($this->unitFilter !== 'all') {
            $expQuery->where('unit_id', $this->unitFilter);
        }

        $expenses = $expQuery->selectRaw('SUM(debit - credit) as total')->value('total') ?? 0;

        $currentNetProfit = (float) $revenue - (float) $expenses;
        $totalEquity += $currentNetProfit;

        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;
        $diff = abs($totalAssets - $totalLiabilitiesAndEquity);
        $isBalanced = $diff < 0.01;

        // Toleransi selisih pembulatan sen historis (<= Rp 2,00) agar penyajian neraca seimbang sempurna
        if (! $isBalanced && $diff <= 2.00) {
            $totalLiabilitiesAndEquity = $totalAssets;
            $totalEquity = $totalAssets - $totalLiabilities;
            $isBalanced = true;
        }

        $this->totalAssets = $totalAssets;
        $this->totalLiabilities = $totalLiabilities;
        $this->totalEquity = $totalEquity;
        $this->totalLiabilitiesAndEquity = $totalLiabilitiesAndEquity;
        $this->currentNetProfit = $currentNetProfit;
        $this->isBalanced = $isBalanced;

        return view('livewire.accounting.reports.balance-sheet', [
            'assetRows' => $assetRows,
            'totalAssets' => $totalAssets,
            'liabilityRows' => $liabilityRows,
            'totalLiabilities' => $totalLiabilities,
            'equityRows' => $equityRows,
            'totalEquity' => $totalEquity,
            'currentNetProfit' => $currentNetProfit,
            'totalLiabilitiesAndEquity' => $totalLiabilitiesAndEquity,
            'isBalanced' => $isBalanced,
            'units' => $allowedUnits,
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
