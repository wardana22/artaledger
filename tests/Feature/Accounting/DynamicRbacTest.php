<?php

use App\Livewire\Accounting\Accounts\AccountIndex;
use App\Livewire\Admin\RoleIndex;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Company::firstOrCreate(['id' => 1], [
        'name' => 'PT Arta Ledger Test',
        'code' => 'ALT',
    ]);
    $this->seed(RoleAndPermissionSeeder::class);
});

test('admin can access role management page and create dynamic roles', function () {
    $adminRole = Role::firstOrCreate(['name' => 'Super Admin']);
    $user = User::factory()->create();
    $user->assignRole($adminRole);

    Permission::firstOrCreate(['name' => 'accounts.view']);
    Permission::firstOrCreate(['name' => 'accounts.create']);

    Livewire::actingAs($user)
        ->test(RoleIndex::class)
        ->set('roleName', 'Senior Auditor')
        ->set('selectedPermissions', ['accounts.view', 'accounts.create'])
        ->call('saveRole')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('roles', [
        'name' => 'Senior Auditor',
    ]);
});

test('user without accounts.create permission cannot save new account', function () {
    $restrictedUser = User::factory()->create();

    $permissionView = Permission::firstOrCreate(['name' => 'accounts.view']);
    $restrictedUser->givePermissionTo($permissionView);

    Livewire::actingAs($restrictedUser)
        ->test(AccountIndex::class)
        ->set('code', '99.99')
        ->set('name', 'Unauthorized Test Account')
        ->set('normal_balance', 'debit')
        ->set('report_type', 'neraca')
        ->call('saveAccount')
        ->assertSee('Akses ditolak');

    $this->assertDatabaseMissing('accounts', [
        'code' => '99.99',
    ]);
});

test('user with accounts.create permission can save new account', function () {
    $authorizedUser = User::factory()->create();

    $permView = Permission::firstOrCreate(['name' => 'accounts.view']);
    $permCreate = Permission::firstOrCreate(['name' => 'accounts.create']);
    $authorizedUser->givePermissionTo([$permView, $permCreate]);

    Livewire::actingAs($authorizedUser)
        ->test(AccountIndex::class)
        ->set('code', '99.88')
        ->set('name', 'Authorized Test Account')
        ->set('normal_balance', 'debit')
        ->set('report_type', 'neraca')
        ->call('saveAccount')
        ->assertHasNoErrors()
        ->assertSee('Akun baru berhasil ditambahkan.');

    $this->assertDatabaseHas('accounts', [
        'code' => '99.88',
        'name' => 'Authorized Test Account',
    ]);
});
