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

    public function mount(): void
    {
        $this->startDate = date('Y-01-01');
        $this->endDate = date('Y-12-31');
    }

    public function render()
    {
        $accounts = Account::orderBy('code', 'asc')->get();

        $rows = [];
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

        foreach ($accounts as $acc) {
            $openingBalance = (float) $acc->opening_balance;

            // General Mutations (Non-adjustment entries)
            $genQuery = JournalLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) {
                    $q->where('status', 'posted')
                        ->where('entry_type', '!=', 'adjustment')
                        ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
                });

            if ($this->unitFilter !== 'all') {
                $genQuery->where('unit_id', $this->unitFilter);
            }

            $generalMutations = $genQuery->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')->first();

            $debitMutation = (float) ($generalMutations->total_debit ?? 0);
            $creditMutation = (float) ($generalMutations->total_credit ?? 0);

            // Adjustment Mutations (entry_type = adjustment)
            $adjQuery = JournalLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) {
                    $q->where('status', 'posted')
                        ->where('entry_type', 'adjustment')
                        ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
                });

            if ($this->unitFilter !== 'all') {
                $adjQuery->where('unit_id', $this->unitFilter);
            }

            $adjMutations = $adjQuery->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')->first();

            $adjDebit = (float) ($adjMutations->total_debit ?? 0);
            $adjCredit = (float) ($adjMutations->total_credit ?? 0);

            // 1. Trial Balance (Neraca Saldo Sebelum Penyesuaian)
            if ($acc->normal_balance === 'debit') {
                $tbBal = $openingBalance + ($debitMutation - $creditMutation);
                $tbDebit = $tbBal;
                $tbCredit = 0.0;
            } else {
                $tbBal = $openingBalance + ($creditMutation - $debitMutation);
                $tbCredit = $tbBal;
                $tbDebit = 0.0;
            }

            // Skip zero rows
            if ($openingBalance == 0 && $debitMutation == 0 && $creditMutation == 0 && $tbBal == 0 && $adjDebit == 0 && $adjCredit == 0) {
                continue;
            }

            // 2. Adjusted Trial Balance (Neraca Saldo Disesuaikan)
            if ($acc->normal_balance === 'debit') {
                $atbBal = $tbBal + ($adjDebit - $adjCredit);
                $atbDebit = $atbBal;
                $atbCredit = 0.0;
            } else {
                $atbBal = $tbBal + ($adjCredit - $adjDebit);
                $atbCredit = $atbBal;
                $atbDebit = 0.0;
            }

            // 3. Income Statement vs Balance Sheet split
            $isDebit = 0.0;
            $isCredit = 0.0;
            $bsDebit = 0.0;
            $bsCredit = 0.0;

            if ($acc->report_type === 'laba_rugi') {
                $isDebit = $atbDebit;
                $isCredit = $atbCredit;
            } else {
                $bsDebit = $atbDebit;
                $bsCredit = $atbCredit;
            }

            $totTbDebit += $tbDebit;
            $totTbCredit += $tbCredit;
            $totAdjDebit += $adjDebit;
            $totAdjCredit += $adjCredit;
            $totAtbDebit += $atbDebit;
            $totAtbCredit += $atbCredit;
            $totIsDebit += $isDebit;
            $totIsCredit += $isCredit;
            $totBsDebit += $bsDebit;
            $totBsCredit += $bsCredit;

            $rows[] = [
                'account' => $acc,
                'tb_debit' => $tbDebit,
                'tb_credit' => $tbCredit,
                'adj_debit' => $adjDebit,
                'adj_credit' => $adjCredit,
                'atb_debit' => $atbDebit,
                'atb_credit' => $atbCredit,
                'is_debit' => $isDebit,
                'is_credit' => $isCredit,
                'bs_debit' => $bsDebit,
                'bs_credit' => $bsCredit,
            ];
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
