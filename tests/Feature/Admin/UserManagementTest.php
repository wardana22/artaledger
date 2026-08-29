<?php

use App\Livewire\Admin\UserIndex;
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

test('admin can view user management page and search users', function () {
    $superAdminRole = Role::where('name', 'Super Admin')->first();
    $admin = User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@artaledger.com']);
    $admin->assignRole($superAdminRole);

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->set('search', 'Budi')
        ->assertSee('Budi Santoso');
});

test('admin can create new user with role and assigned units', function () {
    $superAdminRole = Role::where('name', 'Super Admin')->first();
    $admin = User::factory()->create();
    $admin->assignRole($superAdminRole);

    $unitRst = Unit::create(['code' => 'RST', 'name' => 'RS Tandun']);
    $unitKp = Unit::create(['code' => 'KP', 'name' => 'Kantor Pusat']);

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->set('name', 'Siti Rahma')
        ->set('email', 'siti@artaledger.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('selectedRoles', ['Staf Keuangan'])
        ->set('selectedUnits', [$unitRst->id])
        ->call('saveUser')
        ->assertHasNoErrors();

    $newUser = User::where('email', 'siti@artaledger.com')->first();
    expect($newUser)->not->toBeNull();
    expect($newUser->hasRole('Staf Keuangan'))->toBeTrue();
    expect($newUser->units->pluck('code')->toArray())->toContain('RST');
});

test('admin cannot delete their own active logged in user account', function () {
    $superAdminRole = Role::where('name', 'Super Admin')->first();
    $admin = User::factory()->create(['name' => 'Active Admin User']);
    $admin->assignRole($superAdminRole);

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('deleteUser', $admin->id)
        ->assertSee('Gagal menghapus! Anda tidak dapat menghapus akun Anda sendiri');

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('admin can filter users by role', function () {
    $superAdminRole = Role::where('name', 'Super Admin')->first();
    $staffRole = Role::where('name', 'Staf Keuangan')->first();

    $admin = User::factory()->create(['name' => 'Super Admin User']);
    $admin->assignRole($superAdminRole);

    $staff = User::factory()->create(['name' => 'Staf User Only']);
    $staff->assignRole($staffRole);

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->set('roleFilter', 'Staf Keuangan')
        ->assertSee('Staf User Only')
        ->assertDontSee('Super Admin User');
});
