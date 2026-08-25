<?php

namespace App\Livewire\Accounting\Reports;

use App\Models\Account;
use App\Models\JournalLine;
use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Buku Besar Pembantu (Subsidiary Ledger) - ArtaLedger')]
class SubsidiaryLedger extends Component
{
    public ?int $selectedAccountId = null;

    public string $unitFilter = 'all';

    public string $startDate = '';

    public string $endDate = '';

    public function mount(): void
    {
        $this->startDate = date('Y-01-01');
        $this->endDate = date('Y-12-31');

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
            $openingBalance = (float) $selectedAccount->opening_balance;

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
