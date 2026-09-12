<?php

use App\Livewire\Accounting\Assets\DepreciationRun;
use App\Livewire\Accounting\Assets\FixedAssetIndex;
use App\Models\AssetCategory;
use App\Models\Company;
use App\Models\FixedAsset;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->company = Company::firstOrCreate(['id' => 1], ['name' => 'PT Arta Ledger Test', 'code' => 'ALT']);
    $this->unit = Unit::firstOrCreate(['code' => 'PST'], ['name' => 'Kantor Pusat', 'company_id' => $this->company->id]);
    $this->category = AssetCategory::firstOrCreate(['code' => 'ELK'], [
        'name' => 'Elektronik & IT',
        'useful_life_years' => 4,
        'salvage_percentage' => 0,
        'is_active' => true,
    ]);

    // Setup Staff User with ONLY assets.view permission
    $this->staffUser = User::factory()->create(['name' => 'Staf Keuangan Asset View Only']);
    $staffRole = Role::where('name', 'Staf Keuangan')->first();
    if ($staffRole) {
        $this->staffUser->assignRole($staffRole);
    }
    $this->staffUser->givePermissionTo('assets.view');

    // Setup Admin User with full asset permissions
    $this->adminUser = User::factory()->create(['name' => 'Admin User Full Asset']);
    $adminRole = Role::where('name', 'Super Admin')->first();
    if ($adminRole) {
        $this->adminUser->assignRole($adminRole);
    }
});

test('staff user with only assets.view cannot see mutation buttons or execute actions', function () {
    $asset = FixedAsset::create([
        'company_id' => $this->company->id,
        'unit_id' => $this->unit->id,
        'asset_category_id' => $this->category->id,
        'asset_code' => 'AST-001',
        'name' => 'MacBook Pro M3',
        'acquisition_date' => '2026-01-01',
        'start_depreciation_date' => '2026-01-01',
        'acquisition_cost' => 30000000,
        'salvage_value' => 0,
        'useful_life_months' => 48,
        'monthly_depreciation_amount' => 625000,
        'accumulated_depreciation' => 0,
        'book_value' => 30000000,
        'status' => 'active',
    ]);

    // Livewire component test: Staff can view the index and see the asset
    $component = Livewire::actingAs($this->staffUser)
        ->test(FixedAssetIndex::class)
        ->assertStatus(200)
        ->assertSee('MacBook Pro M3')
        ->assertSee('Print Label')
        // Action buttons MUST NOT be rendered for staff:
        ->assertDontSee('Tambah Aset Tetap')
        ->assertDontSee('Kelola Kategori')
        ->assertDontSee('Eksekusi Penyusutan Bulanan');

    // Attempting to call mutation methods must be aborted with 403
    Livewire::actingAs($this->staffUser)
        ->test(FixedAssetIndex::class)
        ->call('openCreateModal')
        ->assertStatus(403);

    Livewire::actingAs($this->staffUser)
        ->test(FixedAssetIndex::class)
        ->call('openEditModal', $asset->id)
        ->assertStatus(403);

    Livewire::actingAs($this->staffUser)
        ->test(FixedAssetIndex::class)
        ->call('deleteAsset', $asset->id)
        ->assertStatus(403);

    Livewire::actingAs($this->staffUser)
        ->test(FixedAssetIndex::class)
        ->call('openCategoryManagerModal')
        ->assertStatus(403);
});

test('staff user cannot access depreciation run page without assets.depreciate permission', function () {
    Livewire::actingAs($this->staffUser)
        ->test(DepreciationRun::class)
        ->assertStatus(403);
});

test('admin user with full permissions can see all buttons and access depreciation page', function () {
    Livewire::actingAs($this->adminUser)
        ->test(FixedAssetIndex::class)
        ->assertStatus(200)
        ->assertSee('Tambah Aset Tetap')
        ->assertSee('Kelola Kategori')
        ->assertSee('Eksekusi Penyusutan Bulanan');

    Livewire::actingAs($this->adminUser)
        ->test(DepreciationRun::class)
        ->assertStatus(200);
});
