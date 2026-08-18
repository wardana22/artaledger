<?php

namespace App\Livewire\Accounting\Reports;

use App\Models\Account;
use App\Models\JournalLine;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Buku Besar (General Ledger) - Akun Header - ArtaLedger')]
class GeneralLedger extends Component
{
    public ?int $selectedAccountId = null;

    public string $startDate = '';

    public string $endDate = '';

    public function mount(): void
    {
        $this->startDate = date('Y-01-01');
        $this->endDate = date('Y-12-31');

        $firstHeaderAccount = Account::group()->active()->orderBy('code', 'asc')->first();
        $this->selectedAccountId = $firstHeaderAccount?->id;
    }

    public function render()
    {
        // Only Header (Group) Accounts
        $accounts = Account::group()->active()->orderBy('code', 'asc')->get(['id', 'code', 'name']);
        $selectedAccount = Account::find($this->selectedAccountId);

        $lines = [];
        $openingBalance = 0.0;
        $childAccountsCount = 0;

        if ($selectedAccount) {
            $childAccountIds = $selectedAccount->getAllDescendantIds();

            // Include current account if it has transactions or include descendants
            $targetAccountIds = array_merge([$selectedAccount->id], $childAccountIds);
            $childAccountsCount = count($childAccountIds);

            // Sum opening balance of header + children
            $openingBalance = (float) Account::whereIn('id', $targetAccountIds)->sum('opening_balance');

            $lines = JournalLine::with(['journalEntry', 'account'])
                ->whereIn('account_id', $targetAccountIds)
                ->whereHas('journalEntry', function ($q) {
                    $q->where('status', 'posted')
                        ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
                })
                ->get()
                ->sortBy('journalEntry.entry_date');
        }

        return view('livewire.accounting.reports.general-ledger', [
            'accounts' => $accounts,
            'selectedAccount' => $selectedAccount,
            'lines' => $lines,
            'openingBalance' => $openingBalance,
            'childAccountsCount' => $childAccountsCount,
        ]);
    }
}
