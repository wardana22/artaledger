<?php

namespace Database\Seeders;

use App\Models\JournalType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class JournalTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('data/journal_types.json');

        if (! File::exists($jsonPath)) {
            return;
        }

        $items = json_decode(File::get($jsonPath), true);

        if (! is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            JournalType::updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                ]
            );
        }
    }
}
