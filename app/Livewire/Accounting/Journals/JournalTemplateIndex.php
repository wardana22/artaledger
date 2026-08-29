<?php

namespace App\Livewire\Accounting\Journals;

use App\Models\Account;
use App\Models\Company;
use App\Models\JournalTemplate;
use App\Models\JournalTemplateLine;
use App\Models\JournalType;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Template Jurnal Transaksi - ArtaLedger')]
class JournalTemplateIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingTemplateId = null;

    // Form fields
    public string $template_code = '';

    public string $name = '';

    public string $description = '';

    public ?int $journal_type_id = null;

    public bool $is_active = true;

    public array $lines = [];

    public function mount(): void
    {
        $defaultType = JournalType::where('code', 'JK')->first() ?? JournalType::first();
        if ($defaultType) {
            $this->journal_type_id = $defaultType->id;
        }
    }

    public function openCreateModal(): void
    {
        $this->resetTemplateForm();
        $this->generateTemplateCode();
        $this->addLine();
        $this->addLine();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetTemplateForm();
        $template = JournalTemplate::with('lines')->findOrFail($id);

        $this->editingTemplateId = $template->id;
        $this->template_code = $template->template_code;
        $this->name = $template->name;
        $this->description = $template->description ?? '';
        $this->journal_type_id = $template->journal_type_id;
        $this->is_active = (bool) $template->is_active;

        $this->lines = [];
        foreach ($template->lines as $line) {
            $this->lines[] = [
                'account_id' => $line->account_id,
                'unit_id' => $line->unit_id ?? '',
                'description' => $line->description ?? '',
                'debit' => (float) $line->debit,
                'credit' => (float) $line->credit,
            ];
        }

        $this->showModal = true;
    }

    public function generateTemplateCode(): void
    {
        $companyId = Company::first()?->id ?? 1;
        $count = JournalTemplate::where('company_id', $companyId)->count() + 1;
        $this->template_code = sprintf('TPL-%04d', $count);
    }

    public function addLine(): void
    {
        $this->lines[] = [
            'account_id' => '',
            'unit_id' => '',
            'description' => '',
            'debit' => 0,
            'credit' => 0,
        ];
    }

    public function removeLine(int $index): void
    {
        if (count($this->lines) > 2) {
            unset($this->lines[$index]);
            $this->lines = array_values($this->lines);
        }
    }

    public function saveTemplate(): void
    {
        $this->validate([
            'template_code' => 'required|string|max:50|unique:journal_templates,template_code,'.$this->editingTemplateId,
            'name' => 'required|string|max:150',
            'journal_type_id' => 'required|exists:journal_types,id',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.unit_id' => 'nullable|exists:units,id',
        ]);

        $companyId = Company::first()?->id ?? 1;

        DB::transaction(function () use ($companyId) {
            $template = JournalTemplate::updateOrCreate(
                ['id' => $this->editingTemplateId],
                [
                    'company_id' => $companyId,
                    'template_code' => trim($this->template_code),
                    'name' => trim($this->name),
                    'description' => trim($this->description),
                    'journal_type_id' => $this->journal_type_id,
                    'is_active' => $this->is_active,
                    'created_by' => auth()->id(),
                ]
            );

            $template->lines()->delete();

            foreach ($this->lines as $index => $line) {
                JournalTemplateLine::create([
                    'journal_template_id' => $template->id,
                    'line_no' => $index + 1,
                    'account_id' => $line['account_id'],
                    'unit_id' => ! empty($line['unit_id']) ? $line['unit_id'] : null,
                    'description' => $line['description'] ?? null,
                    'debit' => (float) ($line['debit'] ?? 0),
                    'credit' => (float) ($line['credit'] ?? 0),
                ]);
            }

            session()->flash('message', $this->editingTemplateId ? "Template '{$template->name}' berhasil diperbarui." : "Template baru '{$template->name}' berhasil dibuat.");
        });

        $this->showModal = false;
        $this->resetTemplateForm();
    }

    public function deleteTemplate(int $id): void
    {
        $template = JournalTemplate::findOrFail($id);
        $template->delete();

        session()->flash('message', "Template '{$template->name}' berhasil dihapus.");
    }

    public function resetTemplateForm(): void
    {
        $this->editingTemplateId = null;
        $this->template_code = '';
        $this->name = '';
        $this->description = '';
        $this->is_active = true;
        $this->lines = [];
        $this->resetValidation();
    }

    public function render()
    {
        $templates = JournalTemplate::with(['lines.account', 'lines.unit', 'journalType', 'createdBy'])
            ->when($this->search !== '', fn ($q) => $q->where(fn ($sq) => $sq->where('template_code', 'like', "%{$this->search}%")
                ->orWhere('name', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%")))
            ->orderBy('name')
            ->paginate(15);

        $accounts = Account::posting()->active()->orderBy('code', 'asc')->get();
        $journalTypes = JournalType::orderBy('code')->get();
        $units = Unit::all();

        return view('livewire.accounting.journals.journal-template-index', [
            'templates' => $templates,
            'accounts' => $accounts,
            'journalTypes' => $journalTypes,
            'units' => $units,
        ]);
    }
}
