<?php

namespace App\Domain\Accounting\Services;

use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Unit;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class YearEndClosingService
{
    /**
     * Menjalankan proses tutup buku akhir tahun dan rollover saldo awal otomatis ke tahun berikutnya.
     *
     * @param  int  $closedYear  Tahun yang ditutup bukunya (misal: 2025)
     * @param  int|null  $userId  ID User yang melakukan eksekusi
     * @return JournalEntry Jurnal Saldo Awal (SA-{nextYear}-001) yang dihasilkan/diperbarui
     */
    public static function rolloverYearEndOpeningBalance(int $closedYear, ?int $userId = null): JournalEntry
    {
        return DB::transaction(function () use ($closedYear, $userId) {
            $nextYear = $closedYear + 1;
            $company = Company::first();
            $companyId = $company?->id ?? 1;
            $asOfDate = "{$closedYear}-12-31";
            $nextYearStartDate = "{$nextYear}-01-01";
            $defaultUnitId = Unit::first()?->id;

            // 1. Pastikan Periode Januari Tahun Berikutnya ($nextYear, Bulan 1) sudah ada
            $nextYearJanPeriod = AccountingPeriod::firstOrCreate(
                [
                    'company_id' => $companyId,
                    'year' => $nextYear,
                    'month' => 1,
                ],
                [
                    'start_date' => $nextYearStartDate,
                    'end_date' => "{$nextYear}-01-31",
                    'status' => 'open',
                ]
            );

            // 2. Hitung Laba/Rugi Bersih Akumulatif s.d. 31 Desember $closedYear (Prior Net Profit)
            // Mengikuti logika persis OpeningBalanceIndex: iterasi akun nominal detail (is_group = false)
            $nominalAccounts = Account::active()
                ->where('report_type', 'laba_rugi')
                ->where('is_group', false)
                ->get();

            $totRev = 0.0;
            $totExp = 0.0;

            foreach ($nominalAccounts as $nAcc) {
                $nomTotals = JournalLine::where('account_id', $nAcc->id)
                    ->whereHas('journalEntry', function ($q) use ($asOfDate) {
                        $q->where('status', 'posted')
                            ->where('entry_date', '<=', $asOfDate);
                    })
                    ->selectRaw('SUM(debit) as tot_debit, SUM(credit) as tot_credit')
                    ->first();

                $nD = (float) ($nomTotals->tot_debit ?? 0);
                $nC = (float) ($nomTotals->tot_credit ?? 0);

                if ($nAcc->normal_balance === 'credit') {
                    $totRev += ($nC - $nD);
                } else {
                    $totExp += ($nD - $nC);
                }
            }

            $priorNetProfit = $totRev - $totExp;

            // 3. Cari Akun Saldo Laba / Retained Earnings
            $retainedEarningsAccount = Account::where('report_type', 'neraca')
                ->where(function ($q) {
                    $q->where('code', '31.02')
                        ->orWhere('name', 'like', '%Saldo Laba%')
                        ->orWhere('name', 'like', '%Laba Ditahan%')
                        ->orWhere('code', 'like', '3102%');
                })
                ->first();

            // 4. Ambil seluruh akun neraca (sama persis dengan filter OpeningBalanceIndex viewMode=balance_sheet)
            $accounts = Account::active()
                ->where('report_type', 'neraca')
                ->orderBy('code', 'asc')
                ->get();

            // Hitung saldo kumulatif mutasi jurnal posted s.d. 31 Desember $closedYear
            $mutResults = JournalLine::whereHas('journalEntry', function ($q) use ($asOfDate) {
                $q->where('status', 'posted')
                    ->where('entry_date', '<=', $asOfDate);
            })
                ->select('account_id')
                ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                ->groupBy('account_id')
                ->get()
                ->keyBy('account_id');

            // 5. Siapkan atau temukan Journal Entry Saldo Awal SA-{$nextYear}-001
            $entryNumber = "SA-{$nextYear}-001";
            $entry = JournalEntry::where('entry_number', $entryNumber)
                ->orWhere(function ($q) use ($nextYear, $nextYearJanPeriod) {
                    $q->where('source_type', 'opening_balance')
                        ->where(function ($sub) use ($nextYear, $nextYearJanPeriod) {
                            $sub->where('period_id', $nextYearJanPeriod->id)
                                ->orWhereYear('entry_date', $nextYear);
                        });
                })
                ->first();

            $description = "Saldo Awal Otomatis Hasil Tutup Buku Tahun {$closedYear}";

            if (! $entry) {
                $entry = JournalEntry::create([
                    'uuid' => (string) Str::uuid(),
                    'company_id' => $companyId,
                    'period_id' => $nextYearJanPeriod->id,
                    'entry_number' => $entryNumber,
                    'entry_date' => $nextYearStartDate,
                    'document_number' => "DOC-SA-{$nextYear}",
                    'description' => $description,
                    'source_type' => 'opening_balance',
                    'entry_type' => 'general',
                    'status' => 'posted',
                    'is_locked' => true,
                    'posted_by' => $userId ?? 1,
                    'posted_at' => now(),
                ]);
            } else {
                $entry->update([
                    'period_id' => $nextYearJanPeriod->id,
                    'entry_number' => $entryNumber,
                    'entry_date' => $nextYearStartDate,
                    'document_number' => $entry->document_number ?: "DOC-SA-{$nextYear}",
                    'description' => $description,
                    'source_type' => 'opening_balance',
                    'entry_type' => 'general',
                    'status' => 'posted',
                    'is_locked' => true,
                ]);

                // Hapus lines lama sebelum me-rollover baris baru
                $entry->lines()->delete();
            }

            // 6. Bentuk baris Journal Lines untuk akun Neraca sesuai perhitungan Laporan Saldo Awal
            $lineIndex = 1;
            $retainedEarningsId = $retainedEarningsAccount?->id;

            foreach ($accounts as $acc) {
                $mut = $mutResults->get($acc->id);
                $d = (float) ($mut?->total_debit ?? 0);
                $c = (float) ($mut?->total_credit ?? 0);

                $extraCredit = 0.0;
                if ($retainedEarningsId && $acc->id === $retainedEarningsId) {
                    $extraCredit = $priorNetProfit;
                }

                if ($d == 0 && $c == 0 && (float) $acc->opening_balance == 0 && $extraCredit == 0) {
                    continue;
                }

                $lineDebit = 0.0;
                $lineCredit = 0.0;

                if ($acc->normal_balance === 'debit') {
                    $debitVal = ($d - $c);
                    if (abs($debitVal) < 0.0001) {
                        continue;
                    }
                    $lineDebit = $debitVal;
                } else {
                    $creditVal = ($c - $d) + $extraCredit;
                    if (abs($creditVal) < 0.0001) {
                        continue;
                    }
                    $lineCredit = $creditVal;
                }

                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'line_no' => $lineIndex++,
                    'account_id' => $acc->id,
                    'unit_id' => $defaultUnitId,
                    'description' => "Saldo Awal {$nextYear} - {$acc->name}",
                    'debit' => $lineDebit,
                    'credit' => $lineCredit,
                ]);
            }

            AuditLogService::record(
                'period.year_end_closing',
                "Tutup Buku Akhir Tahun {$closedYear} & Rollover Saldo Awal ke {$entryNumber}",
                $entry,
                [],
                [
                    'closed_year' => $closedYear,
                    'next_year' => $nextYear,
                    'prior_net_profit_transferred' => $priorNetProfit,
                ]
            );

            return $entry;
        });
    }
}
