<?php

namespace App\Livewire\Accounting\Journals;

use App\Domain\Accounting\Services\JournalPostingService;
use App\Domain\Accounting\Services\JournalReversalService;
use App\Models\JournalEntry;
use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Jurnal Umum (General Journal) - ArtaLedger')]
class JournalIndex extends Component
{
    use WithPagination;

    #[Url(as: 'status')]
    public string $statusFilter = 'all';

    public string $search = '';

    public string $unitFilter = 'all';

    public string $startDate = '';

    public string $endDate = '';

    public int $perPage = 25;

    public bool $showDetailModal = false;

    public ?JournalEntry $selectedJournalDetail = null;

    public function mount(): void
    {
        if (request()->has('status') && ! empty(request()->get('status'))) {
            $this->statusFilter = request()->get('status');
        }
    }

    public function viewJournalDetail(int $id): void
    {
        $this->selectedJournalDetail = JournalEntry::with(['lines.account', 'lines.unit', 'journalType', 'postedBy', 'company', 'period'])
            ->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedJournalDetail = null;
    }

    public function postJournal(int $id): void
    {
        if (auth()->check() && ! auth()->user()->hasPermissionTo('journals.post')) {
            session()->flash('error', 'Akses ditolak! Anda tidak memiliki izin [journals.post] untuk memposting jurnal.');

            return;
        }

        try {
            $entry = JournalEntry::findOrFail($id);
            $service = new JournalPostingService;
            $service->postDraftEntry($entry, auth()->id());

            session()->flash('message', "Jurnal {$entry->entry_number} berhasil disetujui dan diposting ke Buku Besar!");
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingUnitFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStartDate(): void
    {
        $this->resetPage();
    }

    public function updatingEndDate(): void
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

    public function deleteJournal(int $id): void
    {
        try {
            $entry = JournalEntry::findOrFail($id);
            $service = new JournalPostingService;
            $service->deleteJournalEntry($entry);

            session()->flash('message', "Jurnal {$entry->entry_number} berhasil dihapus.");
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $query = JournalEntry::with(['lines.account', 'lines.unit', 'journalType', 'postedBy', 'period'])
            ->when($this->search !== '', fn ($q) => $q->where(fn ($sq) => $sq->where('entry_number', 'like', "%{$this->search}%")
                ->orWhere('document_number', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%")))
            ->when($this->statusFilter === 'draft', fn ($q) => $q->where('status', 'draft'))
            ->when($this->statusFilter === 'posted', fn ($q) => $q->where('status', 'posted'))
            ->when($this->statusFilter === 'reversed', fn ($q) => $q->where('status', 'reversed'))
            ->when($this->statusFilter === 'all', fn ($q) => $q->whereIn('status', ['posted', 'reversed']))
            ->when($this->unitFilter !== 'all', fn ($q) => $q->whereHas('lines', fn ($lq) => $lq->where('unit_id', $this->unitFilter)))
            ->when(! empty($this->startDate), fn ($q) => $q->whereDate('entry_date', '>=', $this->startDate))
            ->when(! empty($this->endDate), fn ($q) => $q->whereDate('entry_date', '<=', $this->endDate))
            ->orderByDesc('id');

        $journals = $query->paginate($this->perPage);
        $units = Unit::all();

        return view('livewire.accounting.journals.index', [
            'journals' => $journals,
            'units' => $units,
        ]);
    }
}
