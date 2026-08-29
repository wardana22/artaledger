<?php

namespace App\Livewire\Accounting\OpeningBalance;

use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\JournalLine;
use App\Models\Unit;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Laporan Saldo Awal - ArtaLedger')]
class OpeningBalanceIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 25;

    public string $unitFilter = 'all';

    public ?int $periodId = null;

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

    public function mount(): void
    {
        if (auth()->check() && ! auth()->user()->can('reports.opening_balance') && ! auth()->user()->can('reports.view')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        $firstPeriod = AccountingPeriod::orderBy('start_date', 'asc')->first();
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

        $startDate = $selectedPeriod ? $selectedPeriod->start_date : date('Y-01-01');

        $query = Account::active();

        if (! empty($this->search)) {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', $term)
                    ->orWhere('name', 'like', $term);
            });
        }

        $accounts = $query->orderBy('code', 'asc')->get();

        $linesCollection = collect();
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        $isFirstPeriod = $selectedPeriod && ($selectedPeriod->id === $periods->first()?->id);

        foreach ($accounts as $acc) {
            $mutQuery = JournalLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($startDate, $isFirstPeriod) {
                    $q->where('status', 'posted')
                        ->where(function ($subQ) use ($startDate, $isFirstPeriod) {
                            if ($isFirstPeriod) {
                                $subQ->where('entry_number', 'like', 'SA-%')
                                    ->orWhere('entry_type', 'opening_balance')
                                    ->orWhere('source_type', 'opening_balance')
                                    ->orWhere('entry_date', '<=', $startDate);
                            } else {
                                $subQ->where('entry_type', 'opening_balance')
                                    ->orWhere('source_type', 'opening_balance')
                                    ->orWhere('entry_date', '<', $startDate);
                            }
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

            if ($d == 0 && $c == 0 && (float) $acc->opening_balance == 0) {
                continue;
            }

            $debitVal = 0.0;
            $creditVal = 0.0;

            if ($acc->normal_balance === 'debit') {
                $debitVal = ($d - $c);
                $totalDebit += $debitVal;
            } else {
                $creditVal = ($c - $d);
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
            'batchDifference' => abs($totalDebit - $totalCredit),
            'units' => $allowedUnits,
            'periods' => $periods,
            'selectedPeriod' => $selectedPeriod,
        ]);
    }
}
