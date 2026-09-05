<?php

namespace App\Livewire\Accounting\Reconciliation;

use App\Domain\Banking\Services\BankReconciliationService;
use App\Models\Account;
use App\Models\BankStatement;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class BankReconciliationIndex extends Component
{
    use WithFileUploads;

    public int $selectedAccountId = 0;

    /** @var mixed */
    public $pdfFile = null;

    public bool $showUploadModal = false;

    public string $uploadError = '';

    public bool $isUploading = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('reconciliation.view') || auth()->user()?->can('reports.view'), 403, 'Akses Ditolak.');

        // Default ke akun Bank BRI jika ada
        $briAccount = Account::where('code', '11.02.03')->first();
        if (! $briAccount) {
            $briAccount = Account::where('type', 'BANK')->first();
        }

        if ($briAccount) {
            $this->selectedAccountId = (int) $briAccount->id;
        }
    }

    public function openUploadModal(): void
    {
        $this->reset(['pdfFile', 'uploadError', 'isUploading']);
        $this->showUploadModal = true;
    }

    public function closeUploadModal(): void
    {
        $this->showUploadModal = false;
        $this->reset(['pdfFile', 'uploadError', 'isUploading']);
    }

    public function processUpload(BankReconciliationService $service): void
    {
        abort_unless(auth()->user()?->can('reconciliation.upload') || auth()->user()?->can('reconciliation.manage'), 403, 'Akses Ditolak.');

        $this->validate([
            'selectedAccountId' => 'required|exists:accounts,id',
            'pdfFile' => 'required|file|mimes:pdf|max:20480', // max 20MB
        ], [
            'selectedAccountId.required' => 'Pilih akun bank terlebih dahulu.',
            'pdfFile.required' => 'Pilih berkas PDF rekening koran yang akan diunggah.',
            'pdfFile.mimes' => 'Berkas harus berformat PDF resmi CMS Bank.',
            'pdfFile.max' => 'Ukuran berkas PDF maksimal 20 MB.',
        ]);

        $this->isUploading = true;
        $this->uploadError = '';

        try {
            $originalName = $this->pdfFile->getClientOriginalName();
            $storedPath = $this->pdfFile->store('bank-statements', 'local');
            $fullPath = Storage::disk('local')->path($storedPath);

            $statement = $service->importStatement(
                $this->selectedAccountId,
                $fullPath,
                $originalName,
                auth()->user()
            );

            $this->closeUploadModal();
            session()->flash('success', "Rekening koran '{$originalName}' berhasil diproses! {$statement->lines()->count()} mutasi transaksi teridentifikasi.");

            $this->redirect(route('accounting.reconciliation.detail', $statement->id), navigate: true);
        } catch (Exception $e) {
            $this->uploadError = 'Gagal memproses rekening koran: '.$e->getMessage();
            $this->isUploading = false;
        }
    }

    public function deleteStatement(int $statementId): void
    {
        abort_unless(auth()->user()?->can('reconciliation.manage'), 403, 'Akses Ditolak.');

        $statement = BankStatement::findOrFail($statementId);
        if ($statement->file_path && file_exists($statement->file_path)) {
            @unlink($statement->file_path);
        }

        $statement->delete();
        session()->flash('success', 'Rekening koran berhasil dihapus.');
    }

    public function render(): View
    {
        $bankAccounts = Account::where('type', 'BANK')
            ->orWhere('code', 'like', '11.02%')
            ->posting()
            ->active()
            ->orderBy('code', 'asc')
            ->get();

        $statements = BankStatement::with(['account', 'uploader'])
            ->withCount('lines')
            ->orderBy('period_start', 'desc')
            ->get();

        $totalStatements = $statements->count();
        $totalReconciled = $statements->where('status', 'reconciled')->count();
        $totalInProgress = $statements->where('status', 'in_progress')->count();

        return view('livewire.accounting.reconciliation.bank-reconciliation-index', [
            'bankAccounts' => $bankAccounts,
            'statements' => $statements,
            'totalStatements' => $totalStatements,
            'totalReconciled' => $totalReconciled,
            'totalInProgress' => $totalInProgress,
        ])->layout('layouts.app');
    }
}
