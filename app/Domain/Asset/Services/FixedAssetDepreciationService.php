<?php

namespace App\Domain\Asset\Services;

use App\Models\AccountingPeriod;
use App\Models\AssetCategory;
use App\Models\AssetDepreciation;
use App\Models\FixedAsset;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\JournalType;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FixedAssetDepreciationService
{
    /**
     * Hitung nilai penyusutan garis lurus bulanan.
     */
    public function calculateMonthlyStraightLine(float $acquisitionCost, float $salvageValue, int $usefulLifeMonths): float
    {
        if ($usefulLifeMonths <= 0) {
            return 0.00;
        }

        $depreciableBase = max(0, $acquisitionCost - $salvageValue);

        return round($depreciableBase / $usefulLifeMonths, 2);
    }

    /**
     * Buat aset tetap baru dengan kalkulasi otomatis.
     */
    public function createAsset(array $data, ?User $user = null): FixedAsset
    {
        $category = AssetCategory::findOrFail($data['asset_category_id']);
        $cost = (float) ($data['acquisition_cost'] ?? 0);
        $salvage = (float) ($data['salvage_value'] ?? 0);
        $usefulMonths = (int) ($data['useful_life_months'] ?? ($category->useful_life_years * 12));

        $monthlyAmount = $this->calculateMonthlyStraightLine($cost, $salvage, $usefulMonths);
        $accumulated = (float) ($data['accumulated_depreciation'] ?? 0);
        $bookValue = max(0, $cost - $accumulated);

        $unit = Unit::find($data['unit_id']);
        $unitCode = $unit ? $unit->code : 'GEN';
        $year = Carbon::parse($data['acquisition_date'])->format('Y');

        if (empty($data['asset_code'])) {
            $count = FixedAsset::whereYear('acquisition_date', $year)->count() + 1;
            $data['asset_code'] = sprintf('AST-%s-%s-%04d', $unitCode, $year, $count);
        }

        $status = 'active';
        if ($usefulMonths <= 0 && $salvage == 0) {
            // Aset non-depresiasi seperti tanah
            $status = 'active';
        } elseif ($bookValue <= $salvage && $cost > 0) {
            $status = 'fully_depreciated';
        }

        return FixedAsset::create([
            'company_id' => $data['company_id'],
            'unit_id' => $data['unit_id'],
            'asset_category_id' => $category->id,
            'asset_code' => $data['asset_code'],
            'name' => $data['name'],
            'serial_number' => $data['serial_number'] ?? null,
            'location' => $data['location'] ?? null,
            'person_in_charge' => $data['person_in_charge'] ?? null,
            'acquisition_date' => $data['acquisition_date'],
            'acquisition_cost' => $cost,
            'salvage_value' => $salvage,
            'useful_life_months' => $usefulMonths,
            'depreciation_method' => $data['depreciation_method'] ?? 'straight_line',
            'monthly_depreciation_amount' => $monthlyAmount,
            'accumulated_depreciation' => $accumulated,
            'book_value' => $bookValue,
            'start_depreciation_date' => $data['start_depreciation_date'] ?? $data['acquisition_date'],
            'last_depreciated_period' => $data['last_depreciated_period'] ?? null,
            'status' => $status,
            'notes' => $data['notes'] ?? null,
            'created_by' => $user?->id,
        ]);
    }

    /**
     * Hasilkan jadwal penyusutan (Amortization Schedule) dari aset.
     */
    public function generateSchedule(FixedAsset $asset): array
    {
        if ($asset->useful_life_months <= 0 || $asset->monthly_depreciation_amount <= 0) {
            return [];
        }

        $schedule = [];
        $cost = (float) $asset->acquisition_cost;
        $salvage = (float) $asset->salvage_value;
        $depreciableTotal = max(0, $cost - $salvage);
        $monthly = (float) $asset->monthly_depreciation_amount;
        $totalMonths = $asset->useful_life_months;

        $startDate = $asset->start_depreciation_date ? Carbon::parse($asset->start_depreciation_date) : Carbon::parse($asset->acquisition_date);
        $existingDepreciations = $asset->depreciations()->pluck('period')->toArray();

        $runningAccumulated = 0;

        for ($m = 1; $m <= $totalMonths; $m++) {
            $periodDate = (clone $startDate)->addMonths($m - 1);
            $periodStr = $periodDate->format('Y-m');

            if ($m === $totalMonths) {
                // Bulan terakhir mengambil sisa nilai tersusutkan secara presisi
                $periodAmount = max(0, round($depreciableTotal - $runningAccumulated, 2));
            } else {
                $periodAmount = min($monthly, max(0, round($depreciableTotal - $runningAccumulated, 2)));
            }

            $runningAccumulated += $periodAmount;
            $bookValue = max($salvage, round($cost - $runningAccumulated, 2));

            $schedule[] = [
                'month_no' => $m,
                'period' => $periodStr,
                'date' => $periodDate->endOfMonth()->toDateString(),
                'depreciation_amount' => $periodAmount,
                'accumulated_depreciation' => $runningAccumulated,
                'book_value' => $bookValue,
                'is_posted' => in_array($periodStr, $existingDepreciations),
            ];

            if ($runningAccumulated >= $depreciableTotal) {
                break;
            }
        }

        return $schedule;
    }

    /**
     * Dapatkan daftar aset aktif yang belum disusutkan untuk periode tertentu (YYYY-MM).
     */
    public function getPendingAssetsForPeriod(string $period, ?int $unitId = null, ?int $companyId = null): Collection
    {
        $periodDate = Carbon::createFromFormat('Y-m', $period)->endOfMonth();

        $query = FixedAsset::query()
            ->with(['category.assetAccount', 'category.accumulatedDepreciationAccount', 'category.depreciationExpenseAccount', 'unit', 'company'])
            ->where('status', 'active')
            ->where('useful_life_months', '>', 0)
            ->where('monthly_depreciation_amount', '>', 0)
            ->where('start_depreciation_date', '<=', $periodDate->toDateString())
            ->whereColumn('book_value', '>', 'salvage_value')
            ->whereDoesntHave('depreciations', function ($q) use ($period) {
                $q->where('period', $period);
            });

        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->get();
    }

    /**
     * Eksekusi penyusutan bulanan untuk suatu periode (YYYY-MM) dan posting jurnal otomatis.
     */
    public function runDepreciationForPeriod(string $period, ?int $unitId = null, ?User $user = null): array
    {
        return DB::transaction(function () use ($period, $unitId, $user) {
            $assets = $this->getPendingAssetsForPeriod($period, $unitId);

            if ($assets->isEmpty()) {
                return [
                    'success' => false,
                    'message' => "Tidak ada aset aktif yang memerlukan penyusutan untuk periode {$period}.",
                    'count' => 0,
                    'total_amount' => 0,
                    'journal_entries' => [],
                ];
            }

            $depreciationDate = Carbon::createFromFormat('Y-m', $period)->endOfMonth()->toDateString();
            $jpenType = JournalType::where('code', 'JPEN')->first();
            $jpenTypeId = $jpenType?->id ?? 29;

            $totalProcessed = 0;
            $totalAmountAll = 0.0;
            $createdJournals = [];

            // Kelompokkan aset berdasarkan company_id dan unit_id untuk efisiensi jurnal
            $grouped = $assets->groupBy(function ($asset) {
                return $asset->company_id.'-'.$asset->unit_id;
            });

            foreach ($grouped as $groupKey => $groupAssets) {
                $firstAsset = $groupAssets->first();
                $companyId = $firstAsset->company_id;
                $currentUnitId = $firstAsset->unit_id;
                $unitObj = $firstAsset->unit;

                // Cari accounting period
                $accPeriod = AccountingPeriod::where('company_id', $companyId)
                    ->where('year', Carbon::parse($depreciationDate)->year)
                    ->where('month', Carbon::parse($depreciationDate)->month)
                    ->first();

                $entryNumber = sprintf('DEP-%s-%s-%04d', str_replace('-', '', $period), $unitObj?->code ?? 'KP', rand(100, 999));
                $docNumber = sprintf('AST-DEP-%s-%s', $period, $unitObj?->code ?? 'KP');
                $desc = sprintf('Penyusutan Aset Tetap Periode %s (%s)', $period, $unitObj?->name ?? 'Kantor Pusat');

                $journalEntry = JournalEntry::create([
                    'company_id' => $companyId,
                    'period_id' => $accPeriod?->id,
                    'journal_type_id' => $jpenTypeId,
                    'entry_number' => $entryNumber,
                    'document_number' => $docNumber,
                    'entry_date' => $depreciationDate,
                    'entry_type' => 'adjustment',
                    'source_type' => 'depreciation',
                    'description' => $desc,
                    'status' => 'posted',
                    'posted_at' => now(),
                    'posted_by' => $user?->id,
                    'created_by' => $user?->id,
                ]);

                $linesByExpense = [];
                $linesByAccum = [];

                foreach ($groupAssets as $asset) {
                    $category = $asset->category;

                    // Pastikan kategori memiliki akun beban dan akun akumulasi
                    $expenseAccountId = $category->depreciation_expense_account_id;
                    $accumAccountId = $category->accumulated_depreciation_account_id;

                    if (! $expenseAccountId || ! $accumAccountId) {
                        continue;
                    }

                    // Hitung nominal penyusutan bulan ini
                    $remainingDepreciable = max(0, (float) $asset->book_value - (float) $asset->salvage_value);
                    $amount = min((float) $asset->monthly_depreciation_amount, $remainingDepreciable);

                    if ($amount <= 0) {
                        continue;
                    }

                    $newAccumulated = round((float) $asset->accumulated_depreciation + $amount, 2);
                    $newBookValue = max((float) $asset->salvage_value, round((float) $asset->book_value - $amount, 2));

                    // Log logistik depresiasi
                    AssetDepreciation::create([
                        'fixed_asset_id' => $asset->id,
                        'journal_entry_id' => $journalEntry->id,
                        'period' => $period,
                        'depreciation_date' => $depreciationDate,
                        'amount' => $amount,
                        'accumulated_to_date' => $newAccumulated,
                        'book_value_after' => $newBookValue,
                        'posted_by' => $user?->id,
                        'posted_at' => now(),
                    ]);

                    // Perbarui master aset
                    $asset->accumulated_depreciation = $newAccumulated;
                    $asset->book_value = $newBookValue;
                    $asset->last_depreciated_period = $period;

                    if ($newBookValue <= (float) $asset->salvage_value) {
                        $asset->status = 'fully_depreciated';
                    }

                    $asset->save();

                    // Akumulasi baris jurnal
                    $linesByExpense[$expenseAccountId] = ($linesByExpense[$expenseAccountId] ?? 0) + $amount;
                    $linesByAccum[$accumAccountId] = ($linesByAccum[$accumAccountId] ?? 0) + $amount;

                    $totalProcessed++;
                    $totalAmountAll += $amount;
                }

                // Buat JournalLine Debit (Beban Penyusutan)
                $lineNo = 1;
                foreach ($linesByExpense as $accId => $debitAmount) {
                    JournalLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $accId,
                        'unit_id' => $currentUnitId,
                        'line_no' => $lineNo++,
                        'debit' => $debitAmount,
                        'credit' => 0,
                        'description' => $desc,
                    ]);
                }

                // Buat JournalLine Kredit (Akumulasi Penyusutan)
                foreach ($linesByAccum as $accId => $creditAmount) {
                    JournalLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $accId,
                        'unit_id' => $currentUnitId,
                        'line_no' => $lineNo++,
                        'debit' => 0,
                        'credit' => $creditAmount,
                        'description' => $desc,
                    ]);
                }

                $createdJournals[] = $journalEntry->entry_number;
            }

            return [
                'success' => true,
                'message' => sprintf('Berhasil menyusutkan %d aset dengan total beban Rp %s dan membukukan jurnal otomatis.', $totalProcessed, number_format($totalAmountAll, 0, ',', '.')),
                'count' => $totalProcessed,
                'total_amount' => $totalAmountAll,
                'journal_entries' => $createdJournals,
            ];
        });
    }
}
