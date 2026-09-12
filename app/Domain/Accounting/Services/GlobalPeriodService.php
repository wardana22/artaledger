<?php

namespace App\Domain\Accounting\Services;

use Carbon\Carbon;

class GlobalPeriodService
{
    public const SESSION_START_KEY = 'accounting_global_period_start';

    public const SESSION_END_KEY = 'accounting_global_period_end';

    /**
     * Dapatkan tanggal awal periode aktif.
     * Default: 1 Januari tahun kalender berjalan (misal 2026-01-01).
     */
    public function getStartDate(): string
    {
        $sessionStart = session()->get(self::SESSION_START_KEY);

        if (! empty($sessionStart)) {
            return $sessionStart;
        }

        return Carbon::now()->startOfYear()->format('Y-m-d');
    }

    /**
     * Dapatkan tanggal akhir periode aktif.
     * Default: Tanggal hari ini atau 31 Desember tahun kalender berjalan.
     */
    public function getEndDate(): string
    {
        $sessionEnd = session()->get(self::SESSION_END_KEY);

        if (! empty($sessionEnd)) {
            return $sessionEnd;
        }

        return Carbon::now()->endOfYear()->format('Y-m-d');
    }

    /**
     * Dapatkan tanggal cut-off acuan (As Of Date) untuk Neraca & Aging Report.
     */
    public function getAsOfDate(): string
    {
        return $this->getEndDate();
    }

    /**
     * Simpan rentang tanggal periode baru ke session global.
     */
    public function setPeriod(?string $startDate, ?string $endDate): void
    {
        if ($startDate) {
            session()->put(self::SESSION_START_KEY, $startDate);
        }

        if ($endDate) {
            session()->put(self::SESSION_END_KEY, $endDate);
        }
    }

    /**
     * Reset sesi periode global ke default awal tahun kalender berjalan.
     */
    public function resetPeriod(): void
    {
        session()->forget([self::SESSION_START_KEY, self::SESSION_END_KEY]);
    }
}
