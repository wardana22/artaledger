<?php

namespace App\Domain\Import\Services;

use App\Domain\Accounting\Services\JournalPostingService;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\ImportBatch;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Exception;
use Illuminate\Support\Facades\DB;

class ImportCommitService
{
    /**
     * Commit valid rows of an ImportBatch into journal_entries & journal_lines.
     */
    public function commitBatch(ImportBatch $batch, ?int $userId = null): array
    {
        if ($batch->error_rows > 0) {
            throw new Exception("Gagal Posting! Terdapat {$batch->error_rows} baris berstatus ERROR. Harap perbaiki file Excel terlebih dahulu.");
        }

        if ($batch->status === 'posted') {
            throw new Exception("Batch import '{$batch->batch_code}' sudah pernah diposting sebelumnya.");
        }

        $rows = $batch->rows()->where('validation_status', 'valid')->get();

        if ($rows->isEmpty()) {
            throw new Exception('Tidak ada baris transaksi valid yang dapat diposting.');
        }

        $companyId = Company::first()?->id ?? 1;
        $postingService = new JournalPostingService;

        // Group rows by source_block_key
        $blocks = $rows->groupBy('source_block_key');
        $postedEntries = [];

        DB::transaction(function () use ($batch, $blocks, $companyId, $userId, $postingService, &$postedEntries) {
            foreach ($blocks as $blockKey => $blockRows) {
                $firstRow = $blockRows->first();
                $entryDate = $firstRow->entry_date;

                $period = AccountingPeriod::where('company_id', $companyId)
                    ->where('status', 'open')
                    ->whereDate('start_date', '<=', $entryDate)
                    ->whereDate('end_date', '>=', $entryDate)
                    ->first();

                if (! $period) {
                    throw new Exception("Periode akuntansi untuk tanggal {$entryDate->format('d/m/Y')} tidak ditemukan atau sudah ditutup.");
                }

                $entryNumber = $postingService->generateEntryNumber($companyId, $entryDate, 'general');

                $journalEntry = JournalEntry::create([
                    'company_id' => $companyId,
                    'period_id' => $period->id,
                    'entry_number' => $entryNumber,
                    'entry_date' => $entryDate->format('Y-m-d'),
                    'document_number' => $firstRow->document_number,
                    'description' => $firstRow->description ?? "Import Jurnal ({$batch->batch_code})",
                    'source_type' => 'import',
                    'entry_type' => 'general',
                    'status' => 'posted',
                    'posted_by' => $userId,
                    'posted_at' => now(),
                ]);

                foreach ($blockRows as $lineNo => $row) {
                    JournalLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'line_no' => $lineNo + 1,
                        'account_id' => $row->account_id,
                        'unit_id' => $row->unit_id,
                        'description' => $row->description,
                        'debit' => (float) $row->debit,
                        'credit' => (float) $row->credit,
                        'source_import_row_id' => $row->id,
                    ]);
                }

                $postedEntries[] = $journalEntry;
            }

            $batch->update(['status' => 'posted']);
        });

        return $postedEntries;
    }
}
