<?php

namespace App\Livewire\Accounting\Import;

use App\Domain\Import\Services\ExcelImportService;
use App\Domain\Import\Services\ImportCommitService;
use App\Domain\Import\Services\ImportValidationService;
use App\Models\ImportBatch;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Import Jurnal Transaksi Excel - ArtaLedger')]
class JournalImportWizard extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $file = null;

    public ?ImportBatch $activeBatch = null;

    public string $statusFilter = 'all';

    public string $search = '';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFile(): void
    {
        $this->processUpload(app(ExcelImportService::class), app(ImportValidationService::class));
    }

    public function mount(?int $batchId = null): void
    {
        if ($batchId) {
            $this->activeBatch = ImportBatch::find($batchId);
        }
    }

    public function loadBatch(int $batchId): void
    {
        $this->activeBatch = ImportBatch::find($batchId);
        if ($this->activeBatch) {
            $this->statusFilter = $this->activeBatch->error_rows > 0 ? 'error' : 'all';
            $this->resetPage();
        }
    }

    public function deleteBatch(int $batchId): void
    {
        $batch = ImportBatch::find($batchId);
        if (! $batch) {
            return;
        }

        $batchCode = $batch->batch_code;

        DB::transaction(function () use ($batch) {
            // Find all import row IDs belonging to this batch
            $importRowIds = $batch->rows()->pluck('id')->toArray();

            if (! empty($importRowIds)) {
                // Find all journal entry IDs created by these import rows
                $journalEntryIds = JournalLine::whereIn('source_import_row_id', $importRowIds)
                    ->pluck('journal_entry_id')
                    ->filter()
                    ->unique()
                    ->toArray();

                if (! empty($journalEntryIds)) {
                    // Delete journal lines first
                    JournalLine::whereIn('journal_entry_id', $journalEntryIds)->delete();
                    // Delete journal entries
                    JournalEntry::whereIn('id', $journalEntryIds)->delete();
                }
            }

            // Delete staging records
            $batch->rows()->delete();
            $batch->files()->delete();
            $batch->delete();
        });

        if ($this->activeBatch?->id === $batchId) {
            $this->activeBatch = null;
        }

        session()->flash('message', "Seluruh data transaksi jurnal dari batch {$batchCode} berhasil dihapus dari sistem.");
    }

    public function processUpload(ExcelImportService $importService, ImportValidationService $validationService): void
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        if (! $this->file) {
            return;
        }

        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:20480',
        ]);

        try {
            // Purge previous unposted staging batch to keep database clean
            if ($this->activeBatch && $this->activeBatch->status !== 'posted') {
                $this->deleteBatch($this->activeBatch->id);
            }

            $path = $this->file->getRealPath();
            $originalName = $this->file->getClientOriginalName();

            // Step 1 - 4: Import file to staging
            $batch = $importService->importFile($path, $originalName, auth()->id());

            // Step 5 - 6: Run validation
            $batch = $validationService->validateBatch($batch);

            $this->activeBatch = $batch;
            $this->statusFilter = $batch->error_rows > 0 ? 'error' : 'all';
            $this->resetPage();

            session()->flash('message', "File '{$originalName}' berhasil dibaca dan dikonversi. Terdapat {$batch->total_rows} baris transaksi.");
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function commitPosting(ImportCommitService $commitService)
    {
        if (! $this->activeBatch) {
            return;
        }

        try {
            // Step 7: Commit transaction
            $entries = $commitService->commitBatch($this->activeBatch, auth()->id());
            $count = count($entries);

            session()->flash('message', "Berhasil memposting {$count} jurnal transaksi ke General Ledger!");

            return $this->redirect(route('accounting.journals.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function resetWizard(): void
    {
        if ($this->activeBatch && $this->activeBatch->status !== 'posted') {
            $this->deleteBatch($this->activeBatch->id);
        }

        $this->file = null;
        $this->activeBatch = null;
        $this->statusFilter = 'all';
        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        $rows = collect();
        $totalBatchDebit = 0.0;
        $totalBatchCredit = 0.0;
        $errorRowsSummary = collect();
        $headerAccountErrorCount = 0;

        if ($this->activeBatch) {
            $totalBatchDebit = (float) $this->activeBatch->rows()->sum('debit');
            $totalBatchCredit = (float) $this->activeBatch->rows()->sum('credit');

            if ($this->activeBatch->error_rows > 0) {
                $errorRowsSummary = $this->activeBatch->rows()
                    ->where('validation_status', 'error')
                    ->limit(50)
                    ->get();

                $headerAccountErrorCount = $this->activeBatch->rows()
                    ->where('validation_status', 'error')
                    ->where('validation_messages', 'like', '%Header%')
                    ->count();
            }

            $query = $this->activeBatch->rows()->with('account', 'unit');

            if ($this->statusFilter !== 'all') {
                $query->where('validation_status', $this->statusFilter);
            }

            if (! empty($this->search)) {
                $term = '%'.$this->search.'%';
                $query->where(function ($q) use ($term) {
                    $q->where('description', 'like', $term)
                        ->orWhere('document_number', 'like', $term)
                        ->orWhere('raw_account_code', 'like', $term);
                });
            }

            $rows = $query->paginate(10);

            if ($rows->currentPage() > $rows->lastPage() && $rows->lastPage() > 0) {
                $this->resetPage();
                $rows = $query->paginate(10);
            }
        }

        // Display all import batches in history
        $recentBatches = ImportBatch::with('user')->latest()->take(10)->get();

        return view('livewire.accounting.import.journal-import-wizard', [
            'rows' => $rows,
            'totalBatchDebit' => $totalBatchDebit,
            'totalBatchCredit' => $totalBatchCredit,
            'batchDifference' => abs($totalBatchDebit - $totalBatchCredit),
            'errorRowsSummary' => $errorRowsSummary,
            'headerAccountErrorCount' => $headerAccountErrorCount,
            'recentBatches' => $recentBatches,
        ]);
    }
}
