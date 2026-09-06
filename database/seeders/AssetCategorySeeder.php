<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AssetCategory;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'code' => 'KAT-KND',
                'name' => 'Kendaraan Operasional',
                'useful_life_years' => 8,
                'salvage_percentage' => 0.00,
                'asset_account_code' => '12.01.05',
                'accumulated_account_code' => '12.10.5',
                'expense_account_code' => '68.04',
                'description' => 'Kendaraan bermotor roda 2, roda 4, dan armada angkutan operasional perusahaan.',
            ],
            [
                'code' => 'KAT-MSN',
                'name' => 'Peralatan & Mesin',
                'useful_life_years' => 4,
                'salvage_percentage' => 0.00,
                'asset_account_code' => '12.01.03',
                'accumulated_account_code' => '12.10.3',
                'expense_account_code' => '68.02',
                'description' => 'Mesin produksi, perangkat keras komputer, genset, dan peralatan kantor.',
            ],
            [
                'code' => 'KAT-GDG',
                'name' => 'Gedung & Bangunan',
                'useful_life_years' => 20,
                'salvage_percentage' => 0.00,
                'asset_account_code' => '12.01.02',
                'accumulated_account_code' => '12.10.2',
                'expense_account_code' => '68.01',
                'description' => 'Bangunan kantor pusat, pabrik, gudang, dan pos operasional permanen.',
            ],
            [
                'code' => 'KAT-JAL',
                'name' => 'Jalan & Jaringan',
                'useful_life_years' => 10,
                'salvage_percentage' => 0.00,
                'asset_account_code' => '12.01.04',
                'accumulated_account_code' => '12.10.4',
                'expense_account_code' => '68.03',
                'description' => 'Jalan akses internal, instalasi pipa, dan jaringan listrik.',
            ],
            [
                'code' => 'KAT-TNH',
                'name' => 'Tanah & Hak Milik',
                'useful_life_years' => 0,
                'salvage_percentage' => 0.00,
                'asset_account_code' => '12.01.01',
                'accumulated_account_code' => null,
                'expense_account_code' => null,
                'description' => 'Lahan tanah milik perusahaan (tidak disusutkan).',
            ],
            [
                'code' => 'KAT-AST',
                'name' => 'Aset Tetap Lainnya',
                'useful_life_years' => 4,
                'salvage_percentage' => 0.00,
                'asset_account_code' => '12.02.01',
                'accumulated_account_code' => '12.11',
                'expense_account_code' => '68.05',
                'description' => 'Aset berwujud lainnya yang tidak termasuk dalam kelompok spesifik.',
            ],
        ];

        foreach ($categories as $cat) {
            $assetAccount = $cat['asset_account_code'] ? Account::where('code', $cat['asset_account_code'])->first() : null;
            $accumAccount = $cat['accumulated_account_code'] ? Account::where('code', $cat['accumulated_account_code'])->first() : null;
            $expenseAccount = $cat['expense_account_code'] ? Account::where('code', $cat['expense_account_code'])->first() : null;

            AssetCategory::updateOrCreate(
                ['code' => $cat['code']],
                [
                    'name' => $cat['name'],
                    'useful_life_years' => $cat['useful_life_years'],
                    'salvage_percentage' => $cat['salvage_percentage'],
                    'asset_account_id' => $assetAccount?->id,
                    'accumulated_depreciation_account_id' => $accumAccount?->id,
                    'depreciation_expense_account_id' => $expenseAccount?->id,
                    'description' => $cat['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
