<?php

namespace App\Livewire\Accounting\Reconciliation;

use App\Domain\Banking\Services\BankReconciliationService;
use App\Models\Account;
use App\Models\BankStatement;
use App\Models\BankStatementLine;
use App\Models\JournalLine;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class BankReconciliationDetail extends Component
{
    public BankStatement $statement;

    // Filter status di panel bank
    public string $bankFilter = 'all'; // all, unmatched, matched

    public string $search = '';

    // Selection untuk manual match
    public ?int $selectedBankLineId = null;

    public ?int $selectedBookLineId = null;

    // Quick adjustment modal
    public bool $showAdjustmentModal = false;

    public ?int $adjustmentLineId = null;

    public ?BankStatementLine $adjustmentLine = null;

    public int $contraAccountId = 0;

    public string $adjustmentDescription = '';

    public function mount(BankStatement $statement): void
    {
        abort_unless(auth()->user()?->can('reconciliation.view') || auth()->user()?->can('reports.view'), 403, 'Akses Ditolak.');
        $this->statement = $statement;
    }

    public function runAutoMatch(BankReconciliationService $service): void
    {
        abort_unless(auth()->user()?->can('reconciliation.manage'), 403, 'Akses Ditolak.');

        $result = $service->autoMatch($this->statement, auth()->user());
        $this->statement->refresh();

        session()->flash('success', "Auto-Match selesai! Berhasil mencocokkan {$result['matched_count']} transaksi. Sisa belum cocok: {$result['remaining_unmatched']} transaksi.");
    }

    public function selectBankLine(int $lineId): void
    {
        if ($this->selectedBankLineId === $lineId) {
            $this->selectedBankLineId = null;
        } else {
            $this->selectedBankLineId = $lineId;
        }
    }

    public function selectBookLine(int $lineId): void
    {
        if ($this->selectedBookLineId === $lineId) {
            $this->selectedBookLineId = null;
        } else {
            $this->selectedBookLineId = $lineId;
        }
    }

    public function executeManualMatch(BankReconciliationService $service): void
    {
        abort_unless(auth()->user()?->can('reconciliation.manage'), 403, 'Akses Ditolak.');

        if (! $this->selectedBankLineId || ! $this->selectedBookLineId) {
            session()->flash('error', 'Pilih 1 baris mutasi bank dan 1 baris buku kas/bank untuk dicocokkan.');

            return;
        }

        try {
            $service->manualMatch($this->selectedBankLineId, $this->selectedBookLineId, auth()->user());
            $this->reset(['selectedBankLineId', 'selectedBookLineId']);
            $this->statement->refresh();
            session()->flash('success', 'Transaksi berhasil dicocokkan secara manual.');
        } catch (Exception $e) {
            session()->flash('error', 'Gagal mencocokkan: '.$e->getMessage());
        }
    }

    public function unmatchLine(int $lineId, BankReconciliationService $service): void
    {
        abort_unless(auth()->user()?->can('reconciliation.manage'), 403, 'Akses Ditolak.');

        $service->unmatch($lineId);
        $this->statement->refresh();
        session()->flash('success', 'Pencocokan transaksi telah dibatalkan.');
    }

    public function openAdjustmentModal(int $lineId): void
    {
        $this->adjustmentLineId = $lineId;
        $this->adjustmentLine = BankStatementLine::findOrFail($lineId);
        $this->adjustmentDescription = $this->adjustmentLine->description;

        // Auto pilih akun contra yang relevan
        $isDebit = (float) $this->adjustmentLine->debit > 0;
        if ($isDebit) {
            // Pengeluaran di bank -> default ke beban administrasi bank
            $adminExpense = Account::where('name', 'like', '%administrasi%')
                ->orWhere('name', 'like', '%admin bank%')
                ->orWhere('code', 'like', '5%')
                ->posting()->active()->first();
            $this->contraAccountId = $adminExpense ? (int) $adminExpense->id : 0;
        } else {
            // Penerimaan di bank -> default ke pendapatan bunga / jasa giro
            $interestRev = Account::where('name', 'like', '%bunga%')
                ->orWhere('name', 'like', '%jasa giro%')
                ->orWhere('code', 'like', '4%')
                ->posting()->active()->first();
            $this->contraAccountId = $interestRev ? (int) $interestRev->id : 0;
        }

        $this->showAdjustmentModal = true;
    }

    public function closeAdjustmentModal(): void
    {
        $this->showAdjustmentModal = false;
        $this->reset(['adjustmentLineId', 'adjustmentLine', 'contraAccountId', 'adjustmentDescription']);
    }

    public function submitAdjustment(BankReconciliationService $service): void
    {
        abort_unless(auth()->user()?->can('reconciliation.manage'), 403, 'Akses Ditolak.');

        $this->validate([
            'contraAccountId' => 'required|exists:accounts,id',
            'adjustmentDescription' => 'required|string|max:255',
        ], [
            'contraAccountId.required' => 'Pilih akun lawan penyesuaian (beban atau pendapatan).',
            'adjustmentDescription.required' => 'Keterangan penyesuaian wajib diisi.',
        ]);

        if (! $this->adjustmentLineId) {
            return;
        }

        try {
            $service->createQuickAdjustment(
                $this->adjustmentLineId,
                $this->contraAccountId,
                $this->adjustmentDescription,
                auth()->user()
            );

            $this->closeAdjustmentModal();
            $this->statement->refresh();
            session()->flash('success', 'Jurnal penyesuaian berhasil dibuat dan mutasi bank otomatis ditandai cocok.');
        } catch (Exception $e) {
            session()->flash('error', 'Gagal membuat jurnal penyesuaian: '.$e->getMessage());
        }
    }

    public function render(BankReconciliationService $service): View
    {
        // 1. Query Baris Mutasi Bank
        $bankLinesQuery = $this->statement->lines()->with(['matchedJournalLine.journalEntry']);

        if ($this->bankFilter === 'unmatched') {
            $bankLinesQuery->where('match_status', 'unmatched');
        } elseif ($this->bankFilter === 'matched') {
            $bankLinesQuery->whereIn('match_status', ['matched', 'manual_matched', 'adjusted']);
        }

        if (! empty($this->search)) {
            $s = '%'.$this->search.'%';
            $bankLinesQuery->where(function ($q) use ($s) {
                $q->where('description', 'like', $s)
                    ->orWhere('teller_id', 'like', $s)
                    ->orWhere('debit', 'like', $s)
                    ->orWhere('credit', 'like', $s);
            });
        }

        $bankLines = $bankLinesQuery->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // 2. Query Baris Buku Kas / Bank (General Ledger Lines)
        $matchedIds = BankStatementLine::whereNotNull('matched_journal_line_id')->pluck('matched_journal_line_id')->toArray();
        $minDate = Carbon::parse($this->statement->period_start)->subDays(7)->format('Y-m-d');
        $maxDate = Carbon::parse($this->statement->period_end)->addDays(7)->format('Y-m-d');

        $bookLines = JournalLine::with(['journalEntry', 'unit'])
            ->where('account_id', $this->statement->account_id)
            ->whereHas('journalEntry', function ($q) use ($minDate, $maxDate) {
                $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$minDate, $maxDate]);
            })
            ->orderBy('journal_entry_id', 'asc')
            ->get();

        // 3. Ringkasan Rekonsiliasi
        $summary = $service->calculateSummary($this->statement);

        // 4. Daftar Akun untuk Adjustment Modal
        $allAccounts = Account::posting()->active()->orderBy('code', 'asc')->get();

        return view('livewire.accounting.reconciliation.bank-reconciliation-detail', [
            'bankLines' => $bankLines,
            'bookLines' => $bookLines,
            'matchedIds' => $matchedIds,
            'summary' => $summary,
            'allAccounts' => $allAccounts,
        ])->layout('layouts.app');
    }
}
