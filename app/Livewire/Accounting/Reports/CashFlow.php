<?php

namespace App\Livewire\Accounting\Reports;

use App\Models\Account;
use App\Models\JournalLine;
use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan Arus Kas (Cash Flow Statement) - ArtaLedger')]
class CashFlow extends Component
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
        $cashAccounts = Account::where('type', 'KAS')->orWhere('type', 'BANK')->orWhere('code', 'like', '11.01%')->orWhere('code', 'like', '11.02%')->pluck('id')->toArray();

        // 1. Operating Cash Flow
        $opQuery = JournalLine::whereIn('account_id', $cashAccounts)
            ->whereHas('journalEntry', function ($q) {
                $q->where('status', 'posted')->whereBetween('entry_date', [$this->startDate, $this->endDate]);
            });

        if ($this->unitFilter !== 'all') {
            $opQuery->where('unit_id', $this->unitFilter);
        }

        $operatingLines = $opQuery->get();

        $operatingIn = (float) $operatingLines->sum('debit');
        $operatingOut = (float) $operatingLines->sum('credit');
        $netOperatingCash = $operatingIn - $operatingOut;

        $openingCash = (float) Account::whereIn('id', $cashAccounts)->sum('opening_balance');
        $endingCash = $openingCash + $netOperatingCash;
        $units = Unit::all();

        return view('livewire.accounting.reports.cash-flow', [
            'openingCash' => $openingCash,
            'operatingIn' => $operatingIn,
            'operatingOut' => $operatingOut,
            'netOperatingCash' => $netOperatingCash,
            'endingCash' => $endingCash,
            'units' => $units,
        ]);
    }
}
