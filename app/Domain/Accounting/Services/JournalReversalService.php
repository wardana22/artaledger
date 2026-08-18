<?php

namespace App\Domain\Accounting\Services;

use App\Models\JournalEntry;
use App\Models\JournalLine;
use Exception;
use Illuminate\Support\Facades\DB;

class JournalReversalService
{
    public function reverseJournalEntry(JournalEntry $entry, ?int $userId = null, ?string $reason = null): JournalEntry
    {
        if ($entry->status !== 'posted') {
            throw new Exception("Hanya jurnal berstatus 'posted' yang dapat dibalikkan (reversed). Status jurnal saat ini: '{$entry->status}'.");
        }

        if ($entry->source_type === 'reversal' || str_starts_with($entry->document_number ?? '', 'REV-') || str_starts_with($entry->description ?? '', 'REVERSAL:')) {
            throw new Exception('Jurnal pembalik (Reversal) dikunci dan tidak dapat dibalikkan kembali. Silakan buat Jurnal Baru atau Jurnal Koreksi.');
        }

        return DB::transaction(function () use ($entry, $userId, $reason) {
            $postingService = new JournalPostingService;
            $reversalNumber = $postingService->generateEntryNumber($entry->company_id, now());

            $reversalEntry = JournalEntry::create([
                'company_id' => $entry->company_id,
                'period_id' => $entry->period_id,
                'entry_number' => $reversalNumber,
                'entry_date' => now()->format('Y-m-d'),
                'document_number' => 'REV-'.($entry->entry_number),
                'description' => 'REVERSAL: '.($reason ?? $entry->description),
                'source_type' => 'reversal',
                'status' => 'posted',
                'posted_by' => $userId,
                'posted_at' => now(),
            ]);

            foreach ($entry->lines as $line) {
                // Swap debit & credit
                JournalLine::create([
                    'journal_entry_id' => $reversalEntry->id,
                    'line_no' => $line->line_no,
                    'account_id' => $line->account_id,
                    'description' => 'REVERSAL: '.$line->description,
                    'debit' => $line->credit,
                    'credit' => $line->debit,
                ]);
            }

            // Mark original entry as reversed
            $entry->update(['status' => 'reversed']);

            return $reversalEntry;
        });
    }
}
