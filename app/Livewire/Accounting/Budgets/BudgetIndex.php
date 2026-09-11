<?php

namespace App\Livewire\Accounting\Budgets;

use App\Models\Budget;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Anggaran & Kontrol Biaya')]
class BudgetIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterYear = '';

    public string $filterStatus = '';

    public bool $confirmingDeletion = false;

    public ?int $budgetToDeleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterYear' => ['except' => ''],
        'filterStatus' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function activateBudget(int $id): void
    {
        $budget = Budget::findOrFail($id);

        // Deactivate other budgets for the same fiscal year
        Budget::where('fiscal_year', $budget->fiscal_year)
            ->where('id', '!=', $budget->id)
            ->where('status', 'active')
            ->update(['status' => 'closed']);

        $budget->update(['status' => 'active']);
        session()->flash('success', "Anggaran '{$budget->name}' berhasil diaktifkan untuk tahun {$budget->fiscal_year}.");
    }

    public function closeBudget(int $id): void
    {
        $budget = Budget::findOrFail($id);
        $budget->update(['status' => 'closed']);
        session()->flash('success', "Anggaran '{$budget->name}' telah ditutup.");
    }

    public function confirmDelete(int $id): void
    {
        $this->budgetToDeleteId = $id;
        $this->confirmingDeletion = true;
    }

    public function deleteBudget(): void
    {
        if ($this->budgetToDeleteId) {
            $budget = Budget::find($this->budgetToDeleteId);
            if ($budget) {
                $name = $budget->name;
                $budget->delete();
                session()->flash('success', "Anggaran '{$name}' berhasil dihapus.");
            }
        }

        $this->confirmingDeletion = false;
        $this->budgetToDeleteId = null;
    }

    public function duplicateBudget(int $id): void
    {
        $source = Budget::with('lines')->findOrFail($id);
        $newYear = $source->fiscal_year + 1;

        $newBudget = Budget::create([
            'fiscal_year' => $newYear,
            'name' => "{$source->name} (Salinan {$newYear})",
            'description' => "Duplikasi dari anggaran {$source->fiscal_year}",
            'status' => 'draft',
            'enforcement_mode' => $source->enforcement_mode,
            'warning_threshold_pct' => $source->warning_threshold_pct,
            'created_by' => auth()->id(),
        ]);

        foreach ($source->lines as $line) {
            $newBudget->lines()->create([
                'account_id' => $line->account_id,
                'unit_id' => $line->unit_id,
                'annual_amount' => $line->annual_amount,
                'm01_amount' => $line->m01_amount,
                'm02_amount' => $line->m02_amount,
                'm03_amount' => $line->m03_amount,
                'm04_amount' => $line->m04_amount,
                'm05_amount' => $line->m05_amount,
                'm06_amount' => $line->m06_amount,
                'm07_amount' => $line->m07_amount,
                'm08_amount' => $line->m08_amount,
                'm09_amount' => $line->m09_amount,
                'm10_amount' => $line->m10_amount,
                'm11_amount' => $line->m11_amount,
                'm12_amount' => $line->m12_amount,
                'notes' => $line->notes,
            ]);
        }

        session()->flash('success', "Anggaran berhasil disalin untuk tahun {$newYear} dengan status Draft.");
    }

    public function render()
    {
        $query = Budget::query()
            ->with(['creator'])
            ->withCount('lines')
            ->withSum('lines as total_annual_amount', 'annual_amount');

        if (! empty($this->search)) {
            $query->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('description', 'like', '%'.$this->search.'%');
        }

        if (! empty($this->filterYear)) {
            $query->where('fiscal_year', $this->filterYear);
        }

        if (! empty($this->filterStatus)) {
            $query->where('status', $this->filterStatus);
        }

        $budgets = $query->orderBy('fiscal_year', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        $years = Budget::select('fiscal_year')->distinct()->orderBy('fiscal_year', 'desc')->pluck('fiscal_year');

        return view('livewire.accounting.budgets.budget-index', [
            'budgets' => $budgets,
            'years' => $years,
        ]);
    }
}
