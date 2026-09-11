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

    public ?int $filterMonth = null; // null = Full Year

    public ?int $filterUnitId = null;

    protected $queryString = [
        'selectedBudgetId' => ['except' => null],
        'filterMonth' => ['except' => null],
        'filterUnitId' => ['except' => null],
    ];

    public function mount(): void
    {
        if (! $this->selectedBudgetId) {
            $activeBudget = Budget::query()->active()->latest('fiscal_year')->first();
            if (! $activeBudget) {
                $activeBudget = Budget::query()->latest('fiscal_year')->first();
            }
            if ($activeBudget) {
                $this->selectedBudgetId = $activeBudget->id;
            }
        }
    }

    public function render(BudgetCalculationService $calculationService)
    {
        $budgets = Budget::orderBy('fiscal_year', 'desc')->get();
        $units = Unit::all();

        $selectedBudget = $this->selectedBudgetId ? Budget::find($this->selectedBudgetId) : null;
        $reportData = null;

        if ($selectedBudget) {
            $reportData = $calculationService->calculateBudgetComparison(
                $selectedBudget,
                $this->filterMonth ? (int) $this->filterMonth : null,
                $this->filterUnitId ? (int) $this->filterUnitId : null
            );
        }

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return view('livewire.accounting.budgets.budget-variance-report', [
            'budgets' => $budgets,
            'units' => $units,
            'selectedBudget' => $selectedBudget,
            'reportData' => $reportData,
            'monthNames' => $monthNames,
        ]);
    }
}
