<?php

namespace App\Livewire\Accounting\Reports;

use App\Models\Account;
use App\Models\JournalLine;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan Laba Rugi (Profit & Loss) - ArtaLedger')]
class ProfitLoss extends Component
{
    public string $startDate = '';

    public string $endDate = '';

    public function mount(): void
    {
        $this->startDate = date('Y-01-01');
        $this->endDate = date('Y-12-31');
    }

    public function render()
    {
        $revenueAccounts = Account::where('report_type', 'laba_rugi')
            ->where('normal_balance', 'credit')
            ->posting()
            ->active()
            ->orderBy('code', 'asc')
            ->get();

        $expenseAccounts = Account::where('report_type', 'laba_rugi')
            ->where('normal_balance', 'debit')
            ->posting()
            ->active()
            ->orderBy('code', 'asc')
            ->get();

        $revenueRows = [];
        $totalRevenue = 0.0;

        foreach ($revenueAccounts as $acc) {
            $mutations = JournalLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) {
                    $q->where('status', 'posted')
                        ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
                })
                ->selectRaw('SUM(credit - debit) as amount')
                ->value('amount') ?? 0;

            $amount = (float) $mutations + (float) $acc->opening_balance;
            if ($amount != 0) {
                $totalRevenue += $amount;
                $revenueRows[] = ['account' => $acc, 'amount' => $amount];
            }
        }

        $expenseRows = [];
        $totalExpense = 0.0;

        foreach ($expenseAccounts as $acc) {
            $mutations = JournalLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) {
                    $q->where('status', 'posted')
                        ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
                })
                ->selectRaw('SUM(debit - credit) as amount')
                ->value('amount') ?? 0;

            $amount = (float) $mutations + (float) $acc->opening_balance;
            if ($amount != 0) {
                $totalExpense += $amount;
                $expenseRows[] = ['account' => $acc, 'amount' => $amount];
            }
        }

        $netProfit = $totalRevenue - $totalExpense;

        return view('livewire.accounting.reports.profit-loss', [
            'revenueRows' => $revenueRows,
            'totalRevenue' => $totalRevenue,
            'expenseRows' => $expenseRows,
            'totalExpense' => $totalExpense,
            'netProfit' => $netProfit,
        ]);
    }
}
