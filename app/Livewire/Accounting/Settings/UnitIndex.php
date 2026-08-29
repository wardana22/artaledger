<?php

namespace App\Livewire\Accounting\Settings;

use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Master Unit Perusahaan - ArtaLedger')]
class UnitIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $code = '';

    public string $name = '';

    public string $keywords = '';

    public function mount(): void
    {
        if (auth()->check() && ! auth()->user()->can('settings.units') && ! auth()->user()->can('settings.manage')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->code = '';
        $this->name = '';
        $this->keywords = '';
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $unit = Unit::findOrFail($id);
        $this->editingId = $unit->id;
        $this->code = $unit->code;
        $this->name = $unit->name;
        $this->keywords = $unit->keywords ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'code' => 'required|string|max:20|unique:units,code,'.$this->editingId,
            'name' => 'required|string|max:255',
            'keywords' => 'nullable|string|max:1000',
        ];

        $this->validate($rules);

        Unit::updateOrCreate(
            ['id' => $this->editingId],
            [
                'code' => strtoupper(trim($this->code)),
                'name' => trim($this->name),
                'keywords' => trim($this->keywords) ?: null,
            ]
        );

        session()->flash('message', $this->editingId ? 'Unit Perusahaan berhasil diperbarui.' : 'Unit Perusahaan baru berhasil ditambahkan.');
        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        $unit = Unit::findOrFail($id);

        if ($unit->journalLines()->count() > 0) {
            session()->flash('error', "Gagal menghapus! Unit '{$unit->name}' sudah digunakan dalam baris transaksi jurnal.");

            return;
        }

        $unit->delete();
        session()->flash('message', "Unit '{$unit->name}' berhasil dihapus.");
    }

    public function render()
    {
        $units = Unit::withCount('journalLines')
            ->when($this->search !== '', function ($q) {
                $q->where('code', 'like', "%{$this->search}%")
                    ->orWhere('name', 'like', "%{$this->search}%")
                    ->orWhere('keywords', 'like', "%{$this->search}%");
            })
            ->orderBy('code')
            ->paginate($this->perPage);

        return view('livewire.accounting.settings.unit-index', [
            'units' => $units,
        ]);
    }
}
