<?php

use App\Livewire\Admin\RoleIndex;
use App\Models\Company;
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

test('admin can view role management page and search roles', function () {
    $superAdminRole = Role::where('name', 'Super Admin')->first();
    $admin = User::factory()->create(['name' => 'Admin Role Viewer']);
    $admin->assignRole($superAdminRole);

    Livewire::actingAs($admin)
        ->test(RoleIndex::class)
        ->set('search', 'Finance')
        ->assertSee('Akuntan / Finance Manager');
});

test('admin can create a new custom role with permissions', function () {
    $superAdminRole = Role::where('name', 'Super Admin')->first();
    $admin = User::factory()->create();
    $admin->assignRole($superAdminRole);

    Livewire::actingAs($admin)
        ->test(RoleIndex::class)
        ->call('openCreateRoleModal')
        ->set('roleName', 'Auditor Lapangan')
        ->set('selectedPermissions', ['budgets.view', 'reports.view'])
        ->call('saveRole')
        ->assertHasNoErrors();

    $role = Role::where('name', 'Auditor Lapangan')->first();
    expect($role)->not->toBeNull();
    expect($role->hasPermissionTo('budgets.view'))->toBeTrue();
    expect($role->hasPermissionTo('reports.view'))->toBeTrue();
});

test('super admin role cannot be renamed, modified or deleted', function () {
    $superAdminRole = Role::where('name', 'Super Admin')->first();
    $admin = User::factory()->create();
    $admin->assignRole($superAdminRole);

    // Attempt delete
    Livewire::actingAs($admin)
        ->test(RoleIndex::class)
        ->call('deleteRole', $superAdminRole->id)
        ->assertSee('Peran Super Admin bawaan sistem tidak boleh dihapus');

    expect(Role::where('name', 'Super Admin')->exists())->toBeTrue();

    // Attempt edit Super Admin
    Livewire::actingAs($admin)
        ->test(RoleIndex::class)
        ->call('openEditRoleModal', $superAdminRole->id)
        ->set('roleName', 'Hacked Super Admin')
        ->call('saveRole')
        ->assertSee('Perlindungan Sistem: Hak akses peran bawaan Super Admin tidak dapat diubah');

    expect(Role::where('name', 'Super Admin')->exists())->toBeTrue();
});

test('unauthorized user without admin.roles permission cannot access role management', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(RoleIndex::class)
        ->assertForbidden();
});
