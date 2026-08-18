<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('data/units.json');

        if (! File::exists($jsonPath)) {
            return;
        }

        $items = json_decode(File::get($jsonPath), true);

        if (! is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            Unit::updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'keywords' => $item['keywords'] ?? null,
                ]
            );
        }
    }
}
