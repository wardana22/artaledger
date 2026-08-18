<?php

namespace App\Livewire\Accounting\Journals;

use App\Domain\Accounting\Services\JournalReversalService;
use App\Models\JournalEntry;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Jurnal Umum (General Journal) - ArtaLedger')]
class JournalIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public int $perPage = 25;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function reverseJournal(int $id): void
    {
        try {
            $entry = JournalEntry::findOrFail($id);
            $service = new JournalReversalService;
            $reversal = $service->reverseJournalEntry($entry, auth()->id(), 'Pembalikan manual Jurnal '.$entry->entry_number);

            session()->flash('message', "Jurnal {$entry->entry_number} berhasil dibalikkan. Jurnal Reversal: {$reversal->entry_number}.");
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $query = JournalEntry::with(['lines.account', 'journalType', 'postedBy', 'period'])
            ->when($this->search !== '', fn ($q) => $q->where(fn ($sq) => $sq->where('entry_number', 'like', "%{$this->search}%")
                ->orWhere('document_number', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%")))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('id');

        $journals = $query->paginate($this->perPage);

        return view('livewire.accounting.journals.index', [
            'journals' => $journals,
        ]);
    }
}
