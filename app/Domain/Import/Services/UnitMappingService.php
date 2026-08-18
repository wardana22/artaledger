<?php

namespace App\Domain\Import\Services;

use App\Models\Unit;
use Illuminate\Support\Collection;

class UnitMappingService
{
    protected ?Collection $units = null;

    public function __construct()
    {
        $this->units = Unit::all();
    }

    /**
     * Match text (e.g. description/reference) against unit keywords.
     * Fallback to Unit KP (Kantor Pusat) if no keyword matches.
     */
    public function detectUnitId(?string $text): ?int
    {
        if ($this->units->isEmpty()) {
            return null;
        }

        if (! empty($text)) {
            $upperText = strtoupper($text);

            foreach ($this->units as $unit) {
                if (empty($unit->keywords)) {
                    if (str_contains($upperText, strtoupper($unit->code))) {
                        return $unit->id;
                    }

                    continue;
                }

                $keywords = array_map('trim', explode(',', strtoupper($unit->keywords)));

                foreach ($keywords as $kw) {
                    if ($kw !== '' && str_contains($upperText, $kw)) {
                        return $unit->id;
                    }
                }
            }
        }

        // Default fallback: Jika pada kolom O tidak ada keyword Unit maka otomatis menjadi Unit KP (Kantor Pusat)
        $kpUnit = $this->units->firstWhere('code', 'KP') ?? $this->units->first();

        return $kpUnit?->id;
    }
}
