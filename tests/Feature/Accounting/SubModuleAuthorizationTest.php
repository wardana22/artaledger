<?php

use App\Domain\Accounting\Services\JournalPostingService;
use App\Livewire\Accounting\Accounts\AccountIndex;
use App\Livewire\Accounting\Settings\JournalTypeIndex;
use App\Livewire\Accounting\Settings\UnitIndex;
use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Ensure standard permissions exist
    Permission::firstOrCreate(['name' => 'accounts.view']);
    Permission::firstOrCreate(['name' => 'settings.journal_types']);
    Permission::firstOrCreate(['name' => 'settings.units']);
});

test('user with accounts.view permission can access AccountIndex but is forbidden from JournalTypeIndex and UnitIndex', function () {
    $role = Role::create(['name' => 'Custom COA Viewer']);
    $role->givePermissionTo(['accounts.view']);

    $user = User::factory()->create();
    $user->assignRole($role);

    // Can access AccountIndex
    $this->actingAs($user)
        ->get(route('accounting.accounts.index'))
        ->assertStatus(200);

    // Forbidden from JournalTypeIndex
    $this->actingAs($user)
        ->get(route('accounting.journal-types.index'))
        ->assertStatus(403);

    // Forbidden from UnitIndex
    $this->actingAs($user)
        ->get(route('accounting.units.index'))
        ->assertStatus(403);
});

test('user with settings.journal_types permission can access JournalTypeIndex but not UnitIndex', function () {
    $role = Role::create(['name' => 'Custom Journal Type Manager']);
    $role->givePermissionTo(['settings.journal_types']);

    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get(route('accounting.journal-types.index'))
        ->assertStatus(200);

    $this->actingAs($user)
        ->get(route('accounting.accounts.index'))
        ->assertStatus(403);

    $this->actingAs($user)
        ->get(route('accounting.units.index'))
        ->assertStatus(403);
});

test('user with reports.general_ledger permission can access GeneralLedger but not ProfitLoss or OpeningBalance', function () {
    Permission::firstOrCreate(['name' => 'reports.general_ledger']);
    Permission::firstOrCreate(['name' => 'reports.profit_loss']);
    Permission::firstOrCreate(['name' => 'reports.opening_balance']);
    Permission::firstOrCreate(['name' => 'accounts.view']);

    $role = Role::create(['name' => 'General Ledger Viewer Only']);
    $role->givePermissionTo(['reports.general_ledger', 'accounts.view']);

    $user = User::factory()->create();
    $user->assignRole($role);

    // Can access General Ledger
    $this->actingAs($user)
        ->get(route('accounting.reports.general-ledger'))
        ->assertStatus(200);

    // Forbidden from Profit & Loss
    $this->actingAs($user)
        ->get(route('accounting.reports.profit-loss'))
        ->assertStatus(403);

    // Forbidden from Opening Balance
    $this->actingAs($user)
        ->get(route('accounting.reports.opening-balance'))
        ->assertStatus(403);
});

test('admin with journals.delete permission can delete posted journal entries', function () {
    Permission::firstOrCreate(['name' => 'journals.delete']);

    $role = Role::create(['name' => 'Journal Eraser Admin']);
    $role->givePermissionTo(['journals.delete']);

    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user);

    $service = new JournalPostingService;
    $company = Company::first() ?? Company::create(['code' => 'ART', 'name' => 'Arta']);
    $period = AccountingPeriod::firstOrCreate(
        ['company_id' => $company->id, 'year' => 2026, 'month' => 8],
        ['start_date' => '2026-08-01', 'end_date' => '2026-08-31', 'status' => 'open']
    );

    $acc1 = Account::create(['company_id' => $company->id, 'code' => '11.10.999', 'name' => 'Kas Test', 'report_type' => 'neraca', 'normal_balance' => 'debit', 'is_group' => false, 'is_active' => true]);
    $acc2 = Account::create(['company_id' => $company->id, 'code' => '41.10.999', 'name' => 'Pendapatan Test', 'report_type' => 'laba_rugi', 'normal_balance' => 'credit', 'is_group' => false, 'is_active' => true]);

    $journal = $service->postManualEntry([
        'company_id' => $company->id,
        'entry_date' => '2026-08-30',
        'description' => 'Test Posted Entry for Admin Deletion',
    ], [
        ['account_id' => $acc1->id, 'debit' => 100000, 'credit' => 0],
        ['account_id' => $acc2->id, 'debit' => 0, 'credit' => 100000],
    ], $user->id);

    expect($journal->status)->toBe('posted');

    // Admin with journals.delete permission can delete posted journal
    $service->deleteJournalEntry($journal);

    expect(JournalEntry::find($journal->id))->toBeNull();
});
