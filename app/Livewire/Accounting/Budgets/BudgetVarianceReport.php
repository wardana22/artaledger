<?php

namespace App\Livewire\Accounting\Budgets;

use App\Domain\Budget\Services\BudgetCalculationService;
use App\Models\Budget;
use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan Varian Anggaran (Budget vs Actual)')]
class BudgetVarianceReport extends Component
{
    public ?int $selectedBudgetId = null;

    public string $filterYear = '';

    public string $filterStatus = 'all'; // 'all', 'terkendali', 'mendekati', 'melampaui'

    public ?int $filterUnitId = null;

    public ?int $filterMonth = null; // null = Full Year, 1-12 = Monthly

    public string $search = '';

    // Drill-down Modal State
    public bool $showDrillDownModal = false;

    public ?int $drillDownAccountId = null;

    public string $drillDownAccountCode = '';

    public string $drillDownAccountName = '';

    public array $drillDownData = [];

    protected $queryString = [
        'selectedBudgetId' => ['except' => null],
        'filterYear' => ['except' => ''],
        'filterStatus' => ['except' => 'all'],
        'filterUnitId' => ['except' => null],
        'filterMonth' => ['except' => null],
        'search' => ['except' => ''],
    ];

    public function mount(): void
    {
        $years = Budget::distinct()->orderBy('fiscal_year', 'desc')->pluck('fiscal_year');

        if (! $this->filterYear && $years->isNotEmpty()) {
            // Default to current year if exists, otherwise latest
            $currentYr = (int) date('Y');
            $this->filterYear = $years->contains($currentYr) ? (string) $currentYr : (string) $years->first();
        }

        $this->syncSelectedBudget();
    }

    public function updatedFilterYear(): void
    {
        $this->syncSelectedBudget();
    }

    protected function syncSelectedBudget(): void
    {
        if ($this->filterYear) {
            $budget = Budget::where('fiscal_year', $this->filterYear)
                ->where('status', 'active')
                ->first();

            if (! $budget) {
                $budget = Budget::where('fiscal_year', $this->filterYear)->first();
            }

            $this->selectedBudgetId = $budget?->id;
        }
    }

    public function openDrillDown(int $accountId, string $accountCode, string $accountName, BudgetCalculationService $calculationService): void
    {
        $this->drillDownAccountId = $accountId;
        $this->drillDownAccountCode = $accountCode;
        $this->drillDownAccountName = $accountName;

        $year = (int) ($this->filterYear ?: date('Y'));
        $month = $this->filterMonth ? (int) $this->filterMonth : null;
        $unitId = $this->filterUnitId ? (int) $this->filterUnitId : null;

        $this->drillDownData = $calculationService->getAccountJournalDetails($accountId, $year, $month, $unitId);
        $this->showDrillDownModal = true;
    }

    public function closeDrillDown(): void
    {
        $this->showDrillDownModal = false;
        $this->drillDownAccountId = null;
        $this->drillDownAccountCode = '';
        $this->drillDownAccountName = '';
        $this->drillDownData = [];
    }

    public function render(BudgetCalculationService $calculationService)
    {
        $years = Budget::distinct()->orderBy('fiscal_year', 'desc')->pluck('fiscal_year');
        $units = Unit::all();

        $selectedBudget = $this->selectedBudgetId ? Budget::find($this->selectedBudgetId) : null;
        $reportData = null;

        if ($selectedBudget) {
            $reportData = $calculationService->calculateBudgetComparison(
                $selectedBudget,
                $this->filterMonth ? (int) $this->filterMonth : null,
                $this->filterUnitId ? (int) $this->filterUnitId : null,
                $this->filterStatus ?: 'all',
                $this->search
            );
        }

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return view('livewire.accounting.budgets.budget-variance-report', [
            'years' => $years,
            'units' => $units,
            'selectedBudget' => $selectedBudget,
            'reportData' => $reportData,
            'monthNames' => $monthNames,
        ]);
    }
}
