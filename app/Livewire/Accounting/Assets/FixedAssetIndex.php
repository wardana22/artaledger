<?php

namespace App\Livewire\Accounting\Assets;

use App\Domain\Asset\Services\FixedAssetDepreciationService;
use App\Models\AssetCategory;
use App\Models\Company;
use App\Models\FixedAsset;
use App\Models\Unit;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class FixedAssetIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $selectedCategoryId = '';

    public string $selectedUnitId = '';

    public string $selectedStatus = '';

    public int $perPage = 15;

    // Modal state
    public bool $showAssetModal = false;

    public ?int $editingAssetId = null;

    // Form fields
    public int $unit_id = 1;

    public string $asset_category_id = '';

    public string $asset_code = '';

    public string $name = '';

    public string $serial_number = '';

    public string $location = '';

    public string $person_in_charge = '';

    public string $acquisition_date = '';

    public string $start_depreciation_date = '';

    public string $acquisition_cost = '0';

    public string $salvage_value = '0';

    public int $useful_life_months = 48;

    public string $notes = '';

    // Schedule Drawer/Modal
    public bool $showScheduleModal = false;

    public ?FixedAsset $selectedAssetForSchedule = null;

    public array $scheduleData = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCategoryId' => ['except' => ''],
        'selectedUnitId' => ['except' => ''],
        'selectedStatus' => ['except' => ''],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('assets.view') || auth()->user()?->can('reports.view'), 403, 'Akses Ditolak.');

        $this->acquisition_date = now()->toDateString();
        $this->start_depreciation_date = now()->toDateString();
        $defaultUnit = Unit::first();
        if ($defaultUnit) {
            $this->unit_id = $defaultUnit->id;
        }
        $defaultCat = AssetCategory::first();
        if ($defaultCat) {
            $this->asset_category_id = (string) $defaultCat->id;
            $this->useful_life_months = $defaultCat->useful_life_years * 12;
        }
    }

    public function updatedAssetCategoryId($val): void
    {
        if ($val) {
            $cat = AssetCategory::find($val);
            if ($cat && $cat->useful_life_years > 0) {
                $this->useful_life_months = $cat->useful_life_years * 12;
            }
        }
    }

    public function getEstimatedMonthlyProperty(): float
    {
        $cost = (float) str_replace(['.', ','], ['', '.'], $this->acquisition_cost);
        $salvage = (float) str_replace(['.', ','], ['', '.'], $this->salvage_value);
        if ($this->useful_life_months <= 0) {
            return 0.0;
        }

        return round(max(0, $cost - $salvage) / $this->useful_life_months, 2);
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->editingAssetId = null;
        $this->name = '';
        $this->asset_code = '';
        $this->serial_number = '';
        $this->location = '';
        $this->person_in_charge = '';
        $this->acquisition_date = now()->toDateString();
        $this->start_depreciation_date = now()->toDateString();
        $this->acquisition_cost = '0';
        $this->salvage_value = '0';
        $this->notes = '';

        $cat = AssetCategory::find($this->asset_category_id);
        if ($cat) {
            $this->useful_life_months = $cat->useful_life_years * 12;
        }

        $this->showAssetModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $asset = FixedAsset::findOrFail($id);
        $this->editingAssetId = $asset->id;
        $this->unit_id = $asset->unit_id;
        $this->asset_category_id = (string) $asset->asset_category_id;
        $this->asset_code = $asset->asset_code;
        $this->name = $asset->name;
        $this->serial_number = $asset->serial_number ?? '';
        $this->location = $asset->location ?? '';
        $this->person_in_charge = $asset->person_in_charge ?? '';
        $this->acquisition_date = $asset->acquisition_date->toDateString();
        $this->start_depreciation_date = $asset->start_depreciation_date ? $asset->start_depreciation_date->toDateString() : $asset->acquisition_date->toDateString();
        $this->acquisition_cost = (string) $asset->acquisition_cost;
        $this->salvage_value = (string) $asset->salvage_value;
        $this->useful_life_months = $asset->useful_life_months;
        $this->notes = $asset->notes ?? '';

        $this->showAssetModal = true;
    }

    public function saveAsset(FixedAssetDepreciationService $service): void
    {
        $this->validate([
            'unit_id' => 'required|exists:units,id',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'name' => 'required|string|max:255',
            'acquisition_date' => 'required|date',
            'start_depreciation_date' => 'required|date',
            'acquisition_cost' => 'required|numeric|min:0',
            'salvage_value' => 'nullable|numeric|min:0',
            'useful_life_months' => 'required|integer|min:0',
        ], [
            'name.required' => 'Nama aset wajib diisi.',
            'asset_category_id.required' => 'Kategori aset wajib dipilih.',
            'acquisition_date.required' => 'Tanggal perolehan wajib diisi.',
            'acquisition_cost.required' => 'Harga perolehan wajib diisi.',
        ]);

        $company = Company::first();
        $companyId = $company ? $company->id : 1;

        $cost = (float) $this->acquisition_cost;
        $salvage = (float) $this->salvage_value;
        $usefulMonths = (int) $this->useful_life_months;

        if ($this->editingAssetId) {
            $asset = FixedAsset::findOrFail($this->editingAssetId);
            $monthlyAmount = $service->calculateMonthlyStraightLine($cost, $salvage, $usefulMonths);

            $asset->update([
                'unit_id' => $this->unit_id,
                'asset_category_id' => $this->asset_category_id,
                'name' => $this->name,
                'serial_number' => $this->serial_number ?: null,
                'location' => $this->location ?: null,
                'person_in_charge' => $this->person_in_charge ?: null,
                'acquisition_date' => $this->acquisition_date,
                'start_depreciation_date' => $this->start_depreciation_date,
                'acquisition_cost' => $cost,
                'salvage_value' => $salvage,
                'useful_life_months' => $usefulMonths,
                'monthly_depreciation_amount' => $monthlyAmount,
                'book_value' => max(0, $cost - (float) $asset->accumulated_depreciation),
                'notes' => $this->notes ?: null,
            ]);

            session()->flash('success', 'Data aset tetap berhasil diperbarui.');
        } else {
            $service->createAsset([
                'company_id' => $companyId,
                'unit_id' => $this->unit_id,
                'asset_category_id' => $this->asset_category_id,
                'asset_code' => $this->asset_code ?: null,
                'name' => $this->name,
                'serial_number' => $this->serial_number ?: null,
                'location' => $this->location ?: null,
                'person_in_charge' => $this->person_in_charge ?: null,
                'acquisition_date' => $this->acquisition_date,
                'start_depreciation_date' => $this->start_depreciation_date,
                'acquisition_cost' => $cost,
                'salvage_value' => $salvage,
                'useful_life_months' => $usefulMonths,
                'accumulated_depreciation' => 0,
                'notes' => $this->notes ?: null,
            ], auth()->user());

            session()->flash('success', 'Aset tetap baru berhasil didaftarkan.');
        }

        $this->showAssetModal = false;
        $this->resetPage();
    }

    public function deleteAsset(int $id): void
    {
        $asset = FixedAsset::withCount('depreciations')->findOrFail($id);

        if ($asset->depreciations_count > 0) {
            session()->flash('error', 'Aset tidak dapat dihapus karena sudah memiliki log penyusutan dan jurnal yang diposting.');

            return;
        }

        $asset->delete();
        session()->flash('success', 'Aset berhasil dihapus.');
    }

    public function viewSchedule(int $id, FixedAssetDepreciationService $service): void
    {
        $this->selectedAssetForSchedule = FixedAsset::with(['category', 'unit'])->findOrFail($id);
        $this->scheduleData = $service->generateSchedule($this->selectedAssetForSchedule);
        $this->showScheduleModal = true;
    }

    public function closeScheduleModal(): void
    {
        $this->showScheduleModal = false;
        $this->selectedAssetForSchedule = null;
        $this->scheduleData = [];
    }

    public function render(): View
    {
        $query = FixedAsset::query()->with(['category', 'unit']);

        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('asset_code', 'like', $search)
                    ->orWhere('location', 'like', $search)
                    ->orWhere('person_in_charge', 'like', $search);
            });
        }

        if ($this->selectedCategoryId) {
            $query->where('asset_category_id', $this->selectedCategoryId);
        }

        if ($this->selectedUnitId) {
            $query->where('unit_id', $this->selectedUnitId);
        }

        if ($this->selectedStatus) {
            $query->where('status', $this->selectedStatus);
        }

        $assets = $query->orderBy('acquisition_date', 'desc')->paginate($this->perPage);

        // Overall KPIs
        $totalCost = FixedAsset::sum('acquisition_cost');
        $totalAccum = FixedAsset::sum('accumulated_depreciation');
        $totalBookValue = FixedAsset::sum('book_value');
        $totalActive = FixedAsset::where('status', 'active')->count();

        $categories = AssetCategory::where('is_active', true)->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('livewire.accounting.assets.fixed-asset-index', [
            'assets' => $assets,
            'totalCost' => $totalCost,
            'totalAccum' => $totalAccum,
            'totalBookValue' => $totalBookValue,
            'totalActive' => $totalActive,
            'categories' => $categories,
            'units' => $units,
        ])->layout('layouts.app');
    }
}
