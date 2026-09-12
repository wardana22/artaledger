<?php

use App\Livewire\Accounting\Budgets\BudgetVarianceReport;
use App\Models\Budget;
use App\Models\Company;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Company::firstOrCreate(['id' => 1], [
        'name' => 'PT Arta Ledger Test',
        'code' => 'ALT',
    ]);
    $this->seed(RoleAndPermissionSeeder::class);
});

test('global user can see all units option and filterUnitId defaults to null', function () {
    $superAdminRole = Role::where('name', 'Super Admin')->first();
    $admin = User::factory()->create();
    $admin->assignRole($superAdminRole);

    $unitA = Unit::create(['code' => 'KP', 'name' => 'Kantor Pusat']);
    $unitB = Unit::create(['code' => 'RST', 'name' => 'RS Tandun']);

    $budget = Budget::create([
        'fiscal_year' => (int) date('Y'),
        'name' => 'Anggaran Uji Global',
        'status' => 'active',
        'enforcement_mode' => 'warning_only',
        'warning_threshold_pct' => 80,
        'created_by' => $admin->id,
    ]);

    Livewire::actingAs($admin)
        ->test(BudgetVarianceReport::class)
        ->assertSet('filterUnitId', null)
        ->assertSee('Semua Unit')
        ->assertSee('Kantor Pusat')
        ->assertSee('RS Tandun');
});

test('non-global user with assigned unit auto-locks filterUnitId to their assigned unit', function () {
    $staffRole = Role::where('name', 'Staf Keuangan')->first();
    $staff = User::factory()->create();
    $staff->assignRole($staffRole);
    // Give budgets.view permission if not in role
    $staff->givePermissionTo('budgets.view');

    $unitA = Unit::create(['code' => 'KP', 'name' => 'Kantor Pusat']);
    $unitB = Unit::create(['code' => 'RST', 'name' => 'RS Tandun']);

    // Assign only RS Tandun
    $staff->units()->attach($unitB->id);

    $budget = Budget::create([
        'fiscal_year' => (int) date('Y'),
        'name' => 'Anggaran Uji Cabang',
        'status' => 'active',
        'enforcement_mode' => 'warning_only',
        'warning_threshold_pct' => 80,
        'created_by' => $staff->id,
    ]);

    Livewire::actingAs($staff)
        ->test(BudgetVarianceReport::class)
        ->assertSet('filterUnitId', $unitB->id)
        ->assertDontSee('Semua Unit')
        ->assertSee('RS Tandun')
        ->assertDontSee('Kantor Pusat');
});

test('non-global user cannot bypass unit filter by passing query string or setting filterUnitId', function () {
    $staffRole = Role::where('name', 'Staf Keuangan')->first();
    $staff = User::factory()->create();
    $staff->assignRole($staffRole);
    $staff->givePermissionTo('budgets.view');

    $unitA = Unit::create(['code' => 'KP', 'name' => 'Kantor Pusat']);
    $unitB = Unit::create(['code' => 'RST', 'name' => 'RS Tandun']);

    $staff->units()->attach($unitB->id);

    $budget = Budget::create([
        'fiscal_year' => (int) date('Y'),
        'name' => 'Anggaran Uji Bypass',
        'status' => 'active',
        'enforcement_mode' => 'warning_only',
        'warning_threshold_pct' => 80,
        'created_by' => $staff->id,
    ]);

    Livewire::actingAs($staff)
        ->test(BudgetVarianceReport::class)
        ->set('filterUnitId', $unitA->id) // Try to switch to unauthorized unit
        ->assertSet('filterUnitId', $unitB->id); // Automatically forced back to authorized unit
});
