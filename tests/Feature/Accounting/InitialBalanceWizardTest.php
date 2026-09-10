<?php

use App\Livewire\Accounting\Settings\InitialBalanceIndex;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\User;
use Database\Seeders\AccountingPeriodSeeder;
use Database\Seeders\AccountSeeder;
use Database\Seeders\JournalTypeSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\SaldoAwalSeeder;
use Database\Seeders\UnitSeeder;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->user = User::factory()->create([
        'password' => Hash::make('SecretAdmin123!'),
    ]);
    $this->user->assignRole('Super Admin');
    $this->actingAs($this->user);

    $this->company = Company::firstOrCreate([
        'code' => 'AL',
    ], [
        'name' => 'PT Arta Ledger',
    ]);

    $this->seed([
        AccountSeeder::class,
        JournalTypeSeeder::class,
        UnitSeeder::class,
        AccountingPeriodSeeder::class,
        SaldoAwalSeeder::class,
    ]);
});

it('loads existing opening balance entry in locked state by default', function () {
    Livewire::test(InitialBalanceIndex::class)
        ->assertStatus(200)
        ->assertSet('isLocked', true)
        ->assertSet('existingEntryNumber', 'SA-2025-001')
        ->assertSee('🔒 TERKUNCI RESMI')
        ->assertSee('SA-2025-001');
});

it('calculates auto balance correctly to retained earnings', function () {
    $component = Livewire::test(InitialBalanceIndex::class);
    $calc = $component->instance()->calculation;

    expect($calc['is_balanced'])->toBeTrue();
    expect(abs($calc['final_debit'] - $calc['final_credit']))->toBeLessThan(0.01);
});

it('requires super admin password to reopen / unlock initial balance', function () {
    // 1. Password salah harus ditolak
    Livewire::test(InitialBalanceIndex::class)
        ->call('openUnlockModal')
        ->set('unlockPassword', 'WrongPassword123')
        ->set('unlockReason', 'Koreksi saldo audit tahunan 2025')
        ->call('confirmUnlock')
        ->assertHasErrors(['unlockPassword'])
        ->assertSet('isLocked', true);

    // 2. Password benar membuka gembok
    Livewire::test(InitialBalanceIndex::class)
        ->call('openUnlockModal')
        ->set('unlockPassword', 'SecretAdmin123!')
        ->set('unlockReason', 'Koreksi saldo audit tahunan 2025')
        ->call('confirmUnlock')
        ->assertHasNoErrors()
        ->assertSet('isLocked', false);

    // 3. Verifikasi persistensi database: setelah refresh/remount, status tetap terbuka (isLocked = false)
    Livewire::test(InitialBalanceIndex::class)
        ->assertSet('isLocked', false)
        ->assertSee('🔓 MODE PENGISIAN / KOREKSI');

    // 4. Ketika dikunci kembali secara manual, status kembali terkunci di database
    Livewire::test(InitialBalanceIndex::class)
        ->call('lockAgain')
        ->assertSet('isLocked', true);

    Livewire::test(InitialBalanceIndex::class)
        ->assertSet('isLocked', true)
        ->assertSee('🔒 TERKUNCI RESMI');
});

it('can update initial balance and automatically re-lock upon posting', function () {
    $component = Livewire::test(InitialBalanceIndex::class)
        ->call('openUnlockModal')
        ->set('unlockPassword', 'SecretAdmin123!')
        ->set('unlockReason', 'Koreksi saldo audit resmi KAP 2025')
        ->call('confirmUnlock')
        ->assertSet('isLocked', false);

    // Ubah saldo salah satu akun dan simpan
    $component->call('saveAndPost')
        ->assertHasNoErrors()
        ->assertSet('isLocked', true);

    $entry = JournalEntry::where('entry_number', 'SA-2025-001')->first();
    expect($entry)->not->toBeNull();
    expect($entry->status)->toBe('posted');
    expect($entry->is_locked)->toBeTrue();
});

it('rejects unlock attempt from non-superadmin users even with correct user password', function () {
    $accountant = User::factory()->create([
        'password' => Hash::make('AccountantPass123!'),
    ]);
    $accountantRole = Role::firstOrCreate(['name' => 'Akuntan Biasa']);
    $accountantRole->givePermissionTo(['settings.manage', 'accounts.view']);
    $accountant->assignRole($accountantRole);

    $this->actingAs($accountant);

    Livewire::test(InitialBalanceIndex::class)
        ->call('openUnlockModal')
        ->set('unlockPassword', 'AccountantPass123!')
        ->set('unlockReason', 'Percobaan buka kunci tanpa hak akses')
        ->call('confirmUnlock')
        ->assertHasErrors(['unlockPassword'])
        ->assertSet('isLocked', true);
});
