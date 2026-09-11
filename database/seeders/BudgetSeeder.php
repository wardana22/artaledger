<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Budget;
use App\Models\BudgetLine;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetSeeder extends Seeder
{
    /**
     * Run the database seeds for Budgeting & Cost Control.
     */
    public function run(): void
    {
        $admin = User::first();
        $adminId = $admin?->id;
        $currentYear = (int) date('Y');

        // Fetch postable expense accounts (5% and 6%)
        $expenseAccounts = Account::posting()
            ->where(function ($q) {
                $q->where('type', 'BEBAN')
                    ->orWhere('report_type', 'laba_rugi')
                    ->orWhere('code', 'like', '5%')
                    ->orWhere('code', 'like', '6%');
            })
            ->orderBy('code', 'asc')
            ->take(12)
            ->get();

        if ($expenseAccounts->isEmpty()) {
            $this->command?->warn('Tidak ada akun beban posting yang ditemukan untuk seeding anggaran.');

            return;
        }

        $units = Unit::all();
        $firstUnitId = $units->first()?->id;

        DB::transaction(function () use ($adminId, $currentYear, $expenseAccounts, $firstUnitId) {
            $samplePlafons = [
                120000000, // 120 Juta / thn
                240000000, // 240 Juta / thn
                60000000,  // 60 Juta / thn
                48000000,  // 48 Juta / thn
                96000000,  // 96 Juta / thn
                180000000, // 180 Juta / thn
                36000000,  // 36 Juta / thn
                72000000,  // 72 Juta / thn
                150000000, // 150 Juta / thn
                84000000,  // 84 Juta / thn
                54000000,  // 54 Juta / thn
                300000000, // 300 Juta / thn
            ];

            // 1. Master Anggaran Tahun 2025 (Tutup Buku / Historis)
            $budget2025 = Budget::updateOrCreate(
                [
                    'fiscal_year' => 2025,
                    'name' => 'Rencana Kerja & Anggaran Biaya (RKAB) 2025',
                ],
                [
                    'description' => 'Realisasi dan plafon anggaran operasional tahun buku 2025.',
                    'status' => 'active',
                    'enforcement_mode' => 'warning_only',
                    'warning_threshold_pct' => 80.00,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]
            );

            $budget2025->lines()->delete();

            foreach ($expenseAccounts as $index => $acc) {
                // Skala pagu 2025 (~90% dari plafon dasar)
                $annualAmount2025 = round($samplePlafons[$index % count($samplePlafons)] * 0.90, -5);
                $unitId = ($index % 3 === 0) ? $firstUnitId : null;

                $line = new BudgetLine([
                    'budget_id' => $budget2025->id,
                    'account_id' => $acc->id,
                    'unit_id' => $unitId,
                    'annual_amount' => $annualAmount2025,
                    'notes' => "Alokasi anggaran 2025 {$acc->name}",
                ]);

                $line->distributeEvenly();
                $line->save();
            }

            // 2. Master Anggaran Aktif Tahun Berjalan (Tahun ini)
            $activeBudget = Budget::updateOrCreate(
                [
                    'fiscal_year' => $currentYear,
                    'name' => "Rencana Kerja & Anggaran Biaya (RKAB) {$currentYear}",
                ],
                [
                    'description' => "Plafon anggaran operasional dan kontrol biaya resmi tahun buku {$currentYear}.",
                    'status' => 'active',
                    'enforcement_mode' => 'warning_only',
                    'warning_threshold_pct' => 80.00,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]
            );

            // Bersihkan baris lama agar idempotent
            $activeBudget->lines()->delete();

            foreach ($expenseAccounts as $index => $acc) {
                $annualAmount = $samplePlafons[$index % count($samplePlafons)];
                $unitId = ($index % 3 === 0) ? $firstUnitId : null; // Sebagian spesifik unit, sebagian konsolidasi

                $line = new BudgetLine([
                    'budget_id' => $activeBudget->id,
                    'account_id' => $acc->id,
                    'unit_id' => $unitId,
                    'annual_amount' => $annualAmount,
                    'notes' => "Alokasi anggaran {$acc->name}",
                ]);

                // Bagi rata ke 12 bulan
                $line->distributeEvenly();
                $line->save();
            }

            // 3. Master Anggaran Draft untuk Tahun Depan
            $nextYear = $currentYear + 1;
            $draftBudget = Budget::updateOrCreate(
                [
                    'fiscal_year' => $nextYear,
                    'name' => "Rancangan Anggaran Operasional {$nextYear} (Draft)",
                ],
                [
                    'description' => "Rancangan plafon pengeluaran untuk proyeksi tahun fiskal {$nextYear}.",
                    'status' => 'draft',
                    'enforcement_mode' => 'warning_only',
                    'warning_threshold_pct' => 85.00,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]
            );

            $draftBudget->lines()->delete();

            foreach ($expenseAccounts->take(6) as $index => $acc) {
                $annualAmount = round($samplePlafons[$index] * 1.10, -5); // Kenaikan 10%

                $line = new BudgetLine([
                    'budget_id' => $draftBudget->id,
                    'account_id' => $acc->id,
                    'unit_id' => null,
                    'annual_amount' => $annualAmount,
                    'notes' => "Proyeksi plafon RKAB {$nextYear}",
                ]);

                $line->distributeEvenly();
                $line->save();
            }
        });

        $this->command?->info("Seeding anggaran berhasil: Dibuat Anggaran Tahun 2025, Anggaran Aktif ({$currentYear}), dan Anggaran Draft (".($currentYear + 1).').');
    }
}
