<?php

namespace App\Livewire\Accounting\Journals;

use App\Domain\Accounting\Services\JournalPostingService;
use App\Domain\Accounting\Services\JournalReversalService;
use App\Livewire\Concerns\SyncsGlobalPeriod;
use App\Models\Company;
use App\Models\JournalEntry;
use Exception;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Jurnal Penyesuaian (Adjusting Journal Entries)')]
class AdjustmentIndex extends Component
{
    use SyncsGlobalPeriod;
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public string $startDate = '';

    public string $endDate = '';

    public function mount(): void
    {
        if (auth()->check() && ! auth()->user()->can('journals.view')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        $this->initializeGlobalPeriod();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStartDate(): void
    {
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->resetPage();
    }

    public function reverseJournal(int $journalId, JournalReversalService $reversalService): void
    {
        if (auth()->check() && ! auth()->user()->can('journals.post') && ! auth()->user()->can('journals.edit')) {
            session()->flash('error', 'Akses ditolak! Anda tidak memiliki izin untuk membalikkan (reverse) jurnal.');

            return;
        }

        try {
            $journal = JournalEntry::findOrFail($journalId);
            $reversal = $reversalService->reverseJournalEntry($journal, auth()->id());

            session()->flash('message', "Jurnal penyesuaian {$journal->entry_number} berhasil dibalikkan (reverse) dengan jurnal baru {$reversal->entry_number}.");
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function deleteJournal(int $journalId): void
    {
        if (auth()->check() && ! auth()->user()->can('journals.delete')) {
            session()->flash('error', 'Akses ditolak! Anda tidak memiliki izin [journals.delete] untuk menghapus jurnal.');

            return;
        }

        try {
            $journal = JournalEntry::findOrFail($journalId);
            $service = new JournalPostingService;
            $service->deleteJournalEntry($journal);

            session()->flash('message', "Jurnal penyesuaian {$journal->entry_number} berhasil dihapus.");
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $companyId = Company::first()?->id ?? 1;

        $journals = JournalEntry::where('company_id', $companyId)
            ->where('entry_type', 'adjustment')
            ->with(['lines.account', 'journalType'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('entry_number', 'like', "%{$this->search}%")
                        ->orWhere('document_number', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when(! empty($this->startDate), fn ($q) => $q->whereDate('entry_date', '>=', $this->startDate))
            ->when(! empty($this->endDate), fn ($q) => $q->whereDate('entry_date', '<=', $this->endDate))
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.accounting.journals.adjustment-index', [
            'journals' => $journals,
        ])->layout('layouts.app');
    }
}
