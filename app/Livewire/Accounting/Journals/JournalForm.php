<?php

namespace App\Livewire\Accounting\Journals;

use App\Domain\Accounting\Services\JournalPostingService;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalType;
use App\Models\Unit;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Buat Jurnal Umum Manual - ArtaLedger')]
class JournalForm extends Component
{
    public string $entry_date = '';

    public ?int $journal_type_id = null;

    public string $document_number = '';

    public bool $is_auto_document_number = true;

    public string $description = '';

    public array $lines = [];

    public ?int $journal_entry_id = null;

    public bool $is_edit = false;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $journal = JournalEntry::with('lines')->findOrFail($id);
            if ($journal->status === 'reversed') {
                session()->flash('error', "Jurnal {$journal->entry_number} berstatus Reversal dan tidak dapat di-edit.");
                $this->redirect(route('accounting.journals.index'), navigate: true);

                return;
            }

            $this->is_edit = true;
            $this->journal_entry_id = $journal->id;
            $this->entry_date = $journal->entry_date->format('Y-m-d');
            $this->journal_type_id = $journal->journal_type_id;
            $this->document_number = $journal->document_number ?? '';
            $this->is_auto_document_number = (bool) $journal->is_auto_document_number;
            $this->description = $journal->description ?? '';

            $this->lines = [];
            foreach ($journal->lines as $line) {
                $this->lines[] = [
                    'unit_id' => $line->unit_id ?? '',
                    'account_id' => $line->account_id,
                    'description' => $line->description ?? '',
                    'debit' => (float) $line->debit,
                    'credit' => (float) $line->credit,
                ];
            }
        } else {
            $this->entry_date = date('Y-m-d');

            // Default to JK or BM or first type
            $defaultType = JournalType::where('code', 'JK')->first() ?? JournalType::first();
            if ($defaultType) {
                $this->journal_type_id = $defaultType->id;
                $this->generateAutoDocNumber();
            }

            $this->addLine();
            $this->addLine();
        }
    }

    public function updatedJournalTypeId(): void
    {
        if (! $this->is_edit) {
            $this->generateAutoDocNumber();
        }
    }

    public function updatedEntryDate(): void
    {
        if (! $this->is_edit) {
            $this->generateAutoDocNumber();
        }
    }

    public function updatedIsAutoDocumentNumber(): void
    {
        if ($this->is_auto_document_number && ! $this->is_edit) {
            $this->generateAutoDocNumber();
        }
    }

    public function generateAutoDocNumber(): void
    {
        if ($this->is_auto_document_number && $this->journal_type_id && ! empty($this->entry_date)) {
            $service = new JournalPostingService;
            $this->document_number = $service->generateDocumentNumber(
                $this->journal_type_id,
                Carbon::parse($this->entry_date)
            );
        }
    }

    public function addLine(): void
    {
        $this->lines[] = [
            'unit_id' => '',
            'account_id' => '',
            'description' => '',
            'debit' => 0,
            'credit' => 0,
        ];
    }

    public function removeLine(int $index): void
    {
        if (count($this->lines) > 2) {
            unset($this->lines[$index]);
            $this->lines = array_values($this->lines);
        }
    }

    public function getTotalDebitProperty(): float
    {
        return array_reduce($this->lines, fn ($sum, $l) => $sum + (float) ($l['debit'] ?? 0), 0.0);
    }

    public function getTotalCreditProperty(): float
    {
        return array_reduce($this->lines, fn ($sum, $l) => $sum + (float) ($l['credit'] ?? 0), 0.0);
    }

    public function getDifferenceProperty(): float
    {
        return abs($this->totalDebit - $this->totalCredit);
    }

    public function getIsBalancedProperty(): bool
    {
        return $this->difference < 0.01 && $this->totalDebit > 0;
    }

    public function saveJournal(): void
    {
        $this->validate([
            'entry_date' => 'required|date',
            'journal_type_id' => 'required|exists:journal_types,id',
            'description' => 'required|string|max:255',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.unit_id' => 'nullable|exists:units,id',
            'lines.*.debit' => 'numeric|min:0',
            'lines.*.credit' => 'numeric|min:0',
        ]);

        if (! $this->isBalanced) {
            session()->flash('error', 'Gagal menyimpan! Jurnal belum seimbang (Unbalanced). Selisih Debit & Kredit: Rp '.number_format($this->difference, 2, ',', '.'));

            return;
        }

        try {
            $service = new JournalPostingService;

            if ($this->is_edit && $this->journal_entry_id) {
                $journal = JournalEntry::findOrFail($this->journal_entry_id);
                $service->updateManualEntry(
                    $journal,
                    [
                        'entry_date' => $this->entry_date,
                        'journal_type_id' => $this->journal_type_id,
                        'document_number' => $this->document_number,
                        'description' => $this->description,
                    ],
                    $this->lines,
                    auth()->id()
                );
                session()->flash('message', "Jurnal {$journal->entry_number} berhasil diperbarui!");
            } else {
                $journal = $service->postManualEntry(
                    [
                        'entry_date' => $this->entry_date,
                        'journal_type_id' => $this->journal_type_id,
                        'document_number' => $this->document_number,
                        'is_auto_document_number' => $this->is_auto_document_number,
                        'description' => $this->description,
                    ],
                    $this->lines,
                    auth()->id()
                );
                session()->flash('message', "Jurnal {$journal->entry_number} berhasil diposting ke General Ledger!");
            }

            $this->redirect(route('accounting.journals.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        // Only postable accounts (is_group = false)
        $accounts = Account::posting()->active()->orderBy('code', 'asc')->get();
        $journalTypes = JournalType::orderBy('code')->get();
        $units = Unit::all();

        return view('livewire.accounting.journals.form', [
            'accounts' => $accounts,
            'journalTypes' => $journalTypes,
            'units' => $units,
        ]);
    }
}
