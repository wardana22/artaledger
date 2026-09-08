<?php

namespace App\Livewire\Accounting\Reports;

use App\Domain\Accounting\Services\AgingInvoiceManagerService;
use App\Domain\Accounting\Services\AgingReportService;
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
    public string $activeTab = 'receivable'; // 'receivable' | 'payable'

    public string $asOfDate = '';

    public string $unitFilter = 'all';

    public bool $hideZeroBalances = true;

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

        $this->asOfDate = date('Y-m-d');
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

        return view('livewire.accounting.reports.aging-report', [
            'report' => $reportData,
            'units' => $units,
        ]);
    }
}
