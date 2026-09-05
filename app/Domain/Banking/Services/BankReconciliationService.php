<?php

namespace App\Domain\Banking\Services;

use App\Models\Account;
use App\Models\BankReconciliationMatchGroup;
use App\Models\BankStatement;
use App\Models\BankStatementLine;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BankReconciliationService
{
    public const MATCH_TOLERANCE = 2.00;

    public function __construct(
        protected BriCmsPdfParserService $parserService
    ) {}

    /**
     * Impor rekening koran dari file PDF CMS BRI dan simpan ke database.
     */
    public function importStatement(int $accountId, string $filePath, string $originalFileName, ?User $user = null): BankStatement
    {
        $account = Account::findOrFail($accountId);
        $user = $user ?? auth()->user();

        $parsed = $this->parserService->parse($filePath);
        $meta = $parsed['metadata'];
        $rawLines = $parsed['lines'];

        return DB::transaction(function () use ($account, $meta, $rawLines, $filePath, $originalFileName, $user) {
            $statement = BankStatement::create([
                'account_id' => $account->id,
                'bank_name' => $meta['bank_name'],
                'account_number' => $meta['account_number'],
                'account_holder' => $meta['account_holder'],
                'period_start' => $meta['period_start'],
                'period_end' => $meta['period_end'],
                'opening_balance' => $meta['opening_balance'],
                'total_debit' => $meta['total_debit'],
                'total_credit' => $meta['total_credit'],
                'closing_balance' => $meta['closing_balance'],
                'file_path' => $filePath,
                'file_name' => $originalFileName,
                'status' => 'pending',
                'uploaded_by' => $user?->id,
            ]);

            $linesData = [];
            $now = now();
            foreach ($rawLines as $line) {
                $linesData[] = [
                    'bank_statement_id' => $statement->id,
                    'transaction_date' => $line['date'],
                    'transaction_time' => $line['time'],
                    'description' => $line['description'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'balance' => $line['balance'],
                    'teller_id' => $line['teller_id'],
                    'match_status' => 'unmatched',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($linesData, 100) as $chunk) {
                BankStatementLine::insert($chunk);
            }

            // Jalankan auto match awal
            $this->autoMatch($statement, $user);

            return $statement->fresh(['lines']);
        });
    }

    /**
     * Jalankan algoritma pencocokan otomatis 2-arah.
     *
     * @return array{matched_count: int, remaining_unmatched: int}
     */
    public function autoMatch(BankStatement $statement, ?User $user = null): array
    {
        $user = $user ?? auth()->user();
        $unmatchedLines = $statement->lines()->where('match_status', 'unmatched')->get();

        if ($unmatchedLines->isEmpty()) {
            return ['matched_count' => 0, 'remaining_unmatched' => 0];
        }

        // Ambil ID JournalLine yang sudah pernah dicocokkan agar tidak duplicate match
        $alreadyMatchedIds = BankStatementLine::whereNotNull('matched_journal_line_id')
            ->pluck('matched_journal_line_id')
            ->toArray();

        // Ambil kandidat baris jurnal buku besar untuk akun bank ini
        // Rentang tanggal +/- 3 hari
        $minDate = Carbon::parse($statement->period_start)->subDays(3)->format('Y-m-d');
        $maxDate = Carbon::parse($statement->period_end)->addDays(3)->format('Y-m-d');

        $journalCandidates = JournalLine::with('journalEntry')
            ->where('account_id', $statement->account_id)
            ->whereHas('journalEntry', function ($q) use ($minDate, $maxDate) {
                $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$minDate, $maxDate]);
            })
            ->whereNotIn('id', $alreadyMatchedIds)
            ->get();

        $matchedCount = 0;
        $usedCandidateIds = [];

        // Tahap 1: Exact Match (Nominal sama + Tanggal sama persis)
        foreach ($unmatchedLines as $stLine) {
            $isBankDebit = (float) $stLine->debit > 0;
            $amount = $isBankDebit ? (float) $stLine->debit : (float) $stLine->credit;
            $targetDate = Carbon::parse($stLine->transaction_date)->format('Y-m-d');

            $candidate = $journalCandidates->first(function (JournalLine $jLine) use ($isBankDebit, $amount, $targetDate, $usedCandidateIds) {
                if (in_array($jLine->id, $usedCandidateIds)) {
                    return false;
                }
                /** @var JournalEntry|null $jEntry */
                $jEntry = $jLine->journalEntry;
                if (! $jEntry) {
                    return false;
                }
                $entryDate = Carbon::parse($jEntry->entry_date)->format('Y-m-d');
                if ($entryDate !== $targetDate) {
                    return false;
                }
                // Jika bank debit (uang keluar), di jurnal adalah credit
                // Jika bank credit (uang masuk), di jurnal adalah debit
                $jAmount = $isBankDebit ? (float) $jLine->credit : (float) $jLine->debit;

                return abs($jAmount - $amount) <= self::MATCH_TOLERANCE;
            });

            if ($candidate) {
                $stLine->update([
                    'match_status' => 'matched',
                    'matched_journal_line_id' => $candidate->id,
                    'matched_at' => now(),
                    'matched_by' => $user?->id,
                ]);
                $usedCandidateIds[] = $candidate->id;
                $matchedCount++;
            }
        }

        // Tahap 2: Tolerance Window Match (+/- 3 hari kalender)
        $remainingLines = $statement->lines()->where('match_status', 'unmatched')->get();
        foreach ($remainingLines as $stLine) {
            $isBankDebit = (float) $stLine->debit > 0;
            $amount = $isBankDebit ? (float) $stLine->debit : (float) $stLine->credit;
            $stDate = Carbon::parse($stLine->transaction_date);

            $candidate = $journalCandidates->first(function (JournalLine $jLine) use ($isBankDebit, $amount, $stDate, $usedCandidateIds) {
                if (in_array($jLine->id, $usedCandidateIds)) {
                    return false;
                }
                /** @var JournalEntry|null $jEntry */
                $jEntry = $jLine->journalEntry;
                if (! $jEntry) {
                    return false;
                }
                $entryDate = Carbon::parse($jEntry->entry_date);
                if (abs($entryDate->diffInDays($stDate)) > 3) {
                    return false;
                }
                $jAmount = $isBankDebit ? (float) $jLine->credit : (float) $jLine->debit;

                return abs($jAmount - $amount) <= self::MATCH_TOLERANCE;
            });

            if ($candidate) {
                $stLine->update([
                    'match_status' => 'matched',
                    'matched_journal_line_id' => $candidate->id,
                    'matched_at' => now(),
                    'matched_by' => $user?->id,
                ]);
                $usedCandidateIds[] = $candidate->id;
                $matchedCount++;
            }
        }

        // Update status bank statement
        $totalCount = $statement->lines()->count();
        $matchedTotal = $statement->lines()->whereIn('match_status', ['matched', 'manual_matched', 'adjusted', 'opening_reconciled'])->count();

        $newStatus = 'in_progress';
        if ($matchedTotal === $totalCount && $totalCount > 0) {
            $newStatus = 'reconciled';
        }

        $statement->update(['status' => $newStatus]);

        return [
            'matched_count' => $matchedCount,
            'remaining_unmatched' => $totalCount - $matchedTotal,
        ];
    }

    /**
     * Cocokkan transaksi secara fleksibel (1-ke-1, 1-ke-N, N-ke-1, atau N-ke-M).
     * Memvalidasi bahwa total nominal bank harus persis sama dengan total nominal buku (Selisih = 0).
     *
     * @param  array<int>  $statementLineIds
     * @param  array<int>  $journalLineIds
     */
    public function multiMatch(array $statementLineIds, array $journalLineIds, ?User $user = null): BankReconciliationMatchGroup
    {
        if (empty($statementLineIds) || empty($journalLineIds)) {
            throw new \InvalidArgumentException('Pilih minimal 1 baris mutasi bank dan 1 baris buku kas/bank.');
        }

        $user = $user ?? auth()->user();

        $stLines = BankStatementLine::whereIn('id', $statementLineIds)->get();
        $jLines = JournalLine::whereIn('id', $journalLineIds)->get();

        if ($stLines->count() !== count($statementLineIds) || $jLines->count() !== count($journalLineIds)) {
            throw new \InvalidArgumentException('Satu atau lebih data transaksi tidak ditemukan.');
        }

        $firstStLine = $stLines->first();
        if (! $firstStLine) {
            throw new \InvalidArgumentException('Data mutasi bank tidak valid.');
        }
        $statement = $firstStLine->bankStatement;

        // Hitung total nominal bank
        $totalBank = (float) $stLines->sum(function (BankStatementLine $line) {
            return (float) $line->debit > 0 ? (float) $line->debit : (float) $line->credit;
        });

        // Hitung total nominal buku kas/bank
        $totalBook = (float) $jLines->sum(function (JournalLine $jLine) {
            return (float) $jLine->debit > 0 ? (float) $jLine->debit : (float) $jLine->credit;
        });

        $diff = abs($totalBank - $totalBook);

        // Validasi: Selisih tidak boleh melebihi batas toleransi Rp 2,00
        if ($diff > self::MATCH_TOLERANCE) {
            $formattedDiff = number_format($diff, 2, ',', '.');
            throw new \InvalidArgumentException("Pencocokan gagal: Terdapat selisih sebesar Rp {$formattedDiff} (melebihi batas toleransi Rp 2,00). Nominal transaksi harus seimbang dalam batas toleransi.");
        }

        return DB::transaction(function () use ($statement, $stLines, $statementLineIds, $journalLineIds, $totalBank, $totalBook, $diff, $user) {
            $matchType = (count($statementLineIds) > 1 || count($journalLineIds) > 1) ? 'manual_multi' : 'manual_single';

            /** @var BankReconciliationMatchGroup $group */
            $group = BankReconciliationMatchGroup::create([
                'bank_statement_id' => $statement->id,
                'match_code' => 'GRP-'.Str::upper(Str::random(8)),
                'match_type' => $matchType,
                'total_bank_amount' => $totalBank,
                'total_book_amount' => $totalBook,
                'difference' => $diff,
                'matched_by' => $user?->id,
                'matched_at' => now(),
            ]);

            $group->statementLines()->sync($statementLineIds);
            $group->journalLines()->sync($journalLineIds);

            // Update status masing-masing statement line
            foreach ($stLines as $stLine) {
                $stLine->update([
                    'match_status' => 'manual_matched',
                    'matched_journal_line_id' => count($journalLineIds) === 1 ? $journalLineIds[0] : null,
                    'matched_at' => now(),
                    'matched_by' => $user?->id,
                ]);
            }

            $this->refreshStatementStatus($statement);

            return $group;
        });
    }

    /**
     * Cocokkan baris secara manual antara rekening koran dan baris jurnal.
     */
    public function manualMatch(int $statementLineId, int $journalLineId, ?User $user = null): void
    {
        $this->multiMatch([$statementLineId], [$journalLineId], $user);
    }

    /**
     * Batalkan pencocokan baris rekonsiliasi.
     */
    public function unmatch(int $statementLineId): void
    {
        $stLine = BankStatementLine::findOrFail($statementLineId);
        $statement = $stLine->bankStatement;

        DB::transaction(function () use ($stLine) {
            // Cari grup pencocokan yang terhubung
            $matchGroups = $stLine->matchGroups()->get();

            if ($matchGroups->isNotEmpty()) {
                foreach ($matchGroups as $group) {
                    // Reset status semua statement line di grup ini
                    foreach ($group->statementLines as $linkedStLine) {
                        $linkedStLine->update([
                            'match_status' => 'unmatched',
                            'matched_journal_line_id' => null,
                            'matched_at' => null,
                            'matched_by' => null,
                        ]);
                    }
                    // Hapus grup (pivot otomatis terhapus cascade)
                    $group->delete();
                }
            } else {
                $stLine->update([
                    'match_status' => 'unmatched',
                    'matched_journal_line_id' => null,
                    'matched_at' => null,
                    'matched_by' => null,
                ]);
            }
        });

        $this->refreshStatementStatus($statement);
    }

    /**
     * Tandai baris rekening koran sebagai pencairan cek beredar / pos saldo awal (Desember 2024 sebelum Go-Live).
     * Transaksi ini tidak membuat jurnal baru di tahun 2025 sehingga tidak mempengaruhi Laba/Rugi maupun Neraca.
     */
    public function markAsOpeningOutstanding(int $statementLineId, ?string $notes = null, ?User $user = null): void
    {
        $stLine = BankStatementLine::findOrFail($statementLineId);
        $user = $user ?? auth()->user();

        $stLine->update([
            'match_status' => 'opening_reconciled',
            'matched_at' => now(),
            'matched_by' => $user?->id,
            'notes' => $notes ?: 'Cek Beredar / Transaksi Saldo Awal (Desember 2024)',
        ]);

        $this->refreshStatementStatus($stLine->bankStatement);
    }

    /**
     * Buat jurnal penyesuaian instan (biaya bank / pendapatan bunga) langsung dari baris mutasi bank.
     */
    public function createQuickAdjustment(int $statementLineId, int $contraAccountId, ?string $description = null, ?User $user = null): JournalEntry
    {
        $stLine = BankStatementLine::findOrFail($statementLineId);
        $statement = $stLine->bankStatement;
        $bankAccount = $statement->account;
        $contraAccount = Account::findOrFail($contraAccountId);
        $user = $user ?? auth()->user();

        $isBankDebit = (float) $stLine->debit > 0;
        $amount = $isBankDebit ? (float) $stLine->debit : (float) $stLine->credit;
        $entryDate = Carbon::parse($stLine->transaction_date)->format('Y-m-d');
        $journalDesc = $description ?: $stLine->description;

        // Tentukan Unit & Company
        $unit = Unit::first() ?? Unit::create(['name' => 'Kantor Pusat', 'code' => 'KP']);
        $unitId = $unit->id;

        $company = Company::first() ?? Company::create(['name' => 'PT ARTA LEDGER INDONESIA', 'code' => 'ALI']);
        $companyId = $company->id;

        return DB::transaction(function () use ($stLine, $statement, $bankAccount, $contraAccount, $isBankDebit, $amount, $entryDate, $journalDesc, $unitId, $companyId, $user) {
            $prefix = $isBankDebit ? 'ADJ-OUT' : 'ADJ-IN';
            $entryNumber = $prefix.'-'.date('ymd').'-'.str_pad((string) rand(100, 999), 3, '0', STR_PAD_LEFT);

            $entry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_number' => $entryNumber,
                'document_number' => 'REKON-'.$stLine->id,
                'entry_date' => $entryDate,
                'entry_type' => 'adjustment',
                'source_type' => 'reconciliation',
                'description' => $journalDesc,
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => $user?->id,
                'created_by' => $user?->id,
            ]);

            // Jika Bank Debit (uang keluar di bank):
            // Bank Account = Kredit, Contra Account (misal Beban Admin Bank) = Debit
            // Jika Bank Credit (uang masuk di bank):
            // Bank Account = Debit, Contra Account (misal Pendapatan Bunga) = Kredit
            if ($isBankDebit) {
                // Debit: Contra (Beban Admin)
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $contraAccount->id,
                    'unit_id' => $unitId,
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => $journalDesc,
                ]);

                // Kredit: Bank Account
                $bankLine = JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $bankAccount->id,
                    'unit_id' => $unitId,
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => $journalDesc,
                ]);
            } else {
                // Debit: Bank Account
                $bankLine = JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $bankAccount->id,
                    'unit_id' => $unitId,
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => $journalDesc,
                ]);

                // Kredit: Contra (Pendapatan Bunga)
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $contraAccount->id,
                    'unit_id' => $unitId,
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => $journalDesc,
                ]);
            }

            // Tautkan baris bank statement ke baris jurnal bank yang baru dibuat
            $stLine->update([
                'match_status' => 'adjusted',
                'matched_journal_line_id' => $bankLine->id,
                'matched_at' => now(),
                'matched_by' => $user?->id,
                'notes' => "Jurnal Penyesuaian {$entryNumber}",
            ]);

            $this->refreshStatementStatus($statement);

            return $entry;
        });
    }

    /**
     * Hitung ringkasan rekonsiliasi bank komprehensif.
     *
     * @return array{
     *     book_balance: float,
     *     bank_balance: float,
     *     total_matched_count: int,
     *     total_unmatched_count: int,
     *     reconciled_percentage: float,
     *     deposits_in_transit: float,
     *     outstanding_checks: float,
     *     unrecorded_bank_credits: float,
     *     unrecorded_bank_debits: float,
     *     adjusted_book_balance: float,
     *     adjusted_bank_balance: float,
     *     difference: float
     * }
     */
    public function calculateSummary(BankStatement $statement): array
    {
        $account = $statement->account;

        // 1. Saldo Buku Kas/Bank per akhir periode
        $openingBalance = (float) $account->opening_balance;
        $jDeb = (float) JournalLine::where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($statement) {
                $q->where('status', 'posted')
                    ->where('entry_date', '<=', $statement->period_end);
            })
            ->sum('debit');

        $jCred = (float) JournalLine::where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($statement) {
                $q->where('status', 'posted')
                    ->where('entry_date', '<=', $statement->period_end);
            })
            ->sum('credit');

        // Akun Bank saldo normal Debit
        $bookBalance = $openingBalance + ($jDeb - $jCred);
        $bankBalance = (float) $statement->closing_balance;

        // 2. Baris Rekening Koran yang belum dicatat di buku
        $unmatchedBankLines = $statement->lines()->where('match_status', 'unmatched')->get();
        $unrecordedBankCredits = (float) $unmatchedBankLines->sum('credit'); // Uang masuk di bank, belum di buku
        $unrecordedBankDebits = (float) $unmatchedBankLines->sum('debit');   // Uang keluar di bank, belum di buku

        // 3. Baris Jurnal Buku yang belum kliring di bank (Outstanding checks & Deposits in transit)
        $matchedJournalLineIds = $statement->lines()->whereNotNull('matched_journal_line_id')->pluck('matched_journal_line_id')->toArray();

        $unmatchedBookLines = JournalLine::where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($statement) {
                $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$statement->period_start, $statement->period_end]);
            })
            ->whereNotIn('id', $matchedJournalLineIds)
            ->get();

        $depositsInTransit = (float) $unmatchedBookLines->sum('debit');   // Setoran di buku belum muncul di bank
        $outstandingChecks = (float) $unmatchedBookLines->sum('credit');  // Pengeluaran di buku belum ditarik di bank

        // 4. Saldo yang disesuaikan
        $adjustedBookBalance = $bookBalance + $unrecordedBankCredits - $unrecordedBankDebits;
        $adjustedBankBalance = $bankBalance + $depositsInTransit - $outstandingChecks;
        $difference = abs($adjustedBookBalance - $adjustedBankBalance);

        $totalLines = $statement->lines()->count();
        $matchedCount = $statement->lines()->whereIn('match_status', ['matched', 'manual_matched', 'adjusted', 'opening_reconciled'])->count();

        return [
            'book_balance' => $bookBalance,
            'bank_balance' => $bankBalance,
            'total_matched_count' => $matchedCount,
            'total_unmatched_count' => $totalLines - $matchedCount,
            'reconciled_percentage' => $totalLines > 0 ? round(($matchedCount / $totalLines) * 100, 1) : 0.0,
            'deposits_in_transit' => $depositsInTransit,
            'outstanding_checks' => $outstandingChecks,
            'unrecorded_bank_credits' => $unrecordedBankCredits,
            'unrecorded_bank_debits' => $unrecordedBankDebits,
            'adjusted_book_balance' => $adjustedBookBalance,
            'adjusted_bank_balance' => $adjustedBankBalance,
            'difference' => $difference,
        ];
    }

    protected function refreshStatementStatus(BankStatement $statement): void
    {
        $totalCount = $statement->lines()->count();
        $matchedTotal = $statement->lines()->whereIn('match_status', ['matched', 'manual_matched', 'adjusted', 'opening_reconciled'])->count();

        $status = 'pending';
        if ($matchedTotal === $totalCount && $totalCount > 0) {
            $status = 'reconciled';
        } elseif ($matchedTotal > 0) {
            $status = 'in_progress';
        }

        $statement->update(['status' => $status]);
    }
}
