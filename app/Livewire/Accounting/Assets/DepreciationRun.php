<?php

namespace App\Livewire\Accounting\Assets;

use App\Domain\Asset\Services\FixedAssetDepreciationService;
use App\Models\AssetDepreciation;
use App\Models\Unit;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class DepreciationRun extends Component
{
    public string $period = '';

    public string $unit_id = '';

    public bool $showConfirmModal = false;

    public bool $isExecuting = false;

    public ?array $executionResult = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('assets.depreciate') || auth()->user()?->can('reports.view'), 403, 'Akses Ditolak.');

        // Default ke bulan berjalan atau Januari 2025 jika data awal dimulai Januari 2025
        $this->period = '2025-01';
    }

    public function openConfirmModal(): void
    {
        $this->showConfirmModal = true;
    }

    public function executeDepreciation(FixedAssetDepreciationService $service): void
    {
        abort_unless(
            auth()->user()?->can('assets.depreciate') ||
            auth()->user()?->hasRole('Super Admin'),
            403,
            'Akses Ditolak. Anda tidak memiliki izin untuk mengeksekusi penyusutan aset.'
        );

        $this->isExecuting = true;
        $unitId = $this->unit_id ? (int) $this->unit_id : null;

        $result = $service->runDepreciationForPeriod($this->period, $unitId, auth()->user());
        $this->executionResult = $result;
        $this->showConfirmModal = false;
        $this->isExecuting = false;

        if ($result['success']) {
            session()->flash('success', $result['message']);
        } else {
            session()->flash('warning', $result['message']);
        }
    }

    public function render(FixedAssetDepreciationService $service): View
    {
        $unitId = $this->unit_id ? (int) $this->unit_id : null;
        $pendingAssets = $service->getPendingAssetsForPeriod($this->period, $unitId);

        $totalPendingAmount = $pendingAssets->sum(function ($asset) {
            $remaining = max(0, (float) $asset->book_value - (float) $asset->salvage_value);

            return min((float) $asset->monthly_depreciation_amount, $remaining);
        });

        // Riwayat Eksekusi Penyusutan: Dikelompokkan per period dan journal_entry_id
        $historyRuns = AssetDepreciation::with(['journalEntry', 'postedBy', 'fixedAsset.unit'])
            ->select('period', 'journal_entry_id', 'posted_by', 'posted_at')
            ->selectRaw('COUNT(*) as total_assets')
            ->selectRaw('SUM(amount) as total_depreciation')
            ->groupBy('period', 'journal_entry_id', 'posted_by', 'posted_at')
            ->orderBy('period', 'desc')
            ->orderBy('posted_at', 'desc')
            ->take(15)
            ->get();

        $units = Unit::orderBy('name')->get();

        // Buat daftar opsi periode (12 bulan ke belakang sampai 12 bulan ke depan)
        $periods = [];
        $currentYear = 2025;
        for ($m = 1; $m <= 12; $m++) {
            $periods[] = sprintf('%04d-%02d', $currentYear, $m);
        }
        for ($m = 1; $m <= 12; $m++) {
            $periods[] = sprintf('%04d-%02d', 2026, $m);
        }

        return view('livewire.accounting.assets.depreciation-run', [
            'pendingAssets' => $pendingAssets,
            'totalPendingAmount' => $totalPendingAmount,
            'historyRuns' => $historyRuns,
            'units' => $units,
            'periods' => $periods,
        ])->layout('layouts.app');
    }
}
