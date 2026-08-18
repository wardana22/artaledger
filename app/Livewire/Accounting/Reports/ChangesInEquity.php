<?php

namespace App\Livewire\Accounting\Reports;

use App\Models\Account;
use App\Models\JournalLine;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan Perubahan Ekuitas - ArtaLedger')]
class ChangesInEquity extends Component
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
        $equityAccounts = Account::where('code', 'like', '3%')->posting()->active()->get();

        $initialEquity = (float) $equityAccounts->sum('opening_balance');

        $revenue = JournalLine::whereHas('account', fn ($q) => $q->where('report_type', 'laba_rugi')->where('normal_balance', 'credit'))
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->whereBetween('entry_date', [$this->startDate, $this->endDate]))
            ->selectRaw('SUM(credit - debit) as total')
            ->value('total') ?? 0;

        $expenses = JournalLine::whereHas('account', fn ($q) => $q->where('report_type', 'laba_rugi')->where('normal_balance', 'debit'))
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->whereBetween('entry_date', [$this->startDate, $this->endDate]))
            ->selectRaw('SUM(debit - credit) as total')
            ->value('total') ?? 0;

        $netProfit = (float) $revenue - (float) $expenses;
        $endingEquity = $initialEquity + $netProfit;

        return view('livewire.accounting.reports.changes-in-equity', [
            'initialEquity' => $initialEquity,
            'netProfit' => $netProfit,
            'endingEquity' => $endingEquity,
        ]);
    }
}
