<?php

use App\Domain\Dashboard\Services\DashboardMetricService;
use App\Livewire\Dashboard\DashboardIndex;
use App\Models\AccountGroup;
use App\Models\Company;
use App\Models\DashboardChart;
use App\Models\DashboardKpi;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');

    Company::firstOrCreate(
        ['id' => 1],
        [
            'name' => 'PT Test ArtaLedger',
            'code' => 'AL-TEST',
            'fiscal_year_start' => 1,
        ]
    );

    Unit::firstOrCreate(
        ['id' => 1],
        [
            'company_id' => 1,
            'name' => 'Kantor Pusat',
            'code' => 'KP',
            'is_active' => true,
        ]
    );

    app(DashboardMetricService::class)->seedDefaultKpisAndCharts(1);
});

test('it seeds 12 kpis from pendapatan to dsi and custom account groups', function () {
    $kpis = DashboardKpi::where('company_id', 1)->orderBy('order_index')->get();
    expect($kpis->count())->toBe(12);

    expect($kpis[0]->title)->toBe('Pendapatan');
    expect($kpis[1]->title)->toBe('Beban Pokok Pendapatan (COGS)');
    expect($kpis[2]->title)->toBe('Laba Bersih');
    expect($kpis[3]->title)->toBe('EBITDA');
    expect($kpis[4]->title)->toBe('SGA to Sales');
    expect($kpis[5]->title)->toBe('COGS to Sales');
    expect($kpis[6]->title)->toBe('Laba Operasional');
    expect($kpis[7]->title)->toBe('Beban Admin & Umum (SGA)');
    expect($kpis[8]->title)->toBe('Laba Sebelum Pajak (EBT)');
    expect($kpis[9]->title)->toBe('Net Profit Margin (NPM)');
    expect($kpis[10]->title)->toBe('Inventory Turnover (ITO)');
    expect($kpis[11]->title)->toBe('Days Sales of Inventory (DSI)');

    $groups = AccountGroup::where('company_id', 1)->pluck('code')->toArray();
    expect($groups)->toContain('DASH_COGS');
    expect($groups)->toContain('DASH_SGA');
    expect($groups)->toContain('DASH_EBITDA_ADJ');
    expect($groups)->toContain('DASH_TAX');
    expect($groups)->toContain('DASH_INVENTORY');
    expect($groups)->toContain('DASH_COGS_INV');
});

test('dashboard service calculates 12 kpis and financial ratios', function () {
    $service = app(DashboardMetricService::class);
    $kpis = DashboardKpi::where('company_id', 1)->orderBy('order_index')->get();

    foreach ($kpis as $kpi) {
        $val = $service->calculateKpiValue($kpi, '2025-01-01', '2025-01-31');
        expect(is_numeric($val))->toBeTrue();
        expect($kpi->formatDisplayValue($val))->toBeString();
    }

    $ratios = $service->calculateFinancialRatios('2025-01-01', '2025-01-31', null, 1);
    expect($ratios)->toHaveKeys([
        'current_ratio',
        'net_profit_margin',
        'debt_to_equity',
        'current_assets',
        'current_liabilities',
    ]);
});

test('dashboard service generates monthly trend dataset for charts', function () {
    $service = app(DashboardMetricService::class);
    $charts = DashboardChart::where('company_id', 1)->get();
    expect($charts->count())->toBeGreaterThan(0);

    $trend = $service->getMonthlyTrend($charts, 2025, null, 1);
    expect($trend)->toBeArray();
    expect($trend)->toHaveKey($charts->first()->id);
    expect($trend[$charts->first()->id]['categories'])->toHaveCount(12);
    expect($trend[$charts->first()->id]['series'])->toBeArray();
});

test('dashboard livewire component renders all 12 kpis, financial ratios, and charts', function () {
    Livewire::actingAs($this->user)
        ->test(DashboardIndex::class)
        ->assertOk()
        ->assertSee('Dashboard Finansial Eksekutif')
        ->assertSee('Pendapatan')
        ->assertSee('Beban Pokok Pendapatan (COGS)')
        ->assertSee('Laba Bersih')
        ->assertSee('EBITDA')
        ->assertSee('SGA to Sales')
        ->assertSee('COGS to Sales')
        ->assertSee('Laba Operasional')
        ->assertSee('Beban Admin & Umum (SGA)')
        ->assertSee('Laba Sebelum Pajak (EBT)')
        ->assertSee('Net Profit Margin (NPM)')
        ->assertSee('Inventory Turnover (ITO)')
        ->assertSee('Days Sales of Inventory (DSI)')
        ->assertSee('Current Ratio (CR)')
        ->assertSee('Debt to Equity (DER)');
});
