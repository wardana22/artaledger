<?php

use App\Livewire\Accounting\Periods\PeriodIndex;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->company = Company::firstOrCreate(['id' => 1], ['name' => 'PT Arta Ledger Test', 'code' => 'ALT']);

    $this->staffUser = User::factory()->create(['name' => 'Staf Keuangan']);
    $staffRole = Role::where('name', 'Staf Keuangan')->first();
    if ($staffRole) {
        $this->staffUser->assignRole($staffRole);
    }

    $this->superAdminUser = User::factory()->create(['name' => 'Super Admin User']);
    $adminRole = Role::where('name', 'Super Admin')->first();
    if ($adminRole) {
        $this->superAdminUser->assignRole($adminRole);
    }
});

test('staff user can close open accounting period and generate lock key', function () {
    $period = AccountingPeriod::create([
        'company_id' => $this->company->id,
        'year' => 2026,
        'month' => 8,
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-31',
        'status' => 'open',
    ]);

    Livewire::actingAs($this->staffUser)
        ->test(PeriodIndex::class)
        ->call('closePeriod', $period->id)
        ->assertHasNoErrors();

    $period->refresh();
    expect($period->status)->toBe('closed');
    expect($period->lock_key)->not->toBeNull();
    expect($period->lock_key)->toContain('LOCK-202608-');
    expect($period->closed_by)->toBe($this->staffUser->id);
});

test('super admin can reveal lock key while staff cannot see it in livewire render', function () {
    $period = AccountingPeriod::create([
        'company_id' => $this->company->id,
        'year' => 2026,
        'month' => 8,
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-31',
        'status' => 'closed',
        'lock_key' => 'LOCK-202608-SECRET123',
    ]);

    // Staff cannot see reveal button
    Livewire::actingAs($this->staffUser)
        ->test(PeriodIndex::class)
        ->assertViewHas('isSuperAdmin', false)
        ->assertDontSee('Lihat Lock Key (SuperAdmin)');

    // SuperAdmin can see reveal button
    Livewire::actingAs($this->superAdminUser)
        ->test(PeriodIndex::class)
        ->assertViewHas('isSuperAdmin', true)
        ->assertSee('Lihat Lock Key (SuperAdmin)');
});

test('reopening period fails if lock key is wrong and succeeds when key is correct', function () {
    $period = AccountingPeriod::create([
        'company_id' => $this->company->id,
        'year' => 2026,
        'month' => 8,
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-31',
        'status' => 'closed',
        'lock_key' => 'LOCK-202608-VALIDKEY',
    ]);

    // Attempt with WRONG key
    Livewire::actingAs($this->staffUser)
        ->test(PeriodIndex::class)
        ->call('openReopenModal', $period->id)
        ->set('inputLockKey', 'WRONG-KEY-999')
        ->set('reopenReason', 'Penyesuaian Audit Eksternal')
        ->call('confirmReopenPeriod')
        ->assertHasErrors(['inputLockKey']);

    $period->refresh();
    expect($period->status)->toBe('closed');

    // Attempt with CORRECT key
    Livewire::actingAs($this->staffUser)
        ->test(PeriodIndex::class)
        ->call('openReopenModal', $period->id)
        ->set('inputLockKey', 'LOCK-202608-VALIDKEY')
        ->set('reopenReason', 'Penyesuaian Audit Eksternal Resmi')
        ->call('confirmReopenPeriod')
        ->assertHasNoErrors();

    $period->refresh();
    expect($period->status)->toBe('open');
    expect($period->reopen_reason)->toBe('Penyesuaian Audit Eksternal Resmi');
    expect($period->opened_by)->toBe($this->staffUser->id);
});
