<?php

namespace App\Livewire\Accounting\Periods;

use App\Models\AccountingPeriod;
use App\Models\Company;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Periode Akuntansi - ArtaLedger')]
class PeriodIndex extends Component
{
    public int $selectedYear;

    public bool $showFormModal = false;

    public int $year;

    public int $month = 1;

    public string $status = 'open';

    public function mount(): void
    {
        $this->selectedYear = (int) date('Y');
        $this->year = (int) date('Y');
        $this->month = (int) date('n');
    }

    public function generateYearPeriods(): void
    {
        $company = Company::first();
        if (! $company) {
            return;
        }

        for ($m = 1; $m <= 12; $m++) {
            $startDate = Carbon::create($this->selectedYear, $m, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            AccountingPeriod::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'year' => $this->selectedYear,
                    'month' => $m,
                ],
                [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'status' => 'open',
                ]
            );
        }

        session()->flash('message', "Seluruh periode 12 bulan untuk tahun {$this->selectedYear} berhasil dibuat.");
    }

    public function toggleStatus(int $periodId): void
    {
        $period = AccountingPeriod::findOrFail($periodId);

        $nextStatus = match ($period->status) {
            'open' => 'closed',
            'closed' => 'locked',
            'locked' => 'open',
        };

        $period->update(['status' => $nextStatus]);
        session()->flash('message', "Status periode {$period->start_date->format('F Y')} diubah menjadi '{$nextStatus}'.");
    }

    public function render()
    {
        $company = Company::first();

        $periods = AccountingPeriod::where('company_id', $company?->id ?? 1)
            ->where('year', $this->selectedYear)
            ->orderBy('month')
            ->get();

        return view('livewire.accounting.periods.index', [
            'periods' => $periods,
        ]);
    }
}
