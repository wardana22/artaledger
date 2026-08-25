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

    public function mount(): void
    {
        $this->startDate = date('Y-01-01');
        $this->endDate = date('Y-12-31');
    }

    public function render()
    {
        $accounts = Account::orderBy('code', 'asc')->get();

        $rows = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($accounts as $acc) {
            $openingBalance = (float) $acc->opening_balance;

            $mutQuery = JournalLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) {
                    $q->where('status', 'posted')
                        ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
                });

            if ($this->unitFilter !== 'all') {
                $mutQuery->where('unit_id', $this->unitFilter);
            }

            $mutations = $mutQuery->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')->first();

            $debitMutation = (float) ($mutations->total_debit ?? 0);
            $creditMutation = (float) ($mutations->total_credit ?? 0);

            if ($acc->normal_balance === 'debit') {
                $endingBalance = $openingBalance + ($debitMutation - $creditMutation);
                $finalDebit = $endingBalance;
                $finalCredit = 0.0;
            } else {
                $endingBalance = $openingBalance + ($creditMutation - $debitMutation);
                $finalCredit = $endingBalance;
                $finalDebit = 0.0;
            }

            // Skip zero balance accounts if no mutation
            if ($openingBalance == 0 && $debitMutation == 0 && $creditMutation == 0 && $endingBalance == 0) {
                continue;
            }

            $totalDebit += $finalDebit;
            $totalCredit += $finalCredit;

            $rows[] = [
                'account' => $acc,
                'opening_balance' => $openingBalance,
                'debit_mutation' => $debitMutation,
                'credit_mutation' => $creditMutation,
                'final_debit' => $finalDebit,
                'final_credit' => $finalCredit,
            ];
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
}
