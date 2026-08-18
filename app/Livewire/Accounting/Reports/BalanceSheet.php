<?php

namespace App\Livewire\Accounting\Reports;

use App\Models\Account;
use App\Models\JournalLine;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan Neraca (Balance Sheet) - ArtaLedger')]
class BalanceSheet extends Component
{
    public string $asOfDate = '';

    public function mount(): void
    {
        $this->asOfDate = date('Y-12-31');
    }

    public function render()
    {
        // 1. Assets (code starts with 1)
        $assetAccounts = Account::where('code', 'like', '1%')->posting()->active()->orderBy('code', 'asc')->get();
        $assetRows = [];
        $totalAssets = 0.0;

        foreach ($assetAccounts as $acc) {
            $mutations = JournalLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) {
                    $q->where('status', 'posted')->where('entry_date', '<=', $this->asOfDate);
                })
                ->selectRaw('SUM(debit - credit) as amount')
                ->value('amount') ?? 0;

            $amount = (float) $mutations + (float) $acc->opening_balance;
            if ($amount != 0) {
                $totalAssets += $amount;
                $assetRows[] = ['account' => $acc, 'amount' => $amount];
            }
        }

        // 2. Liabilities (code starts with 2)
        $liabilityAccounts = Account::where('code', 'like', '2%')->posting()->active()->orderBy('code', 'asc')->get();
        $liabilityRows = [];
        $totalLiabilities = 0.0;

        foreach ($liabilityAccounts as $acc) {
            $mutations = JournalLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) {
                    $q->where('status', 'posted')->where('entry_date', '<=', $this->asOfDate);
                })
                ->selectRaw('SUM(credit - debit) as amount')
                ->value('amount') ?? 0;

            $amount = (float) $mutations + (float) $acc->opening_balance;
            if ($amount != 0) {
                $totalLiabilities += $amount;
                $liabilityRows[] = ['account' => $acc, 'amount' => $amount];
            }
        }

        // 3. Equity (code starts with 3)
        $equityAccounts = Account::where('code', 'like', '3%')->posting()->active()->orderBy('code', 'asc')->get();
        $equityRows = [];
        $totalEquity = 0.0;

        foreach ($equityAccounts as $acc) {
            $mutations = JournalLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) {
                    $q->where('status', 'posted')->where('entry_date', '<=', $this->asOfDate);
                })
                ->selectRaw('SUM(credit - debit) as amount')
                ->value('amount') ?? 0;

            $amount = (float) $mutations + (float) $acc->opening_balance;
            if ($amount != 0) {
                $totalEquity += $amount;
                $equityRows[] = ['account' => $acc, 'amount' => $amount];
            }
        }

        // Add Net Profit to Equity
        $revenue = JournalLine::whereHas('account', fn ($q) => $q->where('report_type', 'laba_rugi')->where('normal_balance', 'credit'))
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->where('entry_date', '<=', $this->asOfDate))
            ->selectRaw('SUM(credit - debit) as total')
            ->value('total') ?? 0;

        $expenses = JournalLine::whereHas('account', fn ($q) => $q->where('report_type', 'laba_rugi')->where('normal_balance', 'debit'))
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->where('entry_date', '<=', $this->asOfDate))
            ->selectRaw('SUM(debit - credit) as total')
            ->value('total') ?? 0;

        $currentNetProfit = (float) $revenue - (float) $expenses;
        $totalEquity += $currentNetProfit;

        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;
        $isBalanced = abs($totalAssets - $totalLiabilitiesAndEquity) < 0.01;

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
        ]);
    }
}
