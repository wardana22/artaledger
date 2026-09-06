<?php

namespace Database\Seeders;

use App\Domain\Asset\Services\FixedAssetDepreciationService;
use App\Models\AssetCategory;
use App\Models\Company;
use App\Models\FixedAsset;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class FixedAssetSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            return;
        }

        $unit = Unit::where('code', 'KP')->first() ?? Unit::first();
        $user = User::where('email', 'admin@artaledger.com')->first();
        $service = new FixedAssetDepreciationService;

        $catKnd = AssetCategory::where('code', 'KAT-KND')->first();
        $catMsn = AssetCategory::where('code', 'KAT-MSN')->first();
        $catGdg = AssetCategory::where('code', 'KAT-GDG')->first();
        $catTnh = AssetCategory::where('code', 'KAT-TNH')->first();

        $assets = [
            [
                'category' => $catKnd,
                'code' => 'AST-KP-2025-0001',
                'name' => 'Toyota Hilux 2.4 G 4x4 Double Cabin',
                'acquisition_date' => '2025-01-02',
                'acquisition_cost' => 450000000,
                'salvage_value' => 66000000,
                'useful_life_months' => 96,
                'location' => 'Pool Armada Kantor Pusat',
                'person_in_charge' => 'Bambang Triyono (Logistik)',
                'serial_number' => 'MHF21GG88P019283',
                'notes' => 'Kendaraan dinas operasional lapangan.',
            ],
            [
                'category' => $catMsn,
                'code' => 'AST-KP-2025-0002',
                'name' => 'Server Dell PowerEdge R750 Enterprise',
                'acquisition_date' => '2025-01-05',
                'acquisition_cost' => 72000000,
                'salvage_value' => 0,
                'useful_life_months' => 48,
                'location' => 'Data Center Lt. 2',
                'person_in_charge' => 'Farhan Hidayat (IT Admin)',
                'serial_number' => 'ST-98234-JKT',
                'notes' => 'Server database akuntansi dan ERP utama.',
            ],
            [
                'category' => $catGdg,
                'code' => 'AST-KP-2025-0003',
                'name' => 'Gedung Kantor Pusat Graha Arta 3 Lantai',
                'acquisition_date' => '2025-01-01',
                'acquisition_cost' => 2400000000,
                'salvage_value' => 480000000,
                'useful_life_months' => 240,
                'location' => 'Jl. Sudirman Kav. 22',
                'person_in_charge' => 'General Affairs',
                'serial_number' => 'IMB-2024-00918',
                'notes' => 'Bangunan kantor operasional utama.',
            ],
            [
                'category' => $catTnh,
                'code' => 'AST-KP-2025-0004',
                'name' => 'Lahan Tanah Graha Arta 1.500 m2',
                'acquisition_date' => '2025-01-01',
                'acquisition_cost' => 3500000000,
                'salvage_value' => 3500000000,
                'useful_life_months' => 0,
                'location' => 'Jl. Sudirman Kav. 22',
                'person_in_charge' => 'Direksi',
                'serial_number' => 'SHM-No. 1829/2019',
                'notes' => 'Sertifikat Hak Milik (tidak disusutkan).',
            ],
        ];

        foreach ($assets as $a) {
            if (! $a['category']) {
                continue;
            }

            FixedAsset::updateOrCreate(
                ['asset_code' => $a['code']],
                [
                    'company_id' => $company->id,
                    'unit_id' => $unit->id,
                    'asset_category_id' => $a['category']->id,
                    'name' => $a['name'],
                    'serial_number' => $a['serial_number'],
                    'location' => $a['location'],
                    'person_in_charge' => $a['person_in_charge'],
                    'acquisition_date' => $a['acquisition_date'],
                    'acquisition_cost' => $a['acquisition_cost'],
                    'salvage_value' => $a['salvage_value'],
                    'useful_life_months' => $a['useful_life_months'],
                    'depreciation_method' => 'straight_line',
                    'monthly_depreciation_amount' => $service->calculateMonthlyStraightLine($a['acquisition_cost'], $a['salvage_value'], $a['useful_life_months']),
                    'accumulated_depreciation' => 0,
                    'book_value' => $a['acquisition_cost'],
                    'start_depreciation_date' => $a['acquisition_date'],
                    'status' => 'active',
                    'notes' => $a['notes'],
                    'created_by' => $user?->id,
                ]
            );
        }
    }
}
