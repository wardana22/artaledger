<?php

namespace App\Livewire\Accounting\OpeningBalance;

use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Unit;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Laporan Saldo Awal')]
class OpeningBalanceIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 25;

    public string $unitFilter = 'all';

    public ?int $periodId = null;

    public string $viewMode = 'balance_sheet'; // 'balance_sheet' (Post-Closing / Neraca Murni) atau 'all' (Pre-Closing / Kumulatif)

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedUnitFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPeriodId(): void
    {
        $this->resetPage();
    }

    public function updatedViewMode(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        if (auth()->check() && ! auth()->user()->can('reports.opening_balance') && ! auth()->user()->can('reports.view')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        $firstJournalDate = JournalEntry::min('entry_date');
        $firstPeriod = null;

        if ($firstJournalDate) {
            $firstPeriod = AccountingPeriod::where('start_date', '<=', $firstJournalDate)
                ->where('end_date', '>=', $firstJournalDate)
                ->first();
        }

        if (! $firstPeriod) {
            $firstPeriod = AccountingPeriod::orderBy('start_date', 'asc')->first();
        }

        $this->periodId = $firstPeriod?->id;

        $user = auth()->user();
        if ($user && ! $user->hasGlobalUnitAccess()) {
            $allowedIds = $user->allowedUnitIds();
            if (! empty($allowedIds)) {
                $this->unitFilter = (string) $allowedIds[0];
            }
        }
    }

    public function render()
    {
        $user = auth()->user();
        $allowedUnits = $user ? $user->allowedUnits() : Unit::all();
        $allowedUnitIds = $user ? $user->allowedUnitIds() : [];

        $periods = AccountingPeriod::orderBy('start_date', 'asc')->get();
        $selectedPeriod = $periods->firstWhere('id', $this->periodId) ?? $periods->first();

        $query = Account::active();

        if ($this->viewMode === 'balance_sheet') {
            $query->where('report_type', 'neraca');
        }

        if (! empty($this->search)) {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', $term)
                    ->orWhere('name', 'like', $term);
            });
        }

        $accounts = $query->orderBy('code', 'asc')->get();

        // Hitung laba bersih kumulatif periode lalu untuk mode neraca murni
        $priorNetProfit = 0.0;
        if ($this->viewMode === 'balance_sheet' && $selectedPeriod) {
            $nominalAccounts = Account::active()
                ->where('report_type', 'laba_rugi')
                ->where('is_group', false)
                ->get();

            $totRev = 0.0;
            $totExp = 0.0;

            foreach ($nominalAccounts as $nAcc) {
                $nomQuery = JournalLine::where('account_id', $nAcc->id)
                    ->whereHas('journalEntry', function ($q) use ($selectedPeriod) {
                        $q->where('status', 'posted')
                            ->where('entry_date', '<', $selectedPeriod->start_date);
                    })
                    ->when(! empty($allowedUnitIds), function ($q) use ($allowedUnitIds) {
                        $q->whereIn('unit_id', $allowedUnitIds);
                    });

                if ($this->unitFilter !== 'all') {
                    $nomQuery->where('unit_id', $this->unitFilter);
                }

                $nomTotals = $nomQuery->selectRaw('SUM(debit) as tot_debit, SUM(credit) as tot_credit')->first();
                $nD = (float) ($nomTotals->tot_debit ?? 0);
                $nC = (float) ($nomTotals->tot_credit ?? 0);

                if ($nAcc->normal_balance === 'credit') {
                    $totRev += ($nC - $nD);
                } else {
                    $totExp += ($nD - $nC);
                }
            }
            $priorNetProfit = $totRev - $totExp;
        }

        $linesCollection = collect();
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        // Cari akun Saldo Laba / Retained Earnings
        $retainedEarningsAccount = Account::where('report_type', 'neraca')
            ->where(function ($q) {
                $q->where('code', '31.02')
                    ->orWhere('name', 'like', '%Saldo Laba%')
                    ->orWhere('name', 'like', '%Laba Ditahan%');
            })
            ->first();

        foreach ($accounts as $acc) {
            $mutQuery = JournalLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($selectedPeriod) {
                    $q->where('status', 'posted')
                        ->where(function ($subQ) use ($selectedPeriod) {
                            $subQ->where(function ($obQ) use ($selectedPeriod) {
                                $obQ->where(function ($types) {
                                    $types->where('entry_number', 'like', 'SA-%')
                                        ->orWhere('entry_type', 'opening_balance')
                                        ->orWhere('source_type', 'opening_balance');
                                })->where('entry_date', '<=', $selectedPeriod->end_date);
                            })->orWhere(function ($regQ) use ($selectedPeriod) {
                                $regQ->where('entry_number', 'not like', 'SA-%')
                                    ->where('entry_type', '!=', 'opening_balance')
                                    ->where('source_type', '!=', 'opening_balance')
                                    ->where('entry_date', '<', $selectedPeriod->start_date);
                            });
                        });
                })
                ->when(! empty($allowedUnitIds), function ($q) use ($allowedUnitIds) {
                    $q->whereIn('unit_id', $allowedUnitIds);
                });

            if ($this->unitFilter !== 'all') {
                $mutQuery->where('unit_id', $this->unitFilter);
            }

            $totals = $mutQuery->selectRaw('SUM(debit) as tot_debit, SUM(credit) as tot_credit')->first();

            $d = (float) ($totals->tot_debit ?? 0);
            $c = (float) ($totals->tot_credit ?? 0);

            $extraCredit = 0.0;
            if ($this->viewMode === 'balance_sheet' && $retainedEarningsAccount && $acc->id === $retainedEarningsAccount->id) {
                $extraCredit = $priorNetProfit;
            }

            if ($d == 0 && $c == 0 && (float) $acc->opening_balance == 0 && $extraCredit == 0) {
                continue;
            }

            $debitVal = 0.0;
            $creditVal = 0.0;

            if ($acc->normal_balance === 'debit') {
                $debitVal = ($d - $c);
                $totalDebit += $debitVal;
            } else {
                $creditVal = ($c - $d) + $extraCredit;
                $totalCredit += $creditVal;
            }

            if ($debitVal == 0 && $creditVal == 0) {
                continue;
            }

            $linesCollection->push((object) [
                'id' => $acc->id,
                'account' => $acc,
                'debit' => $debitVal,
                'credit' => $creditVal,
            ]);
        }

        $batchDifference = abs($totalDebit - $totalCredit);

        // Jika selisih kecil akibat pembulatan desimal (<= Rp 2,00), ratakan untuk presentasi seimbang
        if ($this->viewMode === 'balance_sheet' && $batchDifference > 0 && $batchDifference <= 2.00) {
            $totalCredit = $totalDebit;
            $batchDifference = 0.0;
        }

        $page = $this->getPage();
        $paginatedLines = new LengthAwarePaginator(
            $linesCollection->slice(($page - 1) * $this->perPage, $this->perPage)->values(),
            $linesCollection->count(),
            $this->perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return view('livewire.accounting.opening-balance.opening-balance-index', [
            'lines' => $paginatedLines,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'batchDifference' => $batchDifference,
            'units' => $allowedUnits,
            'periods' => $periods,
            'selectedPeriod' => $selectedPeriod,
            'viewMode' => $this->viewMode,
        ]);
    }
}
