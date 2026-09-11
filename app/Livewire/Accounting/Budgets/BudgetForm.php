<?php

namespace App\Livewire\Accounting\Budgets;

use App\Models\Account;
use App\Models\Budget;
use App\Models\BudgetLine;
use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Form Anggaran')]
class BudgetForm extends Component
{
    public ?int $budgetId = null;

    public int $fiscal_year;

    public string $name = '';

    public ?string $description = null;

    public string $status = 'draft';

    public string $enforcement_mode = 'warning_only';

    public float $warning_threshold_pct = 80.00;

    // Lines items: array of rows
    public array $lines = [];

    protected function rules(): array
    {
        return [
            'fiscal_year' => 'required|integer|min:2000|max:2099',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,active,closed',
            'enforcement_mode' => 'required|in:warning_only,strict_block',
            'warning_threshold_pct' => 'required|numeric|min:1|max:100',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.unit_id' => 'nullable|exists:units,id',
            'lines.*.annual_amount' => 'required|numeric|min:0',
        ];
    }

    public function mount(?int $id = null): void
    {
        $this->fiscal_year = (int) date('Y');

        if ($id) {
            $budget = Budget::with('lines')->findOrFail($id);
            $this->budgetId = $budget->id;
            $this->fiscal_year = $budget->fiscal_year;
            $this->name = $budget->name;
            $this->description = $budget->description;
            $this->status = $budget->status;
            $this->enforcement_mode = $budget->enforcement_mode;
            $this->warning_threshold_pct = (float) $budget->warning_threshold_pct;

            foreach ($budget->lines as $line) {
                $this->lines[] = [
                    'id' => $line->id,
                    'account_id' => $line->account_id,
                    'unit_id' => $line->unit_id,
                    'annual_amount' => (float) $line->annual_amount,
                    'm01_amount' => (float) $line->m01_amount,
                    'm02_amount' => (float) $line->m02_amount,
                    'm03_amount' => (float) $line->m03_amount,
                    'm04_amount' => (float) $line->m04_amount,
                    'm05_amount' => (float) $line->m05_amount,
                    'm06_amount' => (float) $line->m06_amount,
                    'm07_amount' => (float) $line->m07_amount,
                    'm08_amount' => (float) $line->m08_amount,
                    'm09_amount' => (float) $line->m09_amount,
                    'm10_amount' => (float) $line->m10_amount,
                    'm11_amount' => (float) $line->m11_amount,
                    'm12_amount' => (float) $line->m12_amount,
                    'notes' => $line->notes,
                ];
            }
        } else {
            // Default 1 line
            $this->addLine();
        }
    }

    public function addLine(): void
    {
        $this->lines[] = [
            'id' => null,
            'account_id' => null,
            'unit_id' => null,
            'annual_amount' => 0,
            'm01_amount' => 0,
            'm02_amount' => 0,
            'm03_amount' => 0,
            'm04_amount' => 0,
            'm05_amount' => 0,
            'm06_amount' => 0,
            'm07_amount' => 0,
            'm08_amount' => 0,
            'm09_amount' => 0,
            'm10_amount' => 0,
            'm11_amount' => 0,
            'm12_amount' => 0,
            'notes' => '',
        ];
    }

    public function removeLine(int $index): void
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    /**
     * Auto-distribute the annual amount equally across months 1-12 for a specific row.
     */
    public function autoDistributeRow(int $index): void
    {
        if (! isset($this->lines[$index])) {
            return;
        }

        $annual = (float) ($this->lines[$index]['annual_amount'] ?? 0);
        $monthly = round($annual / 12, 2);
        $remainder = round($annual - ($monthly * 11), 2);

        for ($m = 1; $m <= 11; $m++) {
            $pad = str_pad((string) $m, 2, '0', STR_PAD_LEFT);
            $this->lines[$index]["m{$pad}_amount"] = $monthly;
        }
        $this->lines[$index]['m12_amount'] = $remainder;
    }

    /**
     * Auto-calculate annual total from sum of 12 months.
     */
    public function sumMonthlyToAnnual(int $index): void
    {
        if (! isset($this->lines[$index])) {
            return;
        }

        $total = 0;
        for ($m = 1; $m <= 12; $m++) {
            $pad = str_pad((string) $m, 2, '0', STR_PAD_LEFT);
            $total += (float) ($this->lines[$index]["m{$pad}_amount"] ?? 0);
        }

        $this->lines[$index]['annual_amount'] = $total;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'fiscal_year' => $this->fiscal_year,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'enforcement_mode' => $this->enforcement_mode,
            'warning_threshold_pct' => $this->warning_threshold_pct,
        ];

        if ($this->budgetId) {
            $budget = Budget::findOrFail($this->budgetId);
            $data['updated_by'] = auth()->id();
            $budget->update($data);
        } else {
            $data['created_by'] = auth()->id();
            $budget = Budget::create($data);
            $this->budgetId = $budget->id;
        }

        // Sync lines
        $existingLineIds = [];
        foreach ($this->lines as $lineData) {
            if (empty($lineData['account_id'])) {
                continue;
            }

            $lineAttributes = [
                'account_id' => $lineData['account_id'],
                'unit_id' => ! empty($lineData['unit_id']) ? $lineData['unit_id'] : null,
                'annual_amount' => (float) $lineData['annual_amount'],
                'm01_amount' => (float) $lineData['m01_amount'],
                'm02_amount' => (float) $lineData['m02_amount'],
                'm03_amount' => (float) $lineData['m03_amount'],
                'm04_amount' => (float) $lineData['m04_amount'],
                'm05_amount' => (float) $lineData['m05_amount'],
                'm06_amount' => (float) $lineData['m06_amount'],
                'm07_amount' => (float) $lineData['m07_amount'],
                'm08_amount' => (float) $lineData['m08_amount'],
                'm09_amount' => (float) $lineData['m09_amount'],
                'm10_amount' => (float) $lineData['m10_amount'],
                'm11_amount' => (float) $lineData['m11_amount'],
                'm12_amount' => (float) $lineData['m12_amount'],
                'notes' => $lineData['notes'] ?? null,
            ];

            if (! empty($lineData['id'])) {
                $line = BudgetLine::where('budget_id', $budget->id)->find($lineData['id']);
                if ($line) {
                    $line->update($lineAttributes);
                    $existingLineIds[] = $line->id;

                    continue;
                }
            }

            $created = $budget->lines()->create($lineAttributes);
            $existingLineIds[] = $created->id;
        }

        // Remove deleted lines
        $budget->lines()->whereNotIn('id', $existingLineIds)->delete();

        session()->flash('success', 'Anggaran berhasil disimpan.');

        return redirect()->route('accounting.budgets.index');
    }

    public function render()
    {
        $accounts = Account::posting()
            ->where(function ($q) {
                $q->where('type', 'BEBAN')
                    ->orWhere('report_type', 'laba_rugi')
                    ->orWhere('code', 'like', '5%')
                    ->orWhere('code', 'like', '6%');
            })
            ->orderBy('code', 'asc')
            ->get();

        $units = Unit::all();

        return view('livewire.accounting.budgets.budget-form', [
            'accounts' => $accounts,
            'units' => $units,
        ]);
    }
}
