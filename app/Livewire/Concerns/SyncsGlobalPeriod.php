<?php

namespace App\Livewire\Concerns;

use App\Domain\Accounting\Services\GlobalPeriodService;

trait SyncsGlobalPeriod
{
    /**
     * Inisialisasi tanggal awal dan akhir dari sesi periode global.
     */
    public function initializeGlobalPeriod(): void
    {
        $service = app(GlobalPeriodService::class);

        if (property_exists($this, 'startDate') && empty($this->startDate)) {
            $this->startDate = $service->getStartDate();
        }

        if (property_exists($this, 'endDate') && empty($this->endDate)) {
            $this->endDate = $service->getEndDate();
        }

        if (property_exists($this, 'asOfDate') && empty($this->asOfDate)) {
            $this->asOfDate = $service->getAsOfDate();
        }
    }

    /**
     * Hook saat startDate diubah.
     */
    public function updatedStartDate($value): void
    {
        $endDate = property_exists($this, 'endDate') ? $this->endDate : null;
        app(GlobalPeriodService::class)->setPeriod($value, $endDate);

        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    /**
     * Hook saat endDate diubah.
     */
    public function updatedEndDate($value): void
    {
        $startDate = property_exists($this, 'startDate') ? $this->startDate : null;
        app(GlobalPeriodService::class)->setPeriod($startDate, $value);

        if (property_exists($this, 'asOfDate')) {
            $this->asOfDate = $value;
        }

        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    /**
     * Hook saat asOfDate diubah (misal pada Neraca / Aging Report).
     */
    public function updatedAsOfDate($value): void
    {
        $startDate = property_exists($this, 'startDate') ? $this->startDate : null;
        app(GlobalPeriodService::class)->setPeriod($startDate, $value);

        if (property_exists($this, 'endDate')) {
            $this->endDate = $value;
        }

        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }
}
