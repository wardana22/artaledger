<?php

namespace App\Livewire\Accounting\Reconciliation;

use App\Domain\Banking\Services\BankReconciliationService;
use App\Models\Account;
use App\Models\BankStatement;
use App\Models\BankStatementLine;
use App\Models\JournalLine;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * @property-read float $selectedBankTotal
 * @property-read float $selectedBookTotal
 * @property-read float $difference
 * @property-read bool $isMatchValid
 */
class BankReconciliationDetail extends Component
{
    public BankStatement $statement;

    // Filter status di panel bank
    public string $bankFilter = 'all'; // all, unmatched, matched

    public string $search = '';

    // Filter status di panel buku kas/bank
    public string $bookFilter = 'all'; // all, unmatched, matched

    public string $bookPeriodFilter = 'all'; // all, current, prior, next

    public string $bookSearch = '';

    // Selection untuk manual multi-match (N:M)
    /** @var array<int> */
    public array $selectedBankLineIds = [];

    /** @var array<int> */
    public array $selectedBookLineIds = [];

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

    public function toggleBankLine(int $lineId): void
    {
        if (in_array($lineId, $this->selectedBankLineIds)) {
            $this->selectedBankLineIds = array_values(array_diff($this->selectedBankLineIds, [$lineId]));
        } else {
            $this->selectedBankLineIds[] = $lineId;
        }
    }

    public function toggleBookLine(int $lineId): void
    {
        if (in_array($lineId, $this->selectedBookLineIds)) {
            $this->selectedBookLineIds = array_values(array_diff($this->selectedBookLineIds, [$lineId]));
        } else {
            $this->selectedBookLineIds[] = $lineId;
        }
    }

    public function selectBankLine(int $lineId): void
    {
        $this->toggleBankLine($lineId);
    }

    public function selectBookLine(int $lineId): void
    {
        $this->toggleBookLine($lineId);
    }

    public function clearSelection(): void
    {
        $this->selectedBankLineIds = [];
        $this->selectedBookLineIds = [];
    }

    public function getSelectedBankTotalProperty(): float
    {
        if (empty($this->selectedBankLineIds)) {
            return 0.0;
        }

        return (float) BankStatementLine::whereIn('id', $this->selectedBankLineIds)
            ->get()
            ->sum(function (BankStatementLine $l) {
                return (float) $l->debit > 0 ? (float) $l->debit : (float) $l->credit;
            });
    }

    public function getSelectedBookTotalProperty(): float
    {
        if (empty($this->selectedBookLineIds)) {
            return 0.0;
        }

        return (float) JournalLine::whereIn('id', $this->selectedBookLineIds)
            ->get()
            ->sum(function (JournalLine $l) {
                return (float) $l->debit > 0 ? (float) $l->debit : (float) $l->credit;
            });
    }

    public const MATCH_TOLERANCE = 2.00;

    public function getDifferenceProperty(): float
    {
        return abs($this->getSelectedBankTotalProperty() - $this->getSelectedBookTotalProperty());
    }

    public function getIsMatchValidProperty(): bool
    {
        return count($this->selectedBankLineIds) > 0
            && count($this->selectedBookLineIds) > 0
            && $this->getDifferenceProperty() <= self::MATCH_TOLERANCE;
    }

    public function executeManualMatch(BankReconciliationService $service): void
    {
        abort_unless(auth()->user()?->can('reconciliation.manage'), 403, 'Akses Ditolak.');

        if (empty($this->selectedBankLineIds) || empty($this->selectedBookLineIds)) {
            session()->flash('error', 'Pilih minimal 1 baris mutasi bank dan 1 baris buku kas/bank untuk dicocokkan.');

            return;
        }

        $isMatchValid = $this->getIsMatchValidProperty();
        $diff = $this->getDifferenceProperty();

        if (! $isMatchValid) {
            $diffFormatted = number_format($diff, 2, ',', '.');
            session()->flash('error', "Pencocokan ditolak: Masih terdapat selisih sebesar Rp {$diffFormatted} (melebihi batas toleransi Rp 2,00).");

            return;
        }

        try {
            $service->multiMatch($this->selectedBankLineIds, $this->selectedBookLineIds, auth()->user());
            $this->clearSelection();
            $this->statement->refresh();
            session()->flash('success', 'Transaksi berhasil dicocokkan secara sempurna (Selisih Rp 0,00).');
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

    public function markAsOpeningOutstanding(int $lineId, BankReconciliationService $service): void
    {
        abort_unless(auth()->user()?->can('reconciliation.manage'), 403, 'Akses Ditolak.');

        try {
            $service->markAsOpeningOutstanding($lineId, null, auth()->user());
            $this->statement->refresh();
            session()->flash('success', 'Transaksi berhasil ditandai sebagai Cek Beredar Saldo Awal (Desember 2024). Tidak ada jurnal baru yang dibuat di tahun berjalan.');
        } catch (Exception $e) {
            session()->flash('error', 'Gagal menandai transaksi: '.$e->getMessage());
        }
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
            $bankLinesQuery->whereIn('match_status', ['matched', 'manual_matched', 'adjusted', 'opening_reconciled']);
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
        $matchedIds = DB::table('bank_journal_line_matches')->pluck('journal_line_id')
            ->merge(BankStatementLine::whereNotNull('matched_journal_line_id')->pluck('matched_journal_line_id'))
            ->unique()
            ->values()
            ->toArray();

        $periodStart = $this->statement->period_start;
        $periodEnd = $this->statement->period_end;

        // Kueri Dasar Buku Besar: Jurnal posted untuk akun ini yang berada di periode berjalan ATAU belum direkonsiliasi (lintas periode sebelum & sesudah)
        $baseBookQuery = JournalLine::with(['journalEntry', 'unit'])
            ->where('journal_lines.account_id', $this->statement->account_id)
            ->whereHas('journalEntry', function ($q) {
                $q->where('status', 'posted');
            })
            ->where(function ($q) use ($periodStart, $periodEnd, $matchedIds) {
                $q->whereHas('journalEntry', function ($sub) use ($periodStart, $periodEnd) {
                    $sub->whereBetween('entry_date', [$periodStart, $periodEnd]);
                })
                    ->orWhereNotIn('journal_lines.id', $matchedIds);
            });

        // Terapkan filter status cocok/belum cocok dan pencarian
        $filterAppliedQuery = clone $baseBookQuery;
        if ($this->bookFilter === 'unmatched') {
            $filterAppliedQuery->whereNotIn('journal_lines.id', $matchedIds);
        } elseif ($this->bookFilter === 'matched') {
            $filterAppliedQuery->whereIn('journal_lines.id', $matchedIds);
        }

        if (! empty($this->bookSearch)) {
            $bs = '%'.$this->bookSearch.'%';
            $filterAppliedQuery->where(function ($q) use ($bs) {
                $q->where('journal_lines.description', 'like', $bs)
                    ->orWhere('journal_lines.debit', 'like', $bs)
                    ->orWhere('journal_lines.credit', 'like', $bs)
                    ->orWhereHas('journalEntry', function ($sub) use ($bs) {
                        $sub->where('entry_number', 'like', $bs)
                            ->orWhere('document_number', 'like', $bs)
                            ->orWhere('description', 'like', $bs);
                    });
            });
        }

        // Hitung total untuk masing-masing tab periode
        $allCount = (clone $filterAppliedQuery)->count();
        $currentCount = (clone $filterAppliedQuery)->whereHas('journalEntry', function ($q) use ($periodStart, $periodEnd) {
            $q->whereBetween('entry_date', [$periodStart, $periodEnd]);
        })->count();
        $priorCount = (clone $filterAppliedQuery)->whereHas('journalEntry', function ($q) use ($periodStart) {
            $q->where('entry_date', '<', $periodStart);
        })->count();
        $nextCount = (clone $filterAppliedQuery)->whereHas('journalEntry', function ($q) use ($periodEnd) {
            $q->where('entry_date', '>', $periodEnd);
        })->count();

        $periodCounts = [
            'all' => $allCount,
            'current' => $currentCount,
            'prior' => $priorCount,
            'next' => $nextCount,
        ];

        // Terapkan filter periode buku besar
        $bookLinesQuery = clone $filterAppliedQuery;
        if ($this->bookPeriodFilter === 'current') {
            $bookLinesQuery->whereHas('journalEntry', function ($q) use ($periodStart, $periodEnd) {
                $q->whereBetween('entry_date', [$periodStart, $periodEnd]);
            });
        } elseif ($this->bookPeriodFilter === 'prior') {
            $bookLinesQuery->whereHas('journalEntry', function ($q) use ($periodStart) {
                $q->where('entry_date', '<', $periodStart);
            });
        } elseif ($this->bookPeriodFilter === 'next') {
            $bookLinesQuery->whereHas('journalEntry', function ($q) use ($periodEnd) {
                $q->where('entry_date', '>', $periodEnd);
            });
        }

        $bookLines = $bookLinesQuery
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->orderBy('journal_entries.entry_date', 'asc')
            ->orderBy('journal_lines.id', 'asc')
            ->select('journal_lines.*')
            ->get();

        // 3. Ringkasan Rekonsiliasi
        $summary = $service->calculateSummary($this->statement);

        // 4. Daftar Akun untuk Adjustment Modal
        $allAccounts = Account::posting()->active()->orderBy('code', 'asc')->get();

        return view('livewire.accounting.reconciliation.bank-reconciliation-detail', [
            'bankLines' => $bankLines,
            'bookLines' => $bookLines,
            'matchedIds' => $matchedIds,
            'periodCounts' => $periodCounts,
            'summary' => $summary,
            'allAccounts' => $allAccounts,
        ])->layout('layouts.app');
    }
}
