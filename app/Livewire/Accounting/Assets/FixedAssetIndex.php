<?php

namespace App\Livewire\Accounting\Assets;

use App\Domain\Asset\Services\FixedAssetDepreciationService;
use App\Models\Account;
use App\Models\AssetCategory;
use App\Models\Company;
use App\Models\FixedAsset;
use App\Models\Unit;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class FixedAssetIndex extends Component
{
    use WithFileUploads, WithPagination;

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

    // Photo upload
    public $photo = null;

    public ?string $existingPhotoPath = null;

    // Schedule Drawer/Modal
    public bool $showScheduleModal = false;

    public ?FixedAsset $selectedAssetForSchedule = null;

    public array $scheduleData = [];

    // Label / Barcode Modal
    public bool $showLabelModal = false;

    public ?int $labelAssetId = null;

    public array $labelAssetData = [];

    // Batch Label Modal
    public bool $showBatchLabelModal = false;

    public array $batchLabelData = [];

    // Category Management Modals
    public bool $showCategoryManagerModal = false;

    public bool $showCategoryFormModal = false;

    public ?int $editingCategoryId = null;

    // Category Form Fields
    public string $cat_code = '';

    public string $cat_name = '';

    public int $cat_useful_life_years = 4;

    public string $cat_salvage_percentage = '0.00';

    public string $cat_asset_account_id = '';

    public string $cat_accumulated_account_id = '';

    public string $cat_expense_account_id = '';

    public string $cat_description = '';

    public bool $cat_is_active = true;

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
        $this->photo = null;
        $this->existingPhotoPath = null;

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
        $this->photo = null;
        $this->existingPhotoPath = $asset->photo_path;

        $this->showAssetModal = true;
    }

    public function saveAsset(FixedAssetDepreciationService $service): void
    {
        if ($this->editingAssetId) {
            abort_unless(auth()->user()?->can('assets.edit'), 403, 'Akses Ditolak: Anda tidak memiliki izin mengedit Aset Tetap.');
        } else {
            abort_unless(auth()->user()?->can('assets.create'), 403, 'Akses Ditolak: Anda tidak memiliki izin menambah Aset Tetap.');
        }

        $this->validate([
            'unit_id' => 'required|exists:units,id',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'name' => 'required|string|max:255',
            'acquisition_date' => 'required|date',
            'start_depreciation_date' => 'required|date',
            'acquisition_cost' => 'required|numeric|min:0',
            'salvage_value' => 'nullable|numeric|min:0',
            'useful_life_months' => 'required|integer|min:0',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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

            // Handle photo upload on edit
            $photoPath = $asset->photo_path;
            if ($this->photo) {
                if ($photoPath) {
                    \Storage::disk('public')->delete($photoPath);
                }
                $photoPath = $this->photo->store('assets/photos', 'public');
            }

            $asset->update([
                'unit_id' => $this->unit_id,
                'asset_category_id' => $this->asset_category_id,
                'name' => $this->name,
                'serial_number' => $this->serial_number ?: null,
                'photo_path' => $photoPath,
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
            // Handle photo upload on create
            $photoPath = null;
            if ($this->photo) {
                $photoPath = $this->photo->store('assets/photos', 'public');
            }

            $service->createAsset([
                'company_id' => $companyId,
                'unit_id' => $this->unit_id,
                'asset_category_id' => $this->asset_category_id,
                'asset_code' => $this->asset_code ?: null,
                'name' => $this->name,
                'serial_number' => $this->serial_number ?: null,
                'photo_path' => $photoPath,
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
        abort_unless(auth()->user()?->can('assets.delete'), 403, 'Akses Ditolak: Anda tidak memiliki izin menghapus Aset Tetap.');

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

    public function openLabelModal(int $id): void
    {
        $asset = FixedAsset::with(['category', 'unit'])->findOrFail($id);
        $this->labelAssetId = $id;
        $this->labelAssetData = [
            'code' => $asset->asset_code,
            'name' => $asset->name,
            'category' => $asset->category->name,
            'unit' => $asset->unit->name,
            'location' => $asset->location ?? '-',
            'serial' => $asset->serial_number ?? '-',
            'pic' => $asset->person_in_charge ?? '-',
            'acquisition' => $asset->acquisition_date->format('d/m/Y'),
            'scan_url' => route('assets.scan.public', ['code' => $asset->asset_code]),
            'status' => match ($asset->status) {
                'active' => 'Aktif',
                'fully_depreciated' => 'Habis Disusutkan',
                'disposed' => 'Dilepas',
                default => $asset->status,
            },
        ];
        $this->showLabelModal = true;
        $this->dispatch('labelModalOpened', data: $this->labelAssetData);
    }

    public function closeLabelModal(): void
    {
        $this->showLabelModal = false;
        $this->labelAssetId = null;
        $this->labelAssetData = [];
    }

    public function openBatchLabelModal(): void
    {
        $query = FixedAsset::query()->with(['category', 'unit']);

        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('asset_code', 'like', $search)
                    ->orWhere('location', 'like', $search);
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

        $assets = $query->orderBy('asset_code')->get();

        $this->batchLabelData = $assets->map(fn ($a) => [
            'code' => $a->asset_code,
            'name' => $a->name,
            'category' => $a->category->name,
            'unit' => $a->unit->name,
            'location' => $a->location ?? '-',
            'serial' => $a->serial_number ?? '-',
            'pic' => $a->person_in_charge ?? '-',
            'acquisition' => $a->acquisition_date ? $a->acquisition_date->format('d/m/Y') : '-',
            'scan_url' => route('assets.scan.public', ['code' => $a->asset_code]),
        ])->toArray();

        $this->showBatchLabelModal = true;
        $this->dispatch('batchLabelModalOpened', items: $this->batchLabelData);
    }

    public function closeBatchLabelModal(): void
    {
        $this->showBatchLabelModal = false;
        $this->batchLabelData = [];
    }

    public function openCategoryManagerModal(): void
    {
        $this->showCategoryManagerModal = true;
    }

    public function closeCategoryManagerModal(): void
    {
        $this->showCategoryManagerModal = false;
    }

    public function openCreateCategoryModal(): void
    {
        $this->resetValidation();
        $this->editingCategoryId = null;
        $this->cat_code = '';
        $this->cat_name = '';
        $this->cat_useful_life_years = 4;
        $this->cat_salvage_percentage = '0.00';
        $this->cat_asset_account_id = '';
        $this->cat_accumulated_account_id = '';
        $this->cat_expense_account_id = '';
        $this->cat_description = '';
        $this->cat_is_active = true;

        $this->showCategoryFormModal = true;
    }

    public function openEditCategoryModal(int $id): void
    {
        $this->resetValidation();
        $cat = AssetCategory::findOrFail($id);
        $this->editingCategoryId = $cat->id;
        $this->cat_code = $cat->code;
        $this->cat_name = $cat->name;
        $this->cat_useful_life_years = (int) $cat->useful_life_years;
        $this->cat_salvage_percentage = (string) $cat->salvage_percentage;
        $this->cat_asset_account_id = (string) ($cat->asset_account_id ?? '');
        $this->cat_accumulated_account_id = (string) ($cat->accumulated_depreciation_account_id ?? '');
        $this->cat_expense_account_id = (string) ($cat->depreciation_expense_account_id ?? '');
        $this->cat_description = $cat->description ?? '';
        $this->cat_is_active = (bool) $cat->is_active;

        $this->showCategoryFormModal = true;
    }

    public function closeCategoryFormModal(): void
    {
        $this->showCategoryFormModal = false;
        $this->editingCategoryId = null;
    }

    public function saveCategory(): void
    {
        $uniqueRule = 'unique:asset_categories,code';
        if ($this->editingCategoryId) {
            $uniqueRule .= ','.$this->editingCategoryId;
        }

        $this->validate([
            'cat_code' => ['required', 'string', 'max:50', $uniqueRule],
            'cat_name' => ['required', 'string', 'max:255'],
            'cat_useful_life_years' => ['required', 'integer', 'min:0'],
            'cat_salvage_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'cat_asset_account_id' => ['nullable', 'exists:accounts,id'],
            'cat_accumulated_account_id' => ['nullable', 'exists:accounts,id'],
            'cat_expense_account_id' => ['nullable', 'exists:accounts,id'],
        ], [
            'cat_code.required' => 'Kode kategori wajib diisi.',
            'cat_code.unique' => 'Kode kategori sudah digunakan.',
            'cat_name.required' => 'Nama kategori wajib diisi.',
            'cat_useful_life_years.required' => 'Masa manfaat tahun wajib diisi.',
        ]);

        $data = [
            'code' => strtoupper($this->cat_code),
            'name' => $this->cat_name,
            'useful_life_years' => $this->cat_useful_life_years,
            'salvage_percentage' => (float) $this->cat_salvage_percentage,
            'asset_account_id' => $this->cat_asset_account_id ?: null,
            'accumulated_depreciation_account_id' => $this->cat_accumulated_account_id ?: null,
            'depreciation_expense_account_id' => $this->cat_expense_account_id ?: null,
            'description' => $this->cat_description ?: null,
            'is_active' => $this->cat_is_active,
        ];

        if ($this->editingCategoryId) {
            $cat = AssetCategory::findOrFail($this->editingCategoryId);
            $cat->update($data);
            session()->flash('success', "Kategori aset '{$cat->name}' berhasil diperbarui.");
        } else {
            $cat = AssetCategory::create($data);
            session()->flash('success', "Kategori aset baru '{$cat->name}' berhasil ditambahkan.");

            // Jika form Tambah Aset sedang terbuka, otomatis pilihkan kategori baru ini!
            if ($this->showAssetModal) {
                $this->asset_category_id = (string) $cat->id;
                $this->useful_life_months = $cat->useful_life_years * 12;
            }
        }

        $this->showCategoryFormModal = false;
        $this->editingCategoryId = null;
    }

    public function deleteCategory(int $id): void
    {
        $cat = AssetCategory::withCount('fixedAssets')->findOrFail($id);

        if ($cat->fixed_assets_count > 0) {
            session()->flash('error', "Kategori '{$cat->name}' tidak dapat dihapus karena masih digunakan oleh {$cat->fixed_assets_count} data aset tetap.");

            return;
        }

        $cat->delete();
        session()->flash('success', "Kategori '{$cat->name}' berhasil dihapus.");
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

        // Kategori lengkap dengan relasi dan jumlah aset terdaftar untuk Category Manager
        $allCategories = AssetCategory::with(['assetAccount', 'accumulatedDepreciationAccount', 'depreciationExpenseAccount'])
            ->withCount('fixedAssets')
            ->orderBy('code')
            ->get();

        // Akun COA untuk dropdown mapping
        $assetAccounts = Account::where(function ($q) {
            $q->where('code', 'like', '12.01%')
                ->orWhere('code', 'like', '12.02%')
                ->orWhere('code', 'like', '12.03%')
                ->orWhere('code', 'like', '12.04%');
        })->posting()->active()->orderBy('code')->get();

        $accumAccounts = Account::where('code', 'like', '12.1%')
            ->posting()->active()->orderBy('code')->get();

        $expenseAccounts = Account::where('code', 'like', '68%')
            ->posting()->active()->orderBy('code')->get();

        return view('livewire.accounting.assets.fixed-asset-index', [
            'assets' => $assets,
            'totalCost' => $totalCost,
            'totalAccum' => $totalAccum,
            'totalBookValue' => $totalBookValue,
            'totalActive' => $totalActive,
            'categories' => $categories,
            'allCategories' => $allCategories,
            'units' => $units,
            'assetAccounts' => $assetAccounts,
            'accumAccounts' => $accumAccounts,
            'expenseAccounts' => $expenseAccounts,
        ])->layout('layouts.app');
    }
}
