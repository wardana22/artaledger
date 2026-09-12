<?php

namespace App\Livewire\Accounting\Reports;

use App\Domain\Accounting\Services\CashFlowService;
use App\Livewire\Concerns\SyncsGlobalPeriod;
use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\CashFlowRow;
use App\Models\Company;
use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan Arus Kas (Cash Flow Statement)')]
class CashFlow extends Component
{
    use SyncsGlobalPeriod;

    public string $startDate = '';

    public string $endDate = '';

    public string $unitFilter = 'all';

    public array $expandedRows = [];

    public array $rowBreakdowns = [];

    // Modal Builder State
    public bool $showManageModal = false;

    public bool $showRowFormModal = false;

    public ?int $editingRowId = null;

    public string $row_section = 'operating';

    public string $row_label = '';

    public string $row_source_type = 'account_group';

    public ?int $row_account_id = null;

    public ?int $row_account_group_id = null;

    public string $row_calculation_type = 'net_mutation';

    public string $row_formula_expression = '';

    public string $row_operator_sign = '+';

    public int $row_order_index = 1;

    public function mount(): void
    {
        if (auth()->check() && ! auth()->user()->can('reports.cash_flow') && ! auth()->user()->can('reports.view')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        $this->initializeGlobalPeriod();
    }

    public function toggleRow(int $rowId, ?CashFlowService $service = null): void
    {
        $service = $service ?? app(CashFlowService::class);

        if (in_array($rowId, $this->expandedRows)) {
            $this->expandedRows = array_values(array_diff($this->expandedRows, [$rowId]));
            unset($this->rowBreakdowns[$rowId]);
        } else {
            $this->expandedRows[] = $rowId;
            $this->rowBreakdowns[$rowId] = $service->getRowBreakdown($rowId, $this->startDate, $this->endDate, $this->unitFilter);
        }
    }

    public function openManageModal(): void
    {
        $this->showManageModal = true;
    }

    public function closeManageModal(): void
    {
        $this->showManageModal = false;
        $this->showRowFormModal = false;
        $this->resetRowForm();
    }

    public function createRow(string $section = 'operating'): void
    {
        $this->resetRowForm();
        $this->row_section = $section;
        $maxOrder = CashFlowRow::where('section', $section)->max('order_index') ?? 0;
        $this->row_order_index = $maxOrder + 1;
        $this->showRowFormModal = true;
    }

    public function editRow(int $id): void
    {
        $row = CashFlowRow::find($id);
        if (! $row) {
            return;
        }

        $this->editingRowId = $row->id;
        $this->row_section = $row->section;
        $this->row_label = $row->label;
        $this->row_source_type = $row->source_type;
        $this->row_account_id = $row->account_id;
        $this->row_account_group_id = $row->account_group_id;
        $this->row_calculation_type = $row->calculation_type;
        $this->row_formula_expression = $row->formula_expression ?? '';
        $this->row_operator_sign = $row->operator_sign;
        $this->row_order_index = $row->order_index;
        $this->showRowFormModal = true;
    }

    public function saveRow(): void
    {
        $this->validate([
            'row_label' => 'required|string|max:255',
            'row_section' => 'required|in:operating,investing,financing',
            'row_source_type' => 'required|in:account,account_group,formula',
            'row_operator_sign' => 'required|in:+,-',
            'row_order_index' => 'required|integer|min:1',
        ]);

        $company = Company::first();
        $companyId = $company ? $company->id : 1;

        $data = [
            'company_id' => $companyId,
            'section' => $this->row_section,
            'label' => $this->row_label,
            'source_type' => $this->row_source_type,
            'account_id' => $this->row_source_type === 'account' ? $this->row_account_id : null,
            'account_group_id' => $this->row_source_type === 'account_group' ? $this->row_account_group_id : null,
            'calculation_type' => $this->row_calculation_type,
            'formula_expression' => $this->row_source_type === 'formula' ? $this->row_formula_expression : null,
            'operator_sign' => $this->row_operator_sign,
            'order_index' => $this->row_order_index,
            'is_active' => true,
        ];

        if ($this->editingRowId) {
            CashFlowRow::where('id', $this->editingRowId)->update($data);
        } else {
            CashFlowRow::create($data);
        }

        $this->showRowFormModal = false;
        $this->resetRowForm();
        session()->flash('success', 'Baris arus kas berhasil disimpan.');
    }

    public function deleteRow(int $id): void
    {
        CashFlowRow::destroy($id);
        session()->flash('success', 'Baris arus kas berhasil dihapus.');
    }

    public function moveRowUp(int $id): void
    {
        $row = CashFlowRow::find($id);
        if (! $row) {
            return;
        }

        $prev = CashFlowRow::where('section', $row->section)
            ->where('order_index', '<', $row->order_index)
            ->orderBy('order_index', 'desc')
            ->first();

        if ($prev) {
            $temp = $prev->order_index;
            $prev->update(['order_index' => $row->order_index]);
            $row->update(['order_index' => $temp]);
        }
    }

    public function moveRowDown(int $id): void
    {
        $row = CashFlowRow::find($id);
        if (! $row) {
            return;
        }

        $next = CashFlowRow::where('section', $row->section)
            ->where('order_index', '>', $row->order_index)
            ->orderBy('order_index', 'asc')
            ->first();

        if ($next) {
            $temp = $next->order_index;
            $next->update(['order_index' => $row->order_index]);
            $row->update(['order_index' => $temp]);
        }
    }

    private function resetRowForm(): void
    {
        $this->editingRowId = null;
        $this->row_label = '';
        $this->row_source_type = 'account_group';
        $this->row_account_id = null;
        $this->row_account_group_id = null;
        $this->row_calculation_type = 'net_mutation';
        $this->row_formula_expression = '';
        $this->row_operator_sign = '+';
        $this->row_order_index = 1;
    }

    public function render()
    {
        $service = app(CashFlowService::class);
        $statement = $service->calculateStatement($this->startDate, $this->endDate, $this->unitFilter);
        $units = Unit::all();
        $accountGroups = AccountGroup::orderBy('name')->get();
        $accounts = Account::where('is_group', false)->orderBy('code')->get();
        $allRows = CashFlowRow::orderBy('section')->orderBy('order_index')->get();

        return view('livewire.accounting.reports.cash-flow', array_merge($statement, [
            'units' => $units,
            'accountGroups' => $accountGroups,
            'accounts' => $accounts,
            'allRows' => $allRows,
        ]));
    }
}
