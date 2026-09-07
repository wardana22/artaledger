<?php

namespace Database\Seeders;

use App\Domain\Dashboard\Services\DashboardMetricService;
use App\Models\Company;
use Illuminate\Database\Seeder;

class DashboardKpiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(?int $companyId = null): void
    {
        $service = app(DashboardMetricService::class);

        if ($companyId) {
            $companyIds = [$companyId];
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

        foreach ($companyIds as $cId) {
            $service->seedDefaultKpisAndCharts($cId);
        }
    }
}
