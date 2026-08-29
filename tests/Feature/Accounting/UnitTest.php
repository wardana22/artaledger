<?php

use App\Livewire\Accounting\Settings\UnitIndex;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    $this->actingAs($this->user);
});

it('can render units index page', function () {
    Livewire::test(UnitIndex::class)
        ->assertStatus(200)
        ->assertSee('Master Unit Perusahaan');
});

it('can create a new unit', function () {
    Livewire::test(UnitIndex::class)
        ->set('code', 'TESTUNIT')
        ->set('name', 'Unit Test Baru')
        ->set('keywords', 'TEST, UNIT BARU')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('units', [
        'code' => 'TESTUNIT',
        'name' => 'Unit Test Baru',
    ]);
});

it('can update existing unit', function () {
    $unit = Unit::create([
        'code' => 'KUS',
        'name' => 'Unit Kustom',
        'keywords' => 'KUS, KUSTOM',
    ]);

    Livewire::test(UnitIndex::class)
        ->call('openEditModal', $unit->id)
        ->set('name', 'Unit Kustom Diperbarui')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('units', [
        'id' => $unit->id,
        'name' => 'Unit Kustom Diperbarui',
    ]);
});

it('can delete unused unit', function () {
    $unit = Unit::create([
        'code' => 'DELU',
        'name' => 'Unit Dihapus',
    ]);

    Livewire::test(UnitIndex::class)
        ->call('delete', $unit->id);

    $this->assertDatabaseMissing('units', [
        'id' => $unit->id,
    ]);
});
