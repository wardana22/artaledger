<?php

use App\Livewire\Dashboard\DashboardIndex;
use App\Livewire\Dashboard\DashboardSettingsIndex;
use App\Models\Account;
use App\Models\Company;
use App\Models\DashboardKpi;
use App\Models\DashboardSetting;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $this->company = Company::firstOrCreate(['id' => 1], [
        'code' => 'ALT',
        'name' => 'PT Arta Ledger Test',
        'app_name' => 'ArtaLedger',
    ]);

    $this->account = Account::create([
        'company_id' => $this->company->id,
        'code' => '11.01.01',
        'name' => 'Kas Induk Utama',
        'type' => 'asset',
        'normal_balance' => 'debit',
        'report_type' => 'neraca',
        'is_group' => false,
        'is_active' => true,
    ]);

    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('Super Admin');
});

test('super admin can render dashboard page with dynamic kpi cards', function () {
    Livewire::actingAs($this->adminUser)
        ->test(DashboardIndex::class)
        ->assertStatus(200)
        ->assertSee('Dashboard Finansial Eksekutif');
});

test('super admin can render dashboard settings page', function () {
    Livewire::actingAs($this->adminUser)
        ->test(DashboardSettingsIndex::class)
        ->assertStatus(200)
        ->assertSee('Pengaturan Tampilan');
});

test('super admin can create, update, and delete custom coa kpi card', function () {
    // Create custom KPI card
    Livewire::actingAs($this->adminUser)
        ->test(DashboardSettingsIndex::class)
        ->set('kpi_title', 'Kas Induk Utama Card')
        ->set('kpi_source_type', 'account')
        ->set('kpi_account_id', $this->account->id)
        ->set('kpi_calculation_type', 'ending_balance')
        ->set('kpi_color_theme', 'emerald')
        ->set('kpi_icon', 'wallet')
        ->set('kpi_order_index', 1)
        ->call('saveKpi')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('dashboard_kpis', [
        'company_id' => $this->company->id,
        'title' => 'Kas Induk Utama Card',
        'account_id' => $this->account->id,
        'color_theme' => 'emerald',
    ]);

    $kpi = DashboardKpi::where('title', 'Kas Induk Utama Card')->first();

    // Toggle active state
    Livewire::actingAs($this->adminUser)
        ->test(DashboardSettingsIndex::class)
        ->call('toggleKpiActive', $kpi->id);

    $kpi->refresh();
    expect($kpi->is_active)->toBeFalse();

    // Delete KPI card
    Livewire::actingAs($this->adminUser)
        ->test(DashboardSettingsIndex::class)
        ->call('deleteKpi', $kpi->id);

    $this->assertDatabaseMissing('dashboard_kpis', [
        'id' => $kpi->id,
    ]);
});

test('super admin can toggle dashboard widget visibility settings', function () {
    Livewire::actingAs($this->adminUser)
        ->test(DashboardSettingsIndex::class)
        ->set('show_recent_journals', false)
        ->set('show_quick_actions', false)
        ->call('saveSettings')
        ->assertHasNoErrors();

    $setting = DashboardSetting::where('company_id', $this->company->id)->first();
    expect($setting->show_recent_journals)->toBeFalse();
    expect($setting->show_quick_actions)->toBeFalse();
});

test('unauthorized user without permission receives 403 on dashboard settings', function () {
    $regularUser = User::factory()->create();

    Livewire::actingAs($regularUser)
        ->test(DashboardSettingsIndex::class)
        ->assertStatus(403);
});
