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
#[Title('Buku Besar Pembantu (Subsidiary Ledger)')]
class SubsidiaryLedger extends Component
{
    use SyncsGlobalPeriod;

    public ?int $selectedAccountId = null;

    public string $unitFilter = 'all';

    public string $startDate = '';

    public string $endDate = '';

    public function mount(): void
    {
        if (auth()->check() && ! auth()->user()->can('reports.subsidiary_ledger') && ! auth()->user()->can('reports.view')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        $this->initializeGlobalPeriod();

        $firstPostingAccount = Account::posting()->active()->orderBy('code', 'asc')->first();
        $this->selectedAccountId = $firstPostingAccount?->id;
    }

    public function render()
    {
        $accounts = Account::posting()->active()->orderBy('code', 'asc')->get(['id', 'code', 'name']);
        $selectedAccount = Account::find($this->selectedAccountId);

        $lines = [];
        $openingBalance = 0.0;

        if ($selectedAccount) {
            $startYear = Carbon::parse($this->startDate)->year;
            $hasOpeningBalance = JournalEntry::where('status', 'posted')
                ->where(function ($q) use ($startYear) {
                    $q->where('source_type', 'opening_balance')
                        ->orWhere('entry_type', 'opening_balance')
                        ->orWhere('entry_number', 'like', "SA-{$startYear}%");
                })
                ->whereYear('entry_date', $startYear)
                ->exists();

            $opQuery = JournalLine::whereHas('journalEntry', function ($q) use ($startYear, $hasOpeningBalance) {
                $q->where('status', 'posted');

                if ($hasOpeningBalance) {
                    $q->where(function ($sub) use ($startYear) {
                        $sub->where(function ($obQ) use ($startYear) {
                            $obQ->where('entry_number', 'like', "SA-{$startYear}%")
                                ->orWhere('source_type', 'opening_balance')
                                ->orWhere('entry_type', 'opening_balance');
                        })->orWhere(function ($priorQ) use ($startYear) {
                            $priorQ->where('entry_number', 'not like', 'SA-%')
                                ->where('entry_type', '!=', 'opening_balance')
                                ->where('source_type', '!=', 'opening_balance')
                                ->where('entry_date', '>=', "{$startYear}-01-01")
                                ->where('entry_date', '<', $this->startDate);
                        });
                    });
                } else {
                    $q->where(function ($sub) {
                        $sub->where('entry_type', 'opening_balance')
                            ->orWhere('source_type', 'opening_balance')
                            ->orWhere('entry_number', 'like', 'SA%')
                            ->orWhere('entry_date', '<', $this->startDate);
                    });
                }
            })
                ->where('account_id', $selectedAccount->id);

            if ($this->unitFilter !== 'all') {
                $opQuery->where('unit_id', $this->unitFilter);
            }

            $opTotals = $opQuery->selectRaw('SUM(debit) as tot_d, SUM(credit) as tot_c')->first();
            $opD = (float) ($opTotals->tot_d ?? 0);
            $opC = (float) ($opTotals->tot_c ?? 0);
            $openingBalance = $selectedAccount->normal_balance === 'debit' ? ($opD - $opC) : ($opC - $opD);

            $linesQuery = JournalLine::with(['journalEntry', 'unit'])
                ->where('account_id', $selectedAccount->id)
                ->whereHas('journalEntry', function ($q) {
                    $q->where('status', 'posted')
                        ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
                });

            if ($this->unitFilter !== 'all') {
                $linesQuery->where('unit_id', $this->unitFilter);
            }

            $lines = $linesQuery->get()->sortBy('journalEntry.entry_date');
        }

        $units = Unit::all();

        return view('livewire.accounting.reports.subsidiary-ledger', [
            'accounts' => $accounts,
            'selectedAccount' => $selectedAccount,
            'lines' => $lines,
            'openingBalance' => $openingBalance,
            'units' => $units,
        ]);
    }
}
