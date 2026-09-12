<?php

namespace App\Livewire\Accounting\Reports;

use App\Domain\Accounting\Services\AgingInvoiceManagerService;
use App\Domain\Accounting\Services\AgingReportService;
use App\Livewire\Concerns\SyncsGlobalPeriod;
use App\Models\Account;
use App\Models\ApArInvoice;
use App\Models\ApArSettlement;
use App\Models\JournalLine;
use App\Models\Unit;
use Exception;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan Aging Hutang & Piutang')]
class AgingReport extends Component
{
    use SyncsGlobalPeriod;

    public string $activeTab = 'receivable'; // 'receivable' | 'payable'

    public string $asOfDate = '';

    public string $unitFilter = 'all';

    public bool $hideZeroBalances = true;

    // Filter baris pelunasan existing
    public string $settleDateFilterStart = '';

    public string $settleDateFilterEnd = '';

    public string $settleSearchQuery = '';

    public bool $hideFullyAllocatedPayments = true;

    // Edit Invoice Modal State
    public bool $showEditModal = false;

    public ?int $editingInvoiceId = null;

    public string $editInvoiceNumber = '';

    public string $editInvoiceDate = '';

    public string $editDueDate = '';

    public string $editPartnerName = '';

    public string $editNotes = '';

    public float $editOriginalAmount = 0.0;

    public float $editSettledAmount = 0.0;

    public bool $editHasSettlement = false;

    // Settlement Modal State
    public bool $showSettleModal = false;

    public ?int $settlingInvoiceId = null;

    public ?ApArInvoice $settlingInvoice = null;

    public string $settleMode = 'quick'; // 'quick' | 'existing'

    public float $settleAmount = 0.0;

    public ?int $settleCashAccountId = null;

    public string $settleDate = '';

    public string $settleNotes = '';

    public ?int $settleExistingLineId = null;

    // Assign / Split Modal State
    /** @var array<int, bool> */
    public array $expandedAccounts = [];

    // State Modal Penugasan / Pemecahan Invoice
    public bool $showAssignModal = false;

    public ?int $selectedJournalLineId = null;

    public string $modalMode = 'single'; // 'single' | 'split'

    public ?JournalLine $selectedJournalLine = null;

    // Single Assign Form
    public string $singleInvoiceNumber = '';

    public string $singleInvoiceDate = '';

    public string $singleDueDate = '';

    public string $singlePartnerName = '';

    public string $singleNotes = '';

    // Split Assign Form
    /** @var array<int, array<string, mixed>> */
    public array $splitRows = [];

    // Multi-Journal Consolidation State
    /** @var array<int, bool> */
    public array $selectedLineIds = [];

    public string $mergeInvoiceNumber = '';

    public string $mergeInvoiceDate = '';

    public string $mergeDueDate = '';

    public string $mergePartnerName = '';

    public string $mergeNotes = '';

    public float $mergeTotalAmount = 0.0;

    // Multi-Journal Sub-Mode: 'single' (1 Invoice Gabungan) | 'multiple' (Banyak Invoice)
    public string $mergeSubMode = 'single';

    /** @var array<int, array<string, mixed>> */
    public array $mergeInvoiceRows = [];

    public function mount(): void
    {
        if (auth()->check() && ! auth()->user()->can('reports.view')) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Laporan Keuangan.');
        }

        $this->initializeGlobalPeriod();
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['receivable', 'payable'])) {
            $this->activeTab = $tab;
            $this->expandedAccounts = [];
        }
    }

    public function toggleAccount(int $accountId): void
    {
        if (isset($this->expandedAccounts[$accountId])) {
            unset($this->expandedAccounts[$accountId]);
        } else {
            $this->expandedAccounts[$accountId] = true;
        }
    }

    public function openAssignModal(int $journalLineId): void
    {
        $this->selectedJournalLineId = $journalLineId;
        $this->selectedJournalLine = JournalLine::with(['journalEntry', 'account', 'unit'])->find($journalLineId);

        if (! $this->selectedJournalLine) {
            return;
        }

        $lineAmount = $this->activeTab === 'receivable'
            ? (float) $this->selectedJournalLine->debit
            : (float) $this->selectedJournalLine->credit;

        $entryDateStr = $this->selectedJournalLine->journalEntry?->entry_date->format('Y-m-d') ?? date('Y-m-d');

        $this->modalMode = 'single';
        $this->singleInvoiceNumber = '';
        $this->singleInvoiceDate = $entryDateStr;
        $this->singleDueDate = $entryDateStr;
        $this->singlePartnerName = '';
        $this->singleNotes = $this->selectedJournalLine->description ?? '';

        // Siapkan default split row
        $this->splitRows = [
            [
                'invoice_number' => '',
                'invoice_date' => $entryDateStr,
                'due_date' => $entryDateStr,
                'original_amount' => $lineAmount / 2,
                'partner_name' => '',
                'notes' => '',
            ],
            [
                'invoice_number' => '',
                'invoice_date' => $entryDateStr,
                'due_date' => $entryDateStr,
                'original_amount' => $lineAmount / 2,
                'partner_name' => '',
                'notes' => '',
            ],
        ];

        $this->showAssignModal = true;
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
        $this->selectedJournalLineId = null;
        $this->selectedJournalLine = null;
    }

    public function openEditModal(int $invoiceId): void
    {
        $invoice = ApArInvoice::with('settlements')->find($invoiceId);
        if (! $invoice) {
            session()->flash('error', 'Invoice tidak ditemukan.');

            return;
        }

        $this->editingInvoiceId = $invoice->id;
        $this->editInvoiceNumber = $invoice->invoice_number;
        $this->editInvoiceDate = $invoice->invoice_date?->format('Y-m-d') ?? date('Y-m-d');
        $this->editDueDate = $invoice->due_date?->format('Y-m-d') ?? date('Y-m-d');
        $this->editPartnerName = $invoice->partner_name ?? '';
        $this->editNotes = $invoice->notes ?? '';
        $this->editOriginalAmount = (float) $invoice->original_amount;
        $this->editSettledAmount = (float) $invoice->settlements->sum('settled_amount');
        $this->editHasSettlement = $this->editSettledAmount > 0.01;

        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingInvoiceId = null;
        $this->editInvoiceNumber = '';
        $this->editInvoiceDate = '';
        $this->editDueDate = '';
        $this->editPartnerName = '';
        $this->editNotes = '';
        $this->editOriginalAmount = 0.0;
        $this->editSettledAmount = 0.0;
        $this->editHasSettlement = false;
    }

    public function saveUpdatedInvoice(): void
    {
        $this->validate([
            'editInvoiceNumber' => 'required|string|max:100',
            'editInvoiceDate' => 'required|date',
            'editDueDate' => 'required|date',
            'editPartnerName' => 'nullable|string|max:255',
            'editNotes' => 'nullable|string|max:500',
            'editOriginalAmount' => 'required|numeric|min:0.01',
        ], [
            'editInvoiceNumber.required' => 'Nomor invoice wajib diisi.',
            'editInvoiceDate.required' => 'Tanggal invoice wajib diisi.',
            'editDueDate.required' => 'Tanggal jatuh tempo wajib diisi.',
            'editOriginalAmount.required' => 'Nominal invoice wajib diisi.',
            'editOriginalAmount.min' => 'Nominal invoice harus lebih besar dari 0.',
        ]);

        if (! $this->editingInvoiceId) {
            return;
        }

        $invoice = ApArInvoice::find($this->editingInvoiceId);
        if (! $invoice) {
            session()->flash('error', 'Data invoice tidak ditemukan.');

            return;
        }

        try {
            $service = new AgingInvoiceManagerService;
            $service->updateInvoice($invoice, [
                'invoice_number' => $this->editInvoiceNumber,
                'invoice_date' => $this->editInvoiceDate,
                'due_date' => $this->editDueDate,
                'partner_name' => $this->editPartnerName,
                'notes' => $this->editNotes,
                'original_amount' => $this->editOriginalAmount,
            ], auth()->id());

            session()->flash('message', "Invoice {$this->editInvoiceNumber} berhasil diperbarui!");
            $this->closeEditModal();
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function unlinkInvoice(int $invoiceId): void
    {
        $invoice = ApArInvoice::find($invoiceId);
        if (! $invoice) {
            session()->flash('error', 'Invoice tidak ditemukan.');

            return;
        }

        try {
            $service = new AgingInvoiceManagerService;
            $invoiceNumber = $invoice->invoice_number;
            $service->unlinkInvoice($invoice, auth()->id());

            session()->flash('message', "Penugasan Invoice {$invoiceNumber} berhasil dibatalkan. Baris jurnal kembali ke status belum terdaftar.");
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function openSettleModal(int $invoiceId): void
    {
        $invoice = ApArInvoice::with(['settlements.paymentJournalLine.journalEntry', 'account'])->find($invoiceId);
        if (! $invoice) {
            session()->flash('error', 'Invoice tidak ditemukan.');

            return;
        }

        $this->settlingInvoiceId = $invoice->id;
        $this->settlingInvoice = $invoice;
        $this->settleRemainingBalance = (float) $invoice->remaining_amount;
        $this->settleAmount = $this->settleRemainingBalance;
        $this->settleDate = date('Y-m-d');
        $this->settleNotes = "Pelunasan Invoice {$invoice->invoice_number}";
        $this->settleMode = 'quick';
        $this->settlePaymentLineId = null;

        // Cari akun kas/bank default
        $defaultCash = Account::where('is_group', false)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('type', 'KAS')
                    ->orWhere('type', 'BANK')
                    ->orWhere('code', 'like', '11%');
            })
            ->orderBy('code')
            ->first();

        $this->settleCashAccountId = $defaultCash?->id;
        $this->settleSearchQuery = '';
        $this->settleDateFilterStart = '';
        $this->settleDateFilterEnd = '';
        $this->hideFullyAllocatedPayments = true;
        $this->showSettleModal = true;
    }

    public function closeSettleModal(): void
    {
        $this->showSettleModal = false;
        $this->settlingInvoiceId = null;
        $this->settlingInvoice = null;
        $this->settleAmount = 0.0;
        $this->settleDate = '';
        $this->settleCashAccountId = null;
        $this->settlePaymentLineId = null;
        $this->settleNotes = '';
        $this->settleSearchQuery = '';
        $this->settleDateFilterStart = '';
        $this->settleDateFilterEnd = '';
    }

    public function selectPaymentLine(int $lineId, float $availableAmount): void
    {
        $this->settlePaymentLineId = $lineId;

        // Otomatis isi nominal yang dialokasikan: nilai terkecil antara sisa tagihan invoice dengan sisa kapasitas jurnal
        $targetAmount = min($this->settleRemainingBalance, $availableAmount);
        if ($targetAmount > 0) {
            $this->settleAmount = $targetAmount;
        }
    }

    public function setFullSettleAmount(): void
    {
        if ($this->settlingInvoice) {
            $this->settleAmount = (float) $this->settlingInvoice->remaining_amount;
        }
    }

    public function saveSettlement(): void
    {
        if (! $this->settlingInvoiceId) {
            return;
        }

        $invoice = ApArInvoice::find($this->settlingInvoiceId);
        if (! $invoice) {
            session()->flash('error', 'Invoice tidak ditemukan.');

            return;
        }

        $service = new AgingInvoiceManagerService;

        if ($this->settleMode === 'quick') {
            $this->validate([
                'settleAmount' => 'required|numeric|min:0.01',
                'settleDate' => 'required|date',
                'settleCashAccountId' => 'required|exists:accounts,id',
                'settleNotes' => 'nullable|string|max:255',
            ], [
                'settleAmount.required' => 'Nominal pelunasan wajib diisi.',
                'settleAmount.min' => 'Nominal pelunasan harus lebih besar dari 0.',
                'settleDate.required' => 'Tanggal pembayaran wajib diisi.',
                'settleCashAccountId.required' => 'Pilih akun Kas / Bank.',
            ]);

            try {
                $service->quickSettleInvoice($invoice, [
                    'amount' => $this->settleAmount,
                    'payment_date' => $this->settleDate,
                    'cash_account_id' => $this->settleCashAccountId,
                    'notes' => $this->settleNotes,
                ], auth()->id());

                session()->flash('message', "Pelunasan Invoice {$invoice->invoice_number} sebesar Rp ".number_format($this->settleAmount, 2, ',', '.').' berhasil dicatat!');
                $this->closeSettleModal();
            } catch (Exception $e) {
                session()->flash('error', $e->getMessage());
            }
        } else {
            // Mode Existing Payment Line
            $this->validate([
                'settlePaymentLineId' => 'required|exists:journal_lines,id',
                'settleAmount' => 'required|numeric|min:0.01',
            ], [
                'settlePaymentLineId.required' => 'Pilih baris jurnal pembayaran yang akan ditautkan.',
                'settleAmount.required' => 'Nominal pelunasan wajib diisi.',
            ]);

            try {
                $paymentLine = JournalLine::find($this->settlePaymentLineId);
                $service->settleInvoice($invoice, $paymentLine, $this->settleAmount, auth()->id());

                session()->flash('message', "Berhasil menautkan pelunasan Invoice {$invoice->invoice_number}!");
                $this->closeSettleModal();
            } catch (Exception $e) {
                session()->flash('error', $e->getMessage());
            }
        }
    }

    public function deleteSettlement(int $settlementId): void
    {
        $settlement = ApArSettlement::find($settlementId);
        if (! $settlement) {
            session()->flash('error', 'Data pelunasan tidak ditemukan.');

            return;
        }

        try {
            $service = new AgingInvoiceManagerService;
            $service->cancelSettlement($settlement, auth()->id());

            session()->flash('message', 'Pelunasan berhasil dibatalkan dan saldo tagihan dikembalikan!');

            if ($this->settlingInvoiceId) {
                $this->settlingInvoice = ApArInvoice::with(['settlements.paymentJournalLine.journalEntry', 'account'])->find($this->settlingInvoiceId);
                $this->settleRemainingBalance = (float) $this->settlingInvoice->remaining_amount;
            }
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function addSplitRow(): void
    {
        $entryDateStr = $this->selectedJournalLine?->journalEntry?->entry_date->format('Y-m-d') ?? date('Y-m-d');
        $this->splitRows[] = [
            'invoice_number' => '',
            'invoice_date' => $entryDateStr,
            'due_date' => $entryDateStr,
            'original_amount' => 0.0,
            'partner_name' => '',
            'notes' => '',
        ];
    }

    public function removeSplitRow(int $index): void
    {
        if (count($this->splitRows) > 1) {
            unset($this->splitRows[$index]);
            $this->splitRows = array_values($this->splitRows);
        }
    }

    public function saveSingleInvoice(): void
    {
        $this->validate([
            'singleInvoiceNumber' => 'required|string|max:100',
            'singleInvoiceDate' => 'required|date',
            'singleDueDate' => 'required|date',
            'singlePartnerName' => 'nullable|string|max:255',
            'singleNotes' => 'nullable|string|max:500',
        ]);

        if (! $this->selectedJournalLine) {
            return;
        }

        try {
            $service = new AgingInvoiceManagerService;
            $service->assignSingleInvoice($this->selectedJournalLine, [
                'invoice_number' => $this->singleInvoiceNumber,
                'invoice_date' => $this->singleInvoiceDate,
                'due_date' => $this->singleDueDate,
                'partner_name' => $this->singlePartnerName,
                'notes' => $this->singleNotes,
            ], auth()->id());

            session()->flash('message', "Invoice {$this->singleInvoiceNumber} berhasil ditugaskan!");
            $this->closeAssignModal();
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function saveSplitInvoices(): void
    {
        $this->validate([
            'splitRows.*.invoice_number' => 'required|string|max:100',
            'splitRows.*.invoice_date' => 'required|date',
            'splitRows.*.due_date' => 'required|date',
            'splitRows.*.original_amount' => 'required|numeric|min:1',
            'splitRows.*.partner_name' => 'nullable|string|max:255',
        ]);

        if (! $this->selectedJournalLine) {
            return;
        }

        try {
            $service = new AgingInvoiceManagerService;
            $service->splitJournalLineIntoInvoices($this->selectedJournalLine, $this->splitRows, auth()->id());

            session()->flash('message', 'Baris jurnal berhasil dipecah menjadi '.count($this->splitRows).' invoice!');
            $this->closeAssignModal();
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function openMergeModal(): void
    {
        $activeLineIds = array_keys(array_filter($this->selectedLineIds));
        if (count($activeLineIds) < 2) {
            session()->flash('error', 'Pilih minimal 2 baris jurnal untuk digabungkan menjadi 1 invoice.');

            return;
        }

        $lines = JournalLine::whereIn('id', $activeLineIds)->get();
        $this->mergeTotalAmount = $this->activeTab === 'receivable'
            ? (float) $lines->sum('debit')
            : (float) $lines->sum('credit');

        $this->modalMode = 'merge';
        $this->mergeSubMode = 'single';
        $this->mergeInvoiceNumber = '';
        $this->mergeInvoiceDate = date('Y-m-d');
        $this->mergeDueDate = date('Y-m-d');
        $this->mergePartnerName = '';
        $this->mergeNotes = '';
        $this->selectedJournalLine = JournalLine::with('account')->find($activeLineIds[0]);

        $this->mergeInvoiceRows = [
            [
                'invoice_number' => '',
                'original_amount' => round($this->mergeTotalAmount / 2, 2),
                'invoice_date' => date('Y-m-d'),
                'due_date' => date('Y-m-d'),
                'partner_name' => '',
                'notes' => '',
            ],
            [
                'invoice_number' => '',
                'original_amount' => round($this->mergeTotalAmount - round($this->mergeTotalAmount / 2, 2), 2),
                'invoice_date' => date('Y-m-d'),
                'due_date' => date('Y-m-d'),
                'partner_name' => '',
                'notes' => '',
            ],
        ];

        $this->showAssignModal = true;
    }

    public function setMergeSubMode(string $mode): void
    {
        if (in_array($mode, ['single', 'multiple'])) {
            $this->mergeSubMode = $mode;
        }
    }

    public function addMergeInvoiceRow(): void
    {
        $currentSum = (float) array_sum(array_map(fn ($r) => (float) ($r['original_amount'] ?? 0), $this->mergeInvoiceRows));
        $remainder = max(0, $this->mergeTotalAmount - $currentSum);

        $this->mergeInvoiceRows[] = [
            'invoice_number' => '',
            'original_amount' => round($remainder, 2),
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d'),
            'partner_name' => '',
            'notes' => '',
        ];
    }

    public function removeMergeInvoiceRow(int $index): void
    {
        if (count($this->mergeInvoiceRows) > 1) {
            unset($this->mergeInvoiceRows[$index]);
            $this->mergeInvoiceRows = array_values($this->mergeInvoiceRows);
        }
    }

    public function saveMergedInvoice(): void
    {
        $activeLineIds = array_keys(array_filter($this->selectedLineIds));
        $service = new AgingInvoiceManagerService;

        if ($this->mergeSubMode === 'single') {
            $this->validate([
                'mergeInvoiceNumber' => 'required|string|max:100',
                'mergeInvoiceDate' => 'required|date',
                'mergeDueDate' => 'required|date',
                'mergePartnerName' => 'nullable|string|max:255',
                'mergeNotes' => 'nullable|string|max:500',
            ]);

            try {
                $invoice = $service->consolidateJournalLinesIntoInvoice($activeLineIds, [
                    'invoice_number' => $this->mergeInvoiceNumber,
                    'invoice_date' => $this->mergeInvoiceDate,
                    'due_date' => $this->mergeDueDate,
                    'partner_name' => $this->mergePartnerName,
                    'notes' => $this->mergeNotes,
                ], auth()->id());

                session()->flash('message', 'Berhasil menggabungkan '.count($activeLineIds)." baris jurnal menjadi Invoice {$invoice->invoice_number}!");
                $this->selectedLineIds = [];
                $this->closeAssignModal();
            } catch (Exception $e) {
                session()->flash('error', $e->getMessage());
            }
        } else {
            // Multiple Invoices Sub-Mode
            $this->validate([
                'mergeInvoiceRows' => 'required|array|min:1',
                'mergeInvoiceRows.*.invoice_number' => 'required|string|max:100',
                'mergeInvoiceRows.*.original_amount' => 'required|numeric|min:0.01',
                'mergeInvoiceRows.*.due_date' => 'required|date',
                'mergeInvoiceRows.*.partner_name' => 'nullable|string|max:255',
                'mergeInvoiceRows.*.notes' => 'nullable|string|max:500',
            ], [
                'mergeInvoiceRows.*.invoice_number.required' => 'Nomor invoice wajib diisi pada setiap baris.',
                'mergeInvoiceRows.*.original_amount.required' => 'Nominal invoice wajib diisi.',
                'mergeInvoiceRows.*.original_amount.min' => 'Nominal invoice harus lebih besar dari 0.',
                'mergeInvoiceRows.*.due_date.required' => 'Tanggal jatuh tempo wajib diisi.',
            ]);

            $totalInvoices = (float) array_sum(array_map(fn ($r) => (float) ($r['original_amount'] ?? 0), $this->mergeInvoiceRows));
            if (abs($totalInvoices - $this->mergeTotalAmount) > 0.01) {
                session()->flash('error', 'Total invoice (Rp '.number_format($totalInvoices, 2, ',', '.').') harus sama dengan total akumulasi jurnal (Rp '.number_format($this->mergeTotalAmount, 2, ',', '.').').');

                return;
            }

            try {
                $invoices = $service->consolidateJournalLinesIntoMultipleInvoices($activeLineIds, $this->mergeInvoiceRows, auth()->id());

                session()->flash('message', 'Berhasil menggabungkan '.count($activeLineIds).' baris jurnal menjadi '.count($invoices).' lembar invoice!');
                $this->selectedLineIds = [];
                $this->closeAssignModal();
            } catch (Exception $e) {
                session()->flash('error', $e->getMessage());
            }
        }
    }

    public function render()
    {
        $reportService = new AgingReportService;
        $reportData = $reportService->getAgingReport(
            type: $this->activeTab,
            asOfDate: $this->asOfDate ?: date('Y-m-d'),
            unitFilter: $this->unitFilter,
            hideZeroBalances: $this->hideZeroBalances
        );

        $units = Unit::orderBy('code')->get();

        $cashAccounts = Account::where('is_group', false)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('type', 'KAS')
                    ->orWhere('type', 'BANK')
                    ->orWhere('code', 'like', '11%');
            })
            ->orderBy('code')
            ->get();

        // Cari kandidat baris jurnal pembayaran (misal jika mode existing dipilih)
        $availablePaymentLines = collect();
        if ($this->showSettleModal && $this->settlingInvoiceId) {
            $invoice = $this->settlingInvoice ?: ApArInvoice::find($this->settlingInvoiceId);
            $oppColumn = $this->activeTab === 'receivable' ? 'credit' : 'debit';
            $query = JournalLine::with(['journalEntry', 'apArSettlements'])
                ->where('account_id', $invoice->account_id)
                ->where($oppColumn, '>', 0)
                ->whereHas('journalEntry', function ($q) {
                    $q->where('status', 'posted');

                    if ($this->settleDateFilterStart !== '') {
                        $q->where('entry_date', '>=', $this->settleDateFilterStart);
                    }
                    if ($this->settleDateFilterEnd !== '') {
                        $q->where('entry_date', '<=', $this->settleDateFilterEnd);
                    }
                });

            // Filter Pencarian Teks (No Jurnal, No Dokumen/Referensi, atau Keterangan)
            if (trim($this->settleSearchQuery) !== '') {
                $search = trim($this->settleSearchQuery);
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhereHas('journalEntry', function ($sub) use ($search) {
                            $sub->where('entry_number', 'like', "%{$search}%")
                                ->orWhere('document_number', 'like', "%{$search}%")
                                ->orWhere('description', 'like', "%{$search}%");
                        });
                });
            }

            $rawLines = $query->latest('id')->limit(80)->get();

            $availablePaymentLines = $rawLines->map(function ($line) use ($oppColumn) {
                $totalAmount = (float) $line->{$oppColumn};
                $allocatedAmount = (float) $line->apArSettlements->sum('settled_amount');
                $availableAmount = max(0.0, $totalAmount - $allocatedAmount);

                return [
                    'id' => $line->id,
                    'entry_number' => $line->journalEntry?->entry_number ?: '-',
                    'document_number' => $line->journalEntry?->document_number ?: null,
                    'entry_date' => $line->journalEntry?->entry_date?->format('d/m/Y') ?: '-',
                    'description' => $line->description ?: ($line->journalEntry?->description ?: '-'),
                    'total_amount' => $totalAmount,
                    'allocated_amount' => $allocatedAmount,
                    'available_amount' => $availableAmount,
                    'is_fully_allocated' => $availableAmount <= 0.001,
                ];
            });

            if ($this->hideFullyAllocatedPayments) {
                $availablePaymentLines = $availablePaymentLines->filter(fn ($item) => ! $item['is_fully_allocated'])->values();
            }
        }

        return view('livewire.accounting.reports.aging-report', [
            'report' => $reportData,
            'units' => $units,
            'cashAccounts' => $cashAccounts,
            'availablePaymentLines' => $availablePaymentLines,
        ]);
    }
}
