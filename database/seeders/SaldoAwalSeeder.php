<?php

namespace Database\Seeders;

use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaldoAwalSeeder extends Seeder
{
    /**
     * Seed opening balance journal entry from database/data/saldo_awal.json.
     */
    public function run(): void
    {
        $jsonPath = database_path('data/saldo_awal.json');

        if (! file_exists($jsonPath)) {
            $this->command?->error("File {$jsonPath} tidak ditemukan!");

            return;
        }

        $jsonItems = json_decode(file_get_contents($jsonPath), true);

        if (empty($jsonItems)) {
            $this->command?->error('File saldo_awal.json kosong atau tidak valid!');

            return;
        }

        $companyId = Company::first()?->id ?? 1;
        $entryDate = '2025-01-01';

        $period = AccountingPeriod::where('company_id', $companyId)
            ->whereDate('start_date', '<=', $entryDate)
            ->whereDate('end_date', '>=', $entryDate)
            ->first();

        if (! $period) {
            $period = AccountingPeriod::create([
                'company_id' => $companyId,
                'year' => 2025,
                'month' => 1,
                'start_date' => '2025-01-01',
                'end_date' => '2025-01-31',
                'status' => 'open',
            ]);
        }

        DB::transaction(function () use ($companyId, $period, $entryDate, $jsonItems) {
            // Idempotency: Hapus jurnal saldo awal jika sudah ada sebelumnya
            $existingEntry = JournalEntry::where('company_id', $companyId)
                ->where('entry_number', 'SA-2025-001')
                ->first();

            if ($existingEntry) {
                $existingEntry->lines()->delete();
                $existingEntry->delete();
            }

            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'period_id' => $period->id,
                'entry_number' => 'SA-2025-001',
                'entry_date' => $entryDate,
                'document_number' => 'SALDO-AWAL-2025',
                'description' => 'Posting Saldo Awal Akuntansi Tahun 2025',
                'source_type' => 'manual',
                'entry_type' => 'general',
                'status' => 'posted',
                'posted_by' => 1,
                'posted_at' => now(),
            ]);

            $totalDebit = 0.0;
            $totalCredit = 0.0;

            foreach ($jsonItems as $index => $item) {
                $debit = (float) ($item['debit'] ?? 0.0);
                $credit = (float) ($item['credit'] ?? 0.0);

                $totalDebit += $debit;
                $totalCredit += $credit;

                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'line_no' => $index + 1,
                    'account_id' => $item['account_id'],
                    'unit_id' => $item['unit_id'] ?? 1,
                    'description' => $item['description'] ?? 'Saldo Awal 2025',
                    'debit' => $debit,
                    'credit' => $credit,
                ]);
            }

            $this->command?->info('Berhasil memposting Jurnal Saldo Awal (SA-2025-001):');
            $this->command?->info('Total Baris: '.count($jsonItems));
            $this->command?->info('Total Debit: Rp '.number_format($totalDebit, 2, ',', '.'));
            $this->command?->info('Total Kredit: Rp '.number_format($totalCredit, 2, ',', '.'));
            $this->command?->info('Selisih: Rp '.number_format(abs($totalDebit - $totalCredit), 2, ',', '.'));
        });
    }
}
