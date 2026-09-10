<?php

namespace App\Domain\Accounting\Services;

use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Services\AuditLogService;
use Exception;
use Illuminate\Support\Facades\DB;

class JournalReversalService
{
    public function reverseJournalEntry(JournalEntry $entry, ?int $userId = null, ?string $reason = null): JournalEntry
    {
        if ($entry->status !== 'posted') {
            throw new Exception("Hanya jurnal berstatus 'posted' yang dapat dibalikkan (reversed). Status jurnal saat ini: '{$entry->status}'.");
        }

        if ($entry->is_locked) {
            throw new Exception("Jurnal ({$entry->entry_number}) berstatus Terkunci (Locked) dan tidak dapat dibalikkan secara langsung.");
        }

        if ($entry->source_type === 'reversal' || str_starts_with($entry->document_number ?? '', 'REV-') || str_starts_with($entry->description ?? '', 'REVERSAL:')) {
            throw new Exception('Jurnal pembalik (Reversal) dikunci dan tidak dapat dibalikkan kembali. Silakan buat Jurnal Baru atau Jurnal Koreksi.');
        }

        $now = now();
        $reversalPeriod = AccountingPeriod::where('company_id', $entry->company_id)
            ->where('year', $now->year)
            ->where('month', $now->month)
            ->first();

        if (! $reversalPeriod) {
            $reversalPeriod = AccountingPeriod::create([
                'company_id' => $entry->company_id,
                'year' => $now->year,
                'month' => $now->month,
                'start_date' => $now->copy()->startOfMonth(),
                'end_date' => $now->copy()->endOfMonth(),
                'status' => 'open',
            ]);
        }

        if (! $reversalPeriod->isOpen()) {
            throw new Exception("Gagal melakukan pembalikan! Periode akuntansi saat ini ({$now->format('F Y')}) berstatus '{$reversalPeriod->status}'. Pembalikan jurnal hanya diizinkan pada periode 'open'.");
        }

        return DB::transaction(function () use ($entry, $userId, $reason, $reversalPeriod, $now) {
            $postingService = new JournalPostingService;
            $reversalNumber = $postingService->generateEntryNumber($entry->company_id, $now);

            $reversalEntry = JournalEntry::create([
                'company_id' => $entry->company_id,
                'period_id' => $reversalPeriod->id,
                'entry_number' => $reversalNumber,
                'entry_date' => $now->format('Y-m-d'),
                'document_number' => 'REV-'.($entry->entry_number),
                'description' => 'REVERSAL: '.($reason ?? $entry->description),
                'source_type' => 'reversal',
                'status' => 'posted',
                'posted_by' => $userId,
                'posted_at' => $now,
            ]);

            foreach ($entry->lines as $line) {
                // Swap debit & credit
                JournalLine::create([
                    'journal_entry_id' => $reversalEntry->id,
                    'line_no' => $line->line_no,
                    'account_id' => $line->account_id,
                    'unit_id' => $line->unit_id,
                    'description' => 'REVERSAL: '.$line->description,
                    'debit' => $line->credit,
                    'credit' => $line->debit,
                ]);
            }

            // Mark original entry as reversed
            $entry->update(['status' => 'reversed']);

            AuditLogService::record(
                'journal.reversed',
                "Membalikkan (Reverse) Jurnal {$entry->entry_number} dengan Jurnal Reversal {$reversalEntry->entry_number}",
                $reversalEntry,
                ['status' => 'posted'],
                ['status' => 'reversed', 'reversal_id' => $reversalEntry->id],
                $userId
            );

            return $reversalEntry;
        });
    }
}
