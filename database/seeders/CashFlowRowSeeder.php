<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\CashFlowRow;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CashFlowRowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(?int $targetCompanyId = null): void
    {
        if ($targetCompanyId) {
            $companyIds = [$targetCompanyId];
        } else {
            $companies = Company::all();
            if ($companies->isEmpty()) {
                $defaultCompany = Company::firstOrCreate(
                    ['id' => 1],
                    [
                        'name' => 'PT ArtaLedger Indonesia',
                        'code' => 'AL-MAIN',
                        'fiscal_year_start' => 1,
                    ]
                );
                $companyIds = [$defaultCompany->id];
            } else {
                $companyIds = $companies->pluck('id')->toArray();
            }
        }

        foreach ($companyIds as $companyId) {
            $this->seedCashFlowGroupsAndRows($companyId);
        }
    }

    /**
     * Seed groups and default cash flow rows for a specific company.
     */
    public function seedCashFlowGroupsAndRows(int $companyId): void
    {
        // 1. Define groups needed for Cash Flow
        $groupsDef = [
            [
                'code' => 'CF_KAS_BANK',
                'name' => 'KAS & SETARA KAS',
                'description' => 'Akun Kas, Bank, dan Deposito Berjangka Likuid',
                'color_theme' => 'cyan',
                'is_system' => true,
                'prefixes' => ['11.01', '11.02', '11.03'],
            ],
            [
                'code' => 'CF_REV',
                'name' => 'PENDAPATAN ARUS KAS',
                'description' => 'Seluruh akun pendapatan usaha klinik, rumah sakit, dan non-operasional',
                'color_theme' => 'emerald',
                'is_system' => true,
                'prefixes' => ['41', '70'],
            ],
            [
                'code' => 'CF_AR',
                'name' => 'PIUTANG ARUS KAS',
                'description' => 'Akun piutang usaha pihak ketiga, BPJS, berelasi, dan lainnya',
                'color_theme' => 'blue',
                'is_system' => true,
                'prefixes' => ['11.04', '11.05'],
            ],
            [
                'code' => 'CF_SUPPLIER',
                'name' => 'PEMBAYARAN KAS PEMASOK',
                'description' => 'Pelunasan hutang supplier / PBF farmasi dan pembelian BHP operasional',
                'color_theme' => 'amber',
                'is_system' => true,
                'prefixes' => ['21.01', '21.02.02'],
            ],
            [
                'code' => 'CF_PAYROLL',
                'name' => 'PEMBAYARAN KAS KARYAWAN',
                'description' => 'Beban gaji karyawan, honor, imbalan pasca kerja, dan tunjangan operasional',
                'color_theme' => 'purple',
                'is_system' => true,
                'prefixes' => ['51.01', '61.01', '61.02', '61.03', '61.04', '61.05'],
            ],
            [
                'code' => 'CF_INTEREST',
                'name' => 'BEBAN BUNGA & FINANSIAL',
                'description' => 'Beban administrasi bank dan bunga jasa giro/deposito',
                'color_theme' => 'rose',
                'is_system' => true,
                'prefixes' => ['80.01', '80.02'],
            ],
            [
                'code' => 'CF_TAX',
                'name' => 'PANJAR PAJAK PENGHASILAN',
                'description' => 'Panjar pajak dan pembayaran PPh 25, PPh 23, PPh 4 ayat 2',
                'color_theme' => 'orange',
                'is_system' => true,
                'prefixes' => ['11.10.02', '21.04.05', '21.04.03', '21.04.04'],
            ],
            [
                'code' => 'CF_ASSET_LAND',
                'name' => 'PEROLEHAN ASET TANAH',
                'description' => 'Perolehan dan mutasi aset tanah',
                'color_theme' => 'teal',
                'is_system' => true,
                'prefixes' => ['12.01.01'],
            ],
            [
                'code' => 'CF_ASSET_FIXED',
                'name' => 'PEROLEHAN ASET TETAP',
                'description' => 'Gedung, mesin, peralatan medis, inventaris, dan kendaraan',
                'color_theme' => 'indigo',
                'is_system' => true,
                'prefixes' => ['12.01.02', '12.01.03', '12.01.04', '12.01.05', '12.01.06', '12.02.01', '12.06'],
            ],
            [
                'code' => 'CF_ASSET_OTHER',
                'name' => 'PEROLEHAN ASET KEUANGAN LAINNYA',
                'description' => 'Aset tidak berwujud dan beban tangguhan',
                'color_theme' => 'sky',
                'is_system' => true,
                'prefixes' => ['12.03.01', '12.04.01'],
            ],
            [
                'code' => 'CF_LOAN_BANK',
                'name' => 'PEMBAYARAN HUTANG BANK',
                'description' => 'Pelunasan hutang bank jangka panjang dan liabilitas sewa pembiayaan',
                'color_theme' => 'red',
                'is_system' => true,
                'prefixes' => ['22.01', '22.04'],
            ],
            [
                'code' => 'CF_EQUITY_CAPITAL',
                'name' => 'SETORAN MODAL',
                'description' => 'Penyetoran modal saham dan tambahan modal',
                'color_theme' => 'green',
                'is_system' => true,
                'prefixes' => ['31.01'],
            ],
            [
                'code' => 'CF_DIVIDEND',
                'name' => 'PEMBAYARAN DEVIDEN TUNAI',
                'description' => 'Pengeluaran pembagian dividen tunai',
                'color_theme' => 'pink',
                'is_system' => true,
                'prefixes' => ['31.02'],
            ],
        ];

        $groupModels = [];
        foreach ($groupsDef as $gDef) {
            $group = AccountGroup::where('company_id', $companyId)
                ->where(function ($q) use ($gDef) {
                    $q->where('code', $gDef['code'])
                        ->orWhere('name', $gDef['name']);
                })
                ->first();

            if (! $group) {
                $group = AccountGroup::create([
                    'company_id' => $companyId,
                    'code' => $gDef['code'],
                    'name' => $gDef['name'],
                    'description' => $gDef['description'],
                    'color_theme' => $gDef['color_theme'],
                    'is_system' => $gDef['is_system'],
                ]);
            }

            // Populate members
            foreach ($gDef['prefixes'] as $prefix) {
                $accounts = Account::where('company_id', $companyId)
                    ->where('code', 'like', $prefix.'%')
                    ->where('is_group', false)
                    ->get();

                foreach ($accounts as $acc) {
                    DB::table('account_group_members')->updateOrInsert(
                        [
                            'account_group_id' => $group->id,
                            'account_id' => $acc->id,
                        ],
                        [
                            'account_prefix' => $prefix,
                            'account_type' => $acc->type,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }

            $groupModels[$gDef['code']] = $group;
        }

        // 2. Insert Default 11 Cash Flow Rows if table is empty for this company
        $existingRowsCount = CashFlowRow::where('company_id', $companyId)->count();
        if ($existingRowsCount > 0) {
            return;
        }

        $rowsDef = [
            // OPERATING
            [
                'section' => 'operating',
                'label' => 'Penerimaan Kas dr pelanggan',
                'source_type' => 'formula',
                'account_group_id' => $groupModels['CF_REV']->id ?? null,
                'counter_account_group_id' => $groupModels['CF_AR']->id ?? null,
                'calculation_type' => 'net_mutation',
                'formula_expression' => '[GROUP:'.($groupModels['CF_REV']->id ?? 0).'] - [GROUP:'.($groupModels['CF_AR']->id ?? 0).']',
                'operator_sign' => '+',
                'order_index' => 1,
            ],
            [
                'section' => 'operating',
                'label' => 'Pembayaran kas pd pemasok',
                'source_type' => 'account_group',
                'account_group_id' => $groupModels['CF_SUPPLIER']->id ?? null,
                'counter_account_group_id' => null,
                'calculation_type' => 'net_mutation',
                'formula_expression' => null,
                'operator_sign' => '-',
                'order_index' => 2,
            ],
            [
                'section' => 'operating',
                'label' => 'Pembayaran kas pd karyawan',
                'source_type' => 'account_group',
                'account_group_id' => $groupModels['CF_PAYROLL']->id ?? null,
                'counter_account_group_id' => null,
                'calculation_type' => 'net_mutation',
                'formula_expression' => null,
                'operator_sign' => '-',
                'order_index' => 3,
            ],
            [
                'section' => 'operating',
                'label' => 'Pembayaran bunga',
                'source_type' => 'account_group',
                'account_group_id' => $groupModels['CF_INTEREST']->id ?? null,
                'counter_account_group_id' => null,
                'calculation_type' => 'net_mutation',
                'formula_expression' => null,
                'operator_sign' => '-',
                'order_index' => 4,
            ],
            [
                'section' => 'operating',
                'label' => 'Pembayaran panjar pajak penghasilan',
                'source_type' => 'account_group',
                'account_group_id' => $groupModels['CF_TAX']->id ?? null,
                'counter_account_group_id' => null,
                'calculation_type' => 'net_mutation',
                'formula_expression' => null,
                'operator_sign' => '-',
                'order_index' => 5,
            ],

            // INVESTING
            [
                'section' => 'investing',
                'label' => 'Perolehan Aset Tanah',
                'source_type' => 'account_group',
                'account_group_id' => $groupModels['CF_ASSET_LAND']->id ?? null,
                'counter_account_group_id' => null,
                'calculation_type' => 'net_mutation',
                'formula_expression' => null,
                'operator_sign' => '-',
                'order_index' => 1,
            ],
            [
                'section' => 'investing',
                'label' => 'Perolehan Aset Tetap',
                'source_type' => 'account_group',
                'account_group_id' => $groupModels['CF_ASSET_FIXED']->id ?? null,
                'counter_account_group_id' => null,
                'calculation_type' => 'net_mutation',
                'formula_expression' => null,
                'operator_sign' => '-',
                'order_index' => 2,
            ],
            [
                'section' => 'investing',
                'label' => 'Perolehan Aset Keuangan Lancar Lainnya',
                'source_type' => 'account_group',
                'account_group_id' => $groupModels['CF_ASSET_OTHER']->id ?? null,
                'counter_account_group_id' => null,
                'calculation_type' => 'net_mutation',
                'formula_expression' => null,
                'operator_sign' => '-',
                'order_index' => 3,
            ],

            // FINANCING
            [
                'section' => 'financing',
                'label' => 'Pembayaran Hutang Kepada Bank',
                'source_type' => 'account_group',
                'account_group_id' => $groupModels['CF_LOAN_BANK']->id ?? null,
                'counter_account_group_id' => null,
                'calculation_type' => 'net_mutation',
                'formula_expression' => null,
                'operator_sign' => '-',
                'order_index' => 1,
            ],
            [
                'section' => 'financing',
                'label' => 'Setoran Modal',
                'source_type' => 'account_group',
                'account_group_id' => $groupModels['CF_EQUITY_CAPITAL']->id ?? null,
                'counter_account_group_id' => null,
                'calculation_type' => 'net_mutation',
                'formula_expression' => null,
                'operator_sign' => '+',
                'order_index' => 2,
            ],
            [
                'section' => 'financing',
                'label' => 'Pembayaran deviden tunai',
                'source_type' => 'account_group',
                'account_group_id' => $groupModels['CF_DIVIDEND']->id ?? null,
                'counter_account_group_id' => null,
                'calculation_type' => 'net_mutation',
                'formula_expression' => null,
                'operator_sign' => '-',
                'order_index' => 3,
            ],
        ];

        foreach ($rowsDef as $row) {
            CashFlowRow::create(array_merge($row, [
                'company_id' => $companyId,
                'is_active' => true,
            ]));
        }
    }
}
