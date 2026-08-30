<?php

use App\Livewire\Accounting\Accounts\AccountGroupIndex;
use App\Livewire\Dashboard\DashboardIndex;
use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\Company;
use App\Models\DashboardKpi;
use App\Models\DashboardSetting;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->company = Company::firstOrCreate([], [
        'code' => 'ALT',
        'name' => 'PT Arta Ledger',
        'app_name' => 'ArtaLedger',
    ]);

    $this->user = User::factory()->create(['company_id' => $this->company->id]);
    $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    $this->user->assignRole($role);
    $this->actingAs($this->user);

    DashboardSetting::firstOrCreate(
        ['company_id' => $this->company->id],
        ['show_kpi_cards' => true]
    );
});

test('it renders account groups page and initializes system groups', function () {
    Livewire::test(AccountGroupIndex::class)
        ->assertStatus(200)
        ->assertSee('REVENUE')
        ->assertSee('COGS');

    expect(AccountGroup::where('company_id', $this->company->id)->count())->toBeGreaterThanOrEqual(2);
});

test('it allows super admin to create new custom account group', function () {
    Livewire::test(AccountGroupIndex::class)
        ->set('code', 'CUSTOM_ASSET')
        ->set('name', 'Custom Asset Group')
        ->set('color_theme', 'emerald')
        ->set('member_mode', 'prefix')
        ->set('account_prefix', '11')
        ->call('saveGroup')
        ->assertHasNoErrors();

    expect(AccountGroup::where('code', 'CUSTOM_ASSET')->exists())->toBeTrue();
});

test('it evaluates formula kpi card correctly with percentage display format', function () {
    $gRev = AccountGroup::create([
        'company_id' => $this->company->id,
        'code' => 'TEST_REV',
        'name' => 'Test Revenue',
        'color_theme' => 'emerald',
    ]);
    $gRev->members()->create(['account_prefix' => '4']);

    $gCogs = AccountGroup::create([
        'company_id' => $this->company->id,
        'code' => 'TEST_COGS',
        'name' => 'Test COGS',
        'color_theme' => 'rose',
    ]);
    $gCogs->members()->create(['account_prefix' => '5']);

    $accRev = Account::create([
        'company_id' => $this->company->id,
        'code' => '4101',
        'name' => 'Penjualan Barang',
        'type' => 'PENDAPATAN',
        'report_type' => 'laba_rugi',
        'normal_balance' => 'credit',
        'is_active' => true,
    ]);

    $accCogs = Account::create([
        'company_id' => $this->company->id,
        'code' => '5101',
        'name' => 'HPP Barang',
        'type' => 'BEBAN',
        'report_type' => 'laba_rugi',
        'normal_balance' => 'debit',
        'is_active' => true,
    ]);

    // Create journal entry with 10,000 Sales and 5,000 COGS
    $entry = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-TEST-001',
        'entry_date' => '2025-01-15',
        'status' => 'posted',
    ]);

    JournalLine::create([
        'journal_entry_id' => $entry->id,
        'account_id' => $accCogs->id,
        'debit' => 5000,
        'credit' => 0,
    ]);

    JournalLine::create([
        'journal_entry_id' => $entry->id,
        'account_id' => $accRev->id,
        'debit' => 0,
        'credit' => 10000,
    ]);

    $kpi = DashboardKpi::create([
        'company_id' => $this->company->id,
        'title' => 'COGS to Sales Ratio',
        'source_type' => 'formula',
        'formula_expression' => '([TEST_COGS] / [TEST_REV]) * 100',
        'display_format' => 'percentage',
        'decimal_places' => 2,
        'color_theme' => 'amber',
        'order_index' => 1,
        'is_active' => true,
    ]);

    $val = $kpi->calculateValue(null, 1, 2025);
    expect($val)->toBe(50.0);

    $formatted = $kpi->formatDisplayValue($val);
    expect($formatted)->toBe('50,00%');

    Livewire::test(DashboardIndex::class)
        ->set('selectedMonth', 1)
        ->set('selectedYear', 2025)
        ->assertStatus(200)
        ->assertSee('50,00%');
});
