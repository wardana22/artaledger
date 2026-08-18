<?php

namespace App\Livewire\Accounting\Journals;

use App\Domain\Accounting\Services\JournalPostingService;
use App\Models\Account;
use App\Models\Company;
use App\Models\JournalType;
use Carbon\Carbon;
use Exception;
use Livewire\Component;

class AdjustmentForm extends Component
{
    public string $entry_date = '';

    public ?int $journal_type_id = null;

    public string $document_number = '';

    public bool $is_auto_document_number = true;

    public string $description = '';

    public array $lines = [];

    public function mount(): void
    {
        $this->entry_date = now()->format('Y-m-d');

        // Default to JPEN or first type
        $defaultType = JournalType::where('code', 'JPEN')->first() ?? JournalType::first();
        if ($defaultType) {
            $this->journal_type_id = $defaultType->id;
            $this->generateAutoDocNumber();
        }

        $this->lines = [
            ['account_id' => '', 'description' => '', 'debit' => 0.0, 'credit' => 0.0],
            ['account_id' => '', 'description' => '', 'debit' => 0.0, 'credit' => 0.0],
        ];
    }

    public function updatedJournalTypeId(): void
    {
        $this->generateAutoDocNumber();
    }

    public function updatedEntryDate(): void
    {
        $this->generateAutoDocNumber();
    }

    public function updatedIsAutoDocumentNumber(): void
    {
        if ($this->is_auto_document_number) {
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
            'account_id' => '',
            'description' => '',
            'debit' => 0.0,
            'credit' => 0.0,
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

    public function saveAdjustment(JournalPostingService $postingService)
    {
        $this->validate([
            'entry_date' => 'required|date',
            'journal_type_id' => 'required|exists:journal_types,id',
            'description' => 'required|string|max:255',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.debit' => 'numeric|min:0',
            'lines.*.credit' => 'numeric|min:0',
        ]);

        if (! $this->isBalanced) {
            session()->flash('error', 'Gagal menyimpan! Jurnal penyesuaian belum seimbang (Unbalanced). Selisih Debit & Kredit: Rp '.number_format($this->difference, 2, ',', '.'));

            return;
        }

        try {
            $companyId = Company::first()?->id ?? 1;

            $entryData = [
                'company_id' => $companyId,
                'entry_date' => $this->entry_date,
                'journal_type_id' => $this->journal_type_id,
                'document_number' => $this->document_number ?: null,
                'is_auto_document_number' => $this->is_auto_document_number,
                'description' => $this->description,
            ];

            $journal = $postingService->postManualEntry($entryData, $this->lines, auth()->id(), 'adjustment');

            session()->flash('message', "Jurnal Penyesuaian {$journal->entry_number} berhasil diposting!");

            return $this->redirect(route('accounting.adjustments.index'), navigate: true);
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $companyId = Company::first()?->id ?? 1;

        $accounts = Account::where('company_id', $companyId)
            ->where('is_group', false)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $journalTypes = JournalType::orderBy('code')->get();

        return view('livewire.accounting.journals.adjustment-form', [
            'accounts' => $accounts,
            'journalTypes' => $journalTypes,
        ])->layout('layouts.app');
    }
}
