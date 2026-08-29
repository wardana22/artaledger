<?php

namespace App\Domain\Accounting\Services;

use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\JournalType;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class JournalPostingService
{
    /**
     * Post a new manual journal entry with lines transactionally.
     *
     * @param  array  $entryData  ['entry_date', 'document_number', 'description', 'company_id']
     * @param  array  $linesData  [['account_id', 'description', 'debit', 'credit'], ...]
     *
     * @throws Exception
     */
    public function postManualEntry(array $entryData, array $linesData, ?int $userId = null, string $entryType = 'general'): JournalEntry
    {
        $companyId = $entryData['company_id'] ?? Company::first()?->id ?? 1;
        $entryDate = Carbon::parse($entryData['entry_date']);

        // 1. Check Accounting Period Status
        $period = AccountingPeriod::where('company_id', $companyId)
            ->where('year', $entryDate->year)
            ->where('month', $entryDate->month)
            ->first();

        if (! $period) {
            // Auto create open period if missing for dev/demo flexibility
            $period = AccountingPeriod::create([
                'company_id' => $companyId,
                'year' => $entryDate->year,
                'month' => $entryDate->month,
                'start_date' => $entryDate->copy()->startOfMonth(),
                'end_date' => $entryDate->copy()->endOfMonth(),
                'status' => 'open',
            ]);
        }

        if (! $period->isOpen()) {
            throw new Exception("Gagal posting! Periode akuntansi ({$entryDate->format('F Y')}) berstatus '{$period->status}'. Posting hanya diizinkan pada periode 'open'.");
        }

        // 2. Validate Lines & Balance
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        if (count($linesData) < 2) {
            throw new Exception('Jurnal harus memiliki minimal 2 baris transaksi.');
        }

        foreach ($linesData as $index => $line) {
            $account = Account::find($line['account_id']);
            if (! $account) {
                throw new Exception('Baris #'.($index + 1).": Akun ID {$line['account_id']} tidak ditemukan.");
            }

            if ($account->is_group) {
                throw new Exception('Baris #'.($index + 1).": Akun '{$account->code} - {$account->name}' adalah Header Group dan tidak dapat diposting.");
            }

            if (! $account->is_active) {
                throw new Exception('Baris #'.($index + 1).": Akun '{$account->code} - {$account->name}' berstatus tidak aktif.");
            }

            $debit = (float) ($line['debit'] ?? 0);
            $credit = (float) ($line['credit'] ?? 0);

            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        if (abs($totalDebit - $totalCredit) >= 0.01) {
            throw new Exception('Jurnal tidak seimbang (Unbalanced)! Total Debit ('.number_format($totalDebit, 2, ',', '.').') != Total Kredit ('.number_format($totalCredit, 2, ',', '.').').');
        }

        // 3. Generate Entry Number & Post DB Transaction
        return DB::transaction(function () use ($companyId, $period, $entryDate, $entryData, $linesData, $userId, $entryType) {
            $entryNumber = $this->generateEntryNumber($companyId, $entryDate, $entryType);
            $journalTypeId = $entryData['journal_type_id'] ?? null;

            // Auto-generate document number if empty or requested
            $documentNumber = $entryData['document_number'] ?? null;
            $isAutoDoc = (bool) ($entryData['is_auto_document_number'] ?? true);

            if ($isAutoDoc && $journalTypeId && empty($documentNumber)) {
                $documentNumber = $this->generateDocumentNumber($journalTypeId, $entryDate, $companyId);
            }

            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'period_id' => $period->id,
                'entry_number' => $entryNumber,
                'entry_date' => $entryDate->format('Y-m-d'),
                'document_number' => $documentNumber,
                'is_auto_document_number' => $isAutoDoc,
                'journal_type_id' => $journalTypeId,
                'description' => $entryData['description'] ?? null,
                'source_type' => 'manual',
                'entry_type' => $entryType,
                'status' => 'posted',
                'posted_by' => $userId,
                'posted_at' => now(),
            ]);

            foreach ($linesData as $index => $line) {
                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'line_no' => $index + 1,
                    'account_id' => $line['account_id'],
                    'unit_id' => ! empty($line['unit_id']) ? $line['unit_id'] : null,
                    'description' => $line['description'] ?? $entryData['description'] ?? null,
                    'debit' => (float) ($line['debit'] ?? 0),
                    'credit' => (float) ($line['credit'] ?? 0),
                ]);
            }

            return $journalEntry;
        });
    }

    /**
     * Update an existing manual journal entry and sync lines transactionally.
     */
    public function updateManualEntry(JournalEntry $journalEntry, array $entryData, array $linesData, ?int $userId = null): JournalEntry
    {
        if ($journalEntry->status === 'posted') {
            throw new Exception("Akses Ditolak! Jurnal '{$journalEntry->entry_number}' berstatus POSTED dan telah terkunci demi keamanan audit. Edit tidak diizinkan. Koreksi transaksi hanya dapat dilakukan melalui Jurnal Pembalik (Reversal).");
        }

        if ($journalEntry->status === 'reversed') {
            throw new Exception("Jurnal Reversal ({$journalEntry->entry_number}) berstatus terkunci dan tidak dapat di-edit.");
        }

        $companyId = $entryData['company_id'] ?? $journalEntry->company_id;
        $entryDate = Carbon::parse($entryData['entry_date']);

        // 1. Check Accounting Period Status
        $period = AccountingPeriod::where('company_id', $companyId)
            ->where('year', $entryDate->year)
            ->where('month', $entryDate->month)
            ->first();

        if ($period && ! $period->isOpen()) {
            throw new Exception("Gagal update! Periode akuntansi ({$entryDate->format('F Y')}) berstatus '{$period->status}'. Edit hanya diizinkan pada periode 'open'.");
        }

        // 2. Validate Lines & Balance
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        if (count($linesData) < 2) {
            throw new Exception('Jurnal harus memiliki minimal 2 baris transaksi.');
        }

        foreach ($linesData as $index => $line) {
            $account = Account::find($line['account_id']);
            if (! $account) {
                throw new Exception('Baris #'.($index + 1).": Akun ID {$line['account_id']} tidak ditemukan.");
            }
            if ($account->is_group) {
                throw new Exception('Baris #'.($index + 1).": Akun '{$account->code} - {$account->name}' adalah Header Group.");
            }
            if (! $account->is_active) {
                throw new Exception('Baris #'.($index + 1).": Akun '{$account->code} - {$account->name}' tidak aktif.");
            }
            $totalDebit += (float) ($line['debit'] ?? 0);
            $totalCredit += (float) ($line['credit'] ?? 0);
        }

        if (abs($totalDebit - $totalCredit) >= 0.01) {
            throw new Exception('Jurnal tidak seimbang (Unbalanced)! Total Debit ('.number_format($totalDebit, 2, ',', '.').') != Total Kredit ('.number_format($totalCredit, 2, ',', '.').').');
        }

        return DB::transaction(function () use ($journalEntry, $entryDate, $entryData, $linesData) {
            $journalEntry->update([
                'entry_date' => $entryDate->format('Y-m-d'),
                'document_number' => $entryData['document_number'] ?? $journalEntry->document_number,
                'journal_type_id' => $entryData['journal_type_id'] ?? $journalEntry->journal_type_id,
                'description' => $entryData['description'] ?? $journalEntry->description,
            ]);

            // Replace lines
            $journalEntry->lines()->delete();

            foreach ($linesData as $index => $line) {
                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'line_no' => $index + 1,
                    'account_id' => $line['account_id'],
                    'unit_id' => ! empty($line['unit_id']) ? $line['unit_id'] : null,
                    'description' => $line['description'] ?? $entryData['description'] ?? null,
                    'debit' => (float) ($line['debit'] ?? 0),
                    'credit' => (float) ($line['credit'] ?? 0),
                ]);
            }

            return $journalEntry;
        });
    }

    /**
     * Delete a journal entry and its lines.
     */
    public function deleteJournalEntry(JournalEntry $journalEntry): void
    {
        if ($journalEntry->status === 'posted') {
            throw new Exception("Akses Ditolak! Jurnal '{$journalEntry->entry_number}' berstatus POSTED dan tidak dapat dihapus demi keamanan audit. Transaksi yang sudah terposting harus dibalikkan melalui Jurnal Pembalik (Reversal).");
        }

        if ($journalEntry->status === 'reversed') {
            throw new Exception("Jurnal Reversal ({$journalEntry->entry_number}) berstatus terkunci dan tidak dapat dihapus.");
        }

        DB::transaction(function () use ($journalEntry) {
            $journalEntry->lines()->delete();
            $journalEntry->delete();
        });
    }

    /**
     * Create a manual journal entry with status 'draft'.
     */
    public function createDraftEntry(array $entryData, array $linesData, ?int $userId = null, string $entryType = 'general'): JournalEntry
    {
        $companyId = $entryData['company_id'] ?? Company::first()?->id ?? 1;
        $entryDate = Carbon::parse($entryData['entry_date']);

        $period = AccountingPeriod::where('company_id', $companyId)
            ->where('year', $entryDate->year)
            ->where('month', $entryDate->month)
            ->first();

        if (! $period) {
            $period = AccountingPeriod::create([
                'company_id' => $companyId,
                'year' => $entryDate->year,
                'month' => $entryDate->month,
                'start_date' => $entryDate->copy()->startOfMonth(),
                'end_date' => $entryDate->copy()->endOfMonth(),
                'status' => 'open',
            ]);
        }

        $totalDebit = 0.0;
        $totalCredit = 0.0;

        if (count($linesData) < 2) {
            throw new Exception('Jurnal harus memiliki minimal 2 baris transaksi.');
        }

        foreach ($linesData as $index => $line) {
            $account = Account::find($line['account_id']);
            if (! $account) {
                throw new Exception('Baris #'.($index + 1).": Akun ID {$line['account_id']} tidak ditemukan.");
            }
            if ($account->is_group) {
                throw new Exception('Baris #'.($index + 1).": Akun '{$account->code} - {$account->name}' adalah Header Group.");
            }
            if (! $account->is_active) {
                throw new Exception('Baris #'.($index + 1).": Akun '{$account->code} - {$account->name}' berstatus tidak aktif.");
            }

            $totalDebit += (float) ($line['debit'] ?? 0);
            $totalCredit += (float) ($line['credit'] ?? 0);
        }

        if (abs($totalDebit - $totalCredit) >= 0.01) {
            throw new Exception('Jurnal tidak seimbang (Unbalanced)! Total Debit ('.number_format($totalDebit, 2, ',', '.').') != Total Kredit ('.number_format($totalCredit, 2, ',', '.').').');
        }

        return DB::transaction(function () use ($companyId, $period, $entryDate, $entryData, $linesData, $entryType) {
            $entryNumber = $this->generateEntryNumber($companyId, $entryDate, $entryType);
            $journalTypeId = $entryData['journal_type_id'] ?? null;
            $documentNumber = $entryData['document_number'] ?? null;
            $isAutoDoc = (bool) ($entryData['is_auto_document_number'] ?? true);

            if ($isAutoDoc && $journalTypeId && empty($documentNumber)) {
                $documentNumber = $this->generateDocumentNumber($journalTypeId, $entryDate, $companyId);
            }

            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'period_id' => $period->id,
                'entry_number' => $entryNumber,
                'entry_date' => $entryDate->format('Y-m-d'),
                'document_number' => $documentNumber,
                'is_auto_document_number' => $isAutoDoc,
                'journal_type_id' => $journalTypeId,
                'description' => $entryData['description'] ?? null,
                'source_type' => 'manual',
                'entry_type' => $entryType,
                'status' => 'draft',
                'posted_by' => null,
                'posted_at' => null,
            ]);

            foreach ($linesData as $index => $line) {
                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'line_no' => $index + 1,
                    'account_id' => $line['account_id'],
                    'unit_id' => ! empty($line['unit_id']) ? $line['unit_id'] : null,
                    'description' => $line['description'] ?? $entryData['description'] ?? null,
                    'debit' => (float) ($line['debit'] ?? 0),
                    'credit' => (float) ($line['credit'] ?? 0),
                ]);
            }

            return $journalEntry;
        });
    }

    /**
     * Post an existing draft journal entry to General Ledger.
     */
    public function postDraftEntry(JournalEntry $journalEntry, ?int $userId = null): JournalEntry
    {
        if ($journalEntry->status !== 'draft') {
            throw new Exception("Jurnal {$journalEntry->entry_number} tidak berstatus draft.");
        }

        $period = $journalEntry->period;
        if ($period && ! $period->isOpen()) {
            throw new Exception("Gagal posting! Periode akuntansi berstatus '{$period->status}'.");
        }

        if (! $journalEntry->is_balanced) {
            throw new Exception("Jurnal {$journalEntry->entry_number} tidak seimbang (Unbalanced).");
        }

        $journalEntry->update([
            'status' => 'posted',
            'posted_by' => $userId,
            'posted_at' => now(),
        ]);

        return $journalEntry;
    }

    /**
     * Generate unique journal entry number (JU-YYYY-MM-XXXXXX, AJP-YYYY-MM-XXXXXX, etc)
     */
    public function generateEntryNumber(int $companyId, \DateTimeInterface $date, string $entryType = 'general'): string
    {
        $code = match ($entryType) {
            'adjustment' => 'AJP',
            'closing' => 'JP',
            default => 'JU',
        };

        $prefix = $code.'-'.$date->format('Y-m');

        $latestNumber = JournalEntry::where('company_id', $companyId)
            ->where('entry_number', 'like', "{$prefix}-%")
            ->orderByDesc('entry_number')
            ->value('entry_number');

        if ($latestNumber) {
            $seq = (int) substr($latestNumber, -6) + 1;
        } else {
            $seq = 1;
        }

        return sprintf('%s-%06d', $prefix, $seq);
    }

    /**
     * Generate document number format: CODE/YYYY/MM/XXXX (e.g. BM/2026/08/0001)
     */
    public function generateDocumentNumber(int $journalTypeId, \DateTimeInterface $date, int $companyId = 1): string
    {
        $journalType = JournalType::find($journalTypeId);
        $code = $journalType ? $journalType->code : 'DOC';

        $prefix = sprintf('%s/%s/%s/', $code, $date->format('Y'), $date->format('m'));

        $latestDoc = JournalEntry::where('company_id', $companyId)
            ->where('journal_type_id', $journalTypeId)
            ->where('document_number', 'like', "{$prefix}%")
            ->orderByDesc('document_number')
            ->value('document_number');

        if ($latestDoc) {
            $parts = explode('/', $latestDoc);
            $seq = (int) end($parts) + 1;
        } else {
            $seq = 1;
        }

        return sprintf('%s%04d', $prefix, $seq);
    }
}
