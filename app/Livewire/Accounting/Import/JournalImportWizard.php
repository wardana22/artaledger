<?php

namespace App\Livewire\Accounting\Import;

use App\Domain\Import\Services\ExcelImportService;
use App\Domain\Import\Services\ImportCommitService;
use App\Domain\Import\Services\ImportValidationService;
use App\Models\ImportBatch;
use App\Models\ImportMappingPreset;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Import Jurnal Transaksi Excel')]
class JournalImportWizard extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $file = null;

    // Wizard Step: 1 = Upload, 2 = Mapping & Preview, 3 = Staged / Validated
    public int $wizardStep = 1;

    // Temporary uploaded file details for Step 2
    public ?string $tempUploadedPath = null;

    public string $originalFilename = '';

    // Sheet and inspection data
    public array $availableSheets = [];

    public string $selectedSheet = '';

    public array $sampleRows = [];

    public array $availableColumns = [];

    // Mapping fields
    public int $startRow = 2;

    public string $colDate = '';

    public string $colDocNo = '';

    public string $colDesc = '';

    public string $colAccount = '';

    public string $colSubAccount = '';

    public string $colUnit = '';

    public string $colDebit = '';

    public string $colCredit = '';

    // Navigation Tab: 'import' or 'settings'
    public string $activeTab = 'import';

    // Presets
    public ?int $selectedPresetId = null;

    public string $newPresetName = '';

    public bool $showSavePresetModal = false;

    // Preset Management in Settings Tab
    public ?int $editingPresetId = null;

    public string $editPresetName = '';

    public int $editStartRow = 2;

    public string $editColDate = '';

    public string $editColDocNo = '';

    public string $editColDesc = '';

    public string $editColAccount = '';

    public string $editColSubAccount = '';

    public string $editColUnit = '';

    public string $editColDebit = '';

    public string $editColCredit = '';

    public bool $showEditPresetModal = false;

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
        $this->handleFileUpload(app(ExcelImportService::class));
    }

    public function mount(?int $batchId = null): void
    {
        if (auth()->check() && ! auth()->user()->can('journals.import') && ! auth()->user()->can('journals.view')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

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
        if (auth()->check() && ! auth()->user()->can('journals.delete')) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin [journals.delete] untuk menghapus data impor.');
        }

        set_time_limit(300);
        ini_set('memory_limit', '512M');

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

    public function handleFileUpload(ExcelImportService $importService): void
    {
        if (auth()->check() && ! auth()->user()->can('journals.import')) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin [journals.import] untuk mengunggah berkas impor.');
        }

        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:20480',
        ]);

        try {
            $this->originalFilename = $this->file->getClientOriginalName();
            // Simpan file sementara di storage local agar bisa diinspeksi dan diproses
            $storedName = 'import_temp_'.now()->format('YmdHis').'_'.Str::random(6).'.'.$this->file->getClientOriginalExtension();
            $this->tempUploadedPath = $this->file->storeAs('imports/temp', $storedName, 'local');
            $fullPath = Storage::disk('local')->path($this->tempUploadedPath);

            $inspected = $importService->inspectSpreadsheet($fullPath);
            $this->availableSheets = $inspected['sheets'];
            $this->selectedSheet = $inspected['active_sheet'];
            $this->sampleRows = $inspected['sample_rows'];
            $this->availableColumns = $inspected['columns'];

            // Coba auto-detect preset default (Cleaned vs Legacy)
            $this->autoDetectPreset();

            $this->wizardStep = 2;
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membaca berkas: '.$e->getMessage());
        }
    }

    public function autoDetectPreset(): void
    {
        // 1. Cek format cleaned (B=Tanggal, C=No.Bukti, D=Keterangan, E=Akun, H=Debit, I=Kredit, mulai baris 4)
        $headerCleaned = $this->sampleRows[3] ?? [];
        $valB = strtoupper(trim((string) ($headerCleaned['B'] ?? '')));
        $valE = strtoupper(trim((string) ($headerCleaned['E'] ?? '')));

        if (str_contains($valB, 'TANGGAL') || str_contains($valE, 'KODE AKUN')) {
            $this->startRow = 4;
            $this->colDate = 'B';
            $this->colDocNo = 'C';
            $this->colDesc = 'D';
            $this->colAccount = 'E';
            $this->colUnit = 'G';
            $this->colDebit = 'H';
            $this->colCredit = 'I';

            return;
        }

        // 2. Cek format raw legacy (M=Tanggal, N=No.Bukti, O=Keterangan, Q=Akun Induk, S=Sub, T=Debit, U=Kredit, mulai baris 10)
        $headerRaw = $this->sampleRows[9] ?? ($this->sampleRows[1] ?? []);
        $valM = strtoupper(trim((string) ($headerRaw['M'] ?? '')));
        if (str_contains($valM, 'TGL') || str_contains($valM, 'TANGGAL') || isset($headerRaw['M'])) {
            $this->startRow = 10;
            $this->colDate = 'M';
            $this->colDocNo = 'N';
            $this->colDesc = 'O';
            $this->colAccount = 'Q';
            $this->colSubAccount = 'S';
            $this->colUnit = '';
            $this->colDebit = 'T';
            $this->colCredit = 'U';

            return;
        }

        // 3. Fallback default sederhana
        $this->startRow = 2;
        $this->colDate = in_array('A', $this->availableColumns, true) ? 'A' : '';
        $this->colDocNo = in_array('B', $this->availableColumns, true) ? 'B' : '';
        $this->colDesc = in_array('C', $this->availableColumns, true) ? 'C' : '';
        $this->colAccount = in_array('D', $this->availableColumns, true) ? 'D' : '';
        $this->colSubAccount = '';
        $this->colDebit = in_array('E', $this->availableColumns, true) ? 'E' : '';
        $this->colCredit = in_array('F', $this->availableColumns, true) ? 'F' : '';
    }

    public function updatedSelectedSheet(ExcelImportService $importService): void
    {
        if (! $this->tempUploadedPath) {
            return;
        }

        $fullPath = Storage::disk('local')->path($this->tempUploadedPath);
        $inspected = $importService->inspectSpreadsheet($fullPath, $this->selectedSheet);
        $this->sampleRows = $inspected['sample_rows'];
        $this->availableColumns = $inspected['columns'];
    }

    public function applyPreset(int $presetId): void
    {
        $preset = ImportMappingPreset::find($presetId);
        if (! $preset) {
            return;
        }

        $this->selectedPresetId = $preset->id;
        $this->startRow = $preset->start_row ?? 2;
        if ($preset->sheet_name && in_array($preset->sheet_name, $this->availableSheets, true)) {
            $this->selectedSheet = $preset->sheet_name;
        }

        $cfg = $preset->mapping_config ?? [];
        $this->colDate = $cfg['col_date'] ?? '';
        $this->colDocNo = $cfg['col_doc_no'] ?? '';
        $this->colDesc = $cfg['col_desc'] ?? '';
        $this->colAccount = $cfg['col_account'] ?? '';
        $this->colSubAccount = $cfg['col_sub_account'] ?? '';
        $this->colUnit = $cfg['col_unit'] ?? '';
        $this->colDebit = $cfg['col_debit'] ?? '';
        $this->colCredit = $cfg['col_credit'] ?? '';

        session()->flash('message', "Preset mapping '{$preset->name}' berhasil dimuat!");
    }

    public function applySystemPreset(string $type): void
    {
        if ($type === 'cleaned') {
            $this->startRow = 4;
            $this->colDate = 'B';
            $this->colDocNo = 'C';
            $this->colDesc = 'D';
            $this->colAccount = 'E';
            $this->colSubAccount = '';
            $this->colUnit = 'G';
            $this->colDebit = 'H';
            $this->colCredit = 'I';
            session()->flash('message', 'Format Standar Bersih (Cleaned) diterapkan!');
        } elseif ($type === 'legacy') {
            $this->startRow = 10;
            $this->colDate = 'M';
            $this->colDocNo = 'N';
            $this->colDesc = 'O';
            $this->colAccount = 'Q';
            $this->colSubAccount = 'S';
            $this->colUnit = '';
            $this->colDebit = 'T';
            $this->colCredit = 'U';
            session()->flash('message', 'Format Template Mentah (Legacy) diterapkan!');
        }
    }

    public function savePreset(): void
    {
        $this->validate([
            'newPresetName' => 'required|string|max:100',
            'colAccount' => 'required|string',
            'colDebit' => 'required|string',
            'colCredit' => 'required|string',
        ]);

        $preset = ImportMappingPreset::create([
            'user_id' => auth()->id(),
            'name' => $this->newPresetName,
            'sheet_name' => $this->selectedSheet ?: null,
            'start_row' => $this->startRow,
            'mapping_config' => [
                'col_date' => $this->colDate,
                'col_doc_no' => $this->colDocNo,
                'col_desc' => $this->colDesc,
                'col_account' => $this->colAccount,
                'col_sub_account' => $this->colSubAccount,
                'col_unit' => $this->colUnit,
                'col_debit' => $this->colDebit,
                'col_credit' => $this->colCredit,
            ],
        ]);

        $this->selectedPresetId = $preset->id;
        $this->newPresetName = '';
        $this->showSavePresetModal = false;

        session()->flash('message', "Preset '{$preset->name}' berhasil disimpan dan siap dipakai kapan saja!");
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['import', 'settings'], true) ? $tab : 'import';
    }

    public function openCreatePresetModal(): void
    {
        $this->editingPresetId = null;
        $this->editPresetName = '';
        $this->editStartRow = 2;
        $this->editColDate = 'A';
        $this->editColDocNo = 'B';
        $this->editColDesc = 'C';
        $this->editColAccount = 'D';
        $this->editColSubAccount = '';
        $this->editColUnit = '';
        $this->editColDebit = 'E';
        $this->editColCredit = 'F';

        $this->showEditPresetModal = true;
    }

    public function openEditPresetModal(int $presetId): void
    {
        $preset = ImportMappingPreset::find($presetId);
        if (! $preset) {
            return;
        }

        $this->editingPresetId = $preset->id;
        $this->editPresetName = $preset->name;
        $this->editStartRow = $preset->start_row ?? 2;
        $config = $preset->mapping_config ?? [];
        $this->editColDate = $config['col_date'] ?? '';
        $this->editColDocNo = $config['col_doc_no'] ?? '';
        $this->editColDesc = $config['col_desc'] ?? '';
        $this->editColAccount = $config['col_account'] ?? '';
        $this->editColSubAccount = $config['col_sub_account'] ?? '';
        $this->editColUnit = $config['col_unit'] ?? '';
        $this->editColDebit = $config['col_debit'] ?? '';
        $this->editColCredit = $config['col_credit'] ?? '';

        $this->showEditPresetModal = true;
    }

    public function updatePreset(): void
    {
        $this->validate([
            'editPresetName' => 'required|string|max:100',
            'editStartRow' => 'required|integer|min:1',
            'editColAccount' => 'required|string',
            'editColDebit' => 'required|string',
            'editColCredit' => 'required|string',
        ]);

        $mappingConfig = [
            'col_date' => strtoupper(trim($this->editColDate)),
            'col_doc_no' => strtoupper(trim($this->editColDocNo)),
            'col_desc' => strtoupper(trim($this->editColDesc)),
            'col_account' => strtoupper(trim($this->editColAccount)),
            'col_sub_account' => strtoupper(trim($this->editColSubAccount)),
            'col_unit' => strtoupper(trim($this->editColUnit)),
            'col_debit' => strtoupper(trim($this->editColDebit)),
            'col_credit' => strtoupper(trim($this->editColCredit)),
        ];

        if ($this->editingPresetId) {
            $preset = ImportMappingPreset::find($this->editingPresetId);
            if ($preset) {
                $preset->update([
                    'name' => $this->editPresetName,
                    'start_row' => $this->editStartRow,
                    'mapping_config' => $mappingConfig,
                ]);
                session()->flash('message', "Preset '{$preset->name}' berhasil diperbarui.");
            }
        } else {
            $preset = ImportMappingPreset::create([
                'user_id' => auth()->id(),
                'name' => $this->editPresetName,
                'start_row' => $this->editStartRow,
                'mapping_config' => $mappingConfig,
            ]);
            session()->flash('message', "Preset '{$preset->name}' berhasil ditambahkan.");
        }

        $this->showEditPresetModal = false;
        $this->editingPresetId = null;
    }

    public function deletePreset(int $presetId): void
    {
        $preset = ImportMappingPreset::find($presetId);
        if ($preset) {
            $name = $preset->name;
            $preset->delete();
            if ($this->selectedPresetId === $presetId) {
                $this->selectedPresetId = null;
            }
            if ($this->editingPresetId === $presetId) {
                $this->editingPresetId = null;
                $this->showEditPresetModal = false;
            }
            session()->flash('message', "Preset '{$name}' telah dihapus.");
        }
    }

    public function getLivePreviewRowsProperty(): array
    {
        $preview = [];
        $count = 0;

        foreach ($this->sampleRows as $rowIndex => $row) {
            if ($rowIndex < $this->startRow) {
                continue;
            }

            $dateVal = $this->colDate !== '' ? ($row[$this->colDate] ?? null) : null;
            $docVal = $this->colDocNo !== '' ? ($row[$this->colDocNo] ?? null) : null;
            $descVal = $this->colDesc !== '' ? ($row[$this->colDesc] ?? null) : null;

            $parentVal = $this->colAccount !== '' ? ($row[$this->colAccount] ?? null) : null;
            $subVal = $this->colSubAccount !== '' ? ($row[$this->colSubAccount] ?? null) : null;
            $accVal = ! empty($subVal) ? $subVal : $parentVal;

            $unitVal = $this->colUnit !== '' ? ($row[$this->colUnit] ?? null) : null;
            $debitVal = $this->colDebit !== '' ? ($row[$this->colDebit] ?? null) : 0;
            $creditVal = $this->colCredit !== '' ? ($row[$this->colCredit] ?? null) : 0;

            $preview[] = [
                'row_index' => $rowIndex,
                'date' => $dateVal,
                'doc_no' => $docVal,
                'description' => $descVal,
                'account' => $accVal,
                'unit' => $unitVal,
                'debit' => $debitVal,
                'credit' => $creditVal,
            ];

            $count++;
            if ($count >= 5) {
                break;
            }
        }

        return $preview;
    }

    public function processImportWithMapping(ExcelImportService $importService, ImportValidationService $validationService): void
    {
        if (auth()->check() && ! auth()->user()->can('journals.import')) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin [journals.import].');
        }

        $this->validate([
            'startRow' => 'required|integer|min:1',
            'colDate' => 'required|string',
            'colAccount' => 'required|string',
            'colDebit' => 'required|string',
            'colCredit' => 'required|string',
        ]);

        if (! $this->tempUploadedPath || ! Storage::disk('local')->exists($this->tempUploadedPath)) {
            session()->flash('error', 'Berkas upload tidak ditemukan atau sesi telah kadaluarsa. Silakan unggah ulang.');
            $this->wizardStep = 1;

            return;
        }

        set_time_limit(300);
        ini_set('memory_limit', '512M');

        try {
            // Purge previous unposted staging batch
            if ($this->activeBatch && $this->activeBatch->status !== 'posted') {
                $this->deleteBatch($this->activeBatch->id);
            }

            $fullPath = Storage::disk('local')->path($this->tempUploadedPath);

            $mapping = [
                'sheet_name' => $this->selectedSheet ?: null,
                'start_row' => $this->startRow,
                'col_date' => $this->colDate,
                'col_doc_no' => $this->colDocNo,
                'col_desc' => $this->colDesc,
                'col_account' => $this->colAccount,
                'col_sub_account' => $this->colSubAccount,
                'col_unit' => $this->colUnit,
                'col_debit' => $this->colDebit,
                'col_credit' => $this->colCredit,
            ];

            $batch = $importService->importFile($fullPath, $this->originalFilename, auth()->id(), $mapping);
            $batch = $validationService->validateBatch($batch);

            $this->activeBatch = $batch;
            $this->statusFilter = $batch->error_rows > 0 ? 'error' : 'all';
            $this->wizardStep = 3;
            $this->resetPage();

            session()->flash('message', "File '{$this->originalFilename}' berhasil diproses dengan pemetaan kolom dinamis. Terdapat {$batch->total_rows} baris transaksi.");
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function backToStep1(): void
    {
        if ($this->tempUploadedPath && Storage::disk('local')->exists($this->tempUploadedPath)) {
            Storage::disk('local')->delete($this->tempUploadedPath);
        }
        $this->tempUploadedPath = null;
        $this->file = null;
        $this->wizardStep = 1;
    }

    public function commitPosting(ImportCommitService $commitService)
    {
        if (auth()->check() && ! auth()->user()->can('journals.post')) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin [journals.post] untuk memposting transaksi ke General Ledger.');
        }

        set_time_limit(300);
        ini_set('memory_limit', '512M');

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

        if ($this->tempUploadedPath && Storage::disk('local')->exists($this->tempUploadedPath)) {
            Storage::disk('local')->delete($this->tempUploadedPath);
        }

        $this->file = null;
        $this->tempUploadedPath = null;
        $this->activeBatch = null;
        $this->wizardStep = 1;
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
        $presets = ImportMappingPreset::latest()->get();

        return view('livewire.accounting.import.journal-import-wizard', [
            'rows' => $rows,
            'totalBatchDebit' => $totalBatchDebit,
            'totalBatchCredit' => $totalBatchCredit,
            'batchDifference' => abs($totalBatchDebit - $totalBatchCredit),
            'errorRowsSummary' => $errorRowsSummary,
            'headerAccountErrorCount' => $headerAccountErrorCount,
            'recentBatches' => $recentBatches,
            'presets' => $presets,
            'livePreviewRows' => $this->livePreviewRows,
        ]);
    }
}
