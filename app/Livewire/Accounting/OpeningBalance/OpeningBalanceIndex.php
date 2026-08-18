<?php

namespace App\Livewire\Accounting\OpeningBalance;

use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Unit;
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
        $firstPeriod = AccountingPeriod::orderBy('start_date', 'asc')->first();
        $this->periodId = $firstPeriod?->id;
    }

    public function render()
    {
        $companyId = Company::first()?->id ?? 1;
        $periods = AccountingPeriod::orderBy('start_date', 'asc')->get();

        $selectedPeriod = $periods->firstWhere('id', $this->periodId) ?? $periods->first();

        // Fetch Opening Balance Entries up to or within the selected period
        $openingQuery = JournalEntry::where('company_id', $companyId)
            ->where('status', 'posted');

        if ($selectedPeriod) {
            $openingQuery->whereDate('entry_date', '<=', $selectedPeriod->start_date);
        }

        $openingEntry = JournalEntry::where('status', 'posted')
            ->where(function ($q) {
                $q->where('entry_number', 'SA-2025-001')
                    ->orWhere('source_type', 'opening_balance')
                    ->orWhere('entry_type', 'opening');
            })
            ->first();

        $lines = collect();
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        if ($openingEntry) {
            $totalDebit = (float) $openingEntry->lines()->sum('debit');
            $totalCredit = (float) $openingEntry->lines()->sum('credit');

            $query = JournalLine::with('account', 'unit')
                ->where('journal_entry_id', $openingEntry->id);

            if ($this->unitFilter !== 'all') {
                $query->where('unit_id', $this->unitFilter);
            }

            if (! empty($this->search)) {
                $term = '%'.$this->search.'%';
                $query->where(function ($q) use ($term) {
                    $q->where('description', 'like', $term)
                        ->orWhereHas('account', function ($accQ) use ($term) {
                            $accQ->where('code', 'like', $term)
                                ->orWhere('name', 'like', $term);
                        });
                });
            }

            $lines = $query->join('accounts', 'journal_lines.account_id', '=', 'accounts.id')
                ->orderBy('accounts.code', 'asc')
                ->select('journal_lines.*')
                ->paginate($this->perPage);
        }

        $units = Unit::all();

        return view('livewire.accounting.opening-balance.opening-balance-index', [
            'openingEntry' => $openingEntry,
            'lines' => $lines,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'batchDifference' => abs($totalDebit - $totalCredit),
            'units' => $units,
            'periods' => $periods,
            'selectedPeriod' => $selectedPeriod,
        ]);
    }
}
