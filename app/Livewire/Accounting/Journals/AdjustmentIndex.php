<?php

namespace App\Livewire\Accounting\Journals;

use App\Domain\Accounting\Services\JournalReversalService;
use App\Models\Company;
use App\Models\JournalEntry;
use Exception;
use Livewire\Component;
use Livewire\WithPagination;

class AdjustmentIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function reverseJournal(int $journalId, JournalReversalService $reversalService): void
    {
        try {
            $journal = JournalEntry::findOrFail($journalId);
            $reversal = $reversalService->reverseJournalEntry($journal, auth()->id());

            session()->flash('message', "Jurnal penyesuaian {$journal->entry_number} berhasil dibalikkan (reverse) dengan jurnal baru {$reversal->entry_number}.");
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
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.accounting.journals.adjustment-index', [
            'journals' => $journals,
        ])->layout('layouts.app');
    }
}
