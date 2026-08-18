<?php

namespace Database\Seeders;

use App\Domain\Accounting\Services\AccountSeederService;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service = new AccountSeederService;
        $service->seedFromData();
    }
}
