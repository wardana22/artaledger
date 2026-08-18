<?php

namespace Database\Seeders;

use App\Models\AccountingPeriod;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AccountingPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds for 2025 and 2026.
     */
    public function run(): void
    {
        $company = Company::first() ?? Company::create([
            'name' => 'PT Arta Ledger',
            'code' => 'AL',
        ]);

        $years = [2025, 2026];

        foreach ($years as $year) {
            for ($month = 1; $month <= 12; $month++) {
                $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
                $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

                AccountingPeriod::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'year' => $year,
                        'month' => $month,
                    ],
                    [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'status' => 'open',
                    ]
                );
            }
        }
    }
}
