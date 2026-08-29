<?php

namespace App\Livewire\Accounting\Reports;

use App\Models\Account;
use App\Models\JournalLine;
use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan Perubahan Ekuitas - ArtaLedger')]
class ChangesInEquity extends Component
{
    public string $startDate = '';

    public string $endDate = '';

    public string $unitFilter = 'all';

    public function mount(): void
    {
        if (auth()->check() && ! auth()->user()->can('reports.changes_in_equity') && ! auth()->user()->can('reports.view')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        $this->startDate = date('Y-01-01');
        $this->endDate = date('Y-12-31');

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

        $equityAccounts = Account::where('code', 'like', '3%')->posting()->active()->get();

        $initialEquity = (float) $equityAccounts->sum('opening_balance');

        $revQuery = JournalLine::whereHas('account', fn ($q) => $q->where('report_type', 'laba_rugi')->where('normal_balance', 'credit'))
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->whereBetween('entry_date', [$this->startDate, $this->endDate]))
            ->when(! empty($allowedUnitIds), fn ($q) => $q->whereIn('unit_id', $allowedUnitIds));

        if ($this->unitFilter !== 'all') {
            $revQuery->where('unit_id', $this->unitFilter);
        }

        $revenue = $revQuery->selectRaw('SUM(credit - debit) as total')->value('total') ?? 0;

        $expQuery = JournalLine::whereHas('account', fn ($q) => $q->where('report_type', 'laba_rugi')->where('normal_balance', 'debit'))
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->whereBetween('entry_date', [$this->startDate, $this->endDate]))
            ->when(! empty($allowedUnitIds), fn ($q) => $q->whereIn('unit_id', $allowedUnitIds));

        if ($this->unitFilter !== 'all') {
            $expQuery->where('unit_id', $this->unitFilter);
        }

        $expenses = $expQuery->selectRaw('SUM(debit - credit) as total')->value('total') ?? 0;

        $netProfit = (float) $revenue - (float) $expenses;
        $endingEquity = $initialEquity + $netProfit;

        return view('livewire.accounting.reports.changes-in-equity', [
            'initialEquity' => $initialEquity,
            'netProfit' => $netProfit,
            'endingEquity' => $endingEquity,
            'units' => $allowedUnits,
        ]);
    }
}
