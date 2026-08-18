<?php

namespace App\Livewire\Accounting\Settings;

use App\Models\JournalType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Master Jenis Jurnal - ArtaLedger')]
class JournalTypeIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $code = '';

    public string $name = '';

    public string $description = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->code = '';
        $this->name = '';
        $this->description = '';
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $type = JournalType::findOrFail($id);
        $this->editingId = $type->id;
        $this->code = $type->code;
        $this->name = $type->name;
        $this->description = $type->description ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'code' => 'required|string|max:20|unique:journal_types,code,'.$this->editingId,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ];

        $this->validate($rules);

        JournalType::updateOrCreate(
            ['id' => $this->editingId],
            [
                'code' => strtoupper(trim($this->code)),
                'name' => trim($this->name),
                'description' => trim($this->description) ?: null,
            ]
        );

        session()->flash('message', $this->editingId ? 'Jenis Jurnal berhasil diperbarui.' : 'Jenis Jurnal baru berhasil ditambahkan.');
        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        $type = JournalType::findOrFail($id);

        if ($type->journalEntries()->count() > 0) {
            session()->flash('error', "Gagal menghapus! Jenis Jurnal '{$type->name}' sudah digunakan dalam transaksi jurnal.");

            return;
        }

        $type->delete();
        session()->flash('message', "Jenis Jurnal '{$type->name}' berhasil dihapus.");
    }

    public function render()
    {
        $journalTypes = JournalType::withCount('journalEntries')
            ->when($this->search !== '', function ($q) {
                $q->where('code', 'like', "%{$this->search}%")
                    ->orWhere('name', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            })
            ->orderBy('code')
            ->paginate(15);

        return view('livewire.accounting.settings.journal-type-index', [
            'journalTypes' => $journalTypes,
        ]);
    }
}
