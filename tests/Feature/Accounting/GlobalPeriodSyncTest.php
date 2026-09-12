<?php

use App\Domain\Accounting\Services\GlobalPeriodService;
use App\Livewire\Accounting\Journals\JournalIndex;
use App\Livewire\Accounting\Reports\BalanceSheet;
use App\Livewire\Accounting\Reports\GeneralLedger;
use App\Livewire\Accounting\Reports\ProfitLoss;
use App\Livewire\Dashboard\DashboardIndex;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('Super Admin');

    // Reset session before each test
    app(GlobalPeriodService::class)->resetPeriod();
});

test('default global period starts from 1 January of the current year', function () {
    $service = app(GlobalPeriodService::class);
    $currentYear = Carbon::now()->year;

    expect($service->getStartDate())->toBe("{$currentYear}-01-01");
});

test('updating period in Dashboard synchronizes to other menus and vice versa', function () {
    // 1. Visit Dashboard and update date range to 01 Jan 2026 - 28 Feb 2026
    Livewire::actingAs($this->adminUser)
        ->test(DashboardIndex::class)
        ->set('startDate', '2026-01-01')
        ->set('endDate', '2026-02-28')
        ->assertSet('startDate', '2026-01-01')
        ->assertSet('endDate', '2026-02-28');

    // 2. Visit General Ledger: Must automatically have 2026-01-01 to 2026-02-28
    Livewire::actingAs($this->adminUser)
        ->test(GeneralLedger::class)
        ->assertSet('startDate', '2026-01-01')
        ->assertSet('endDate', '2026-02-28');

    // 3. Visit Profit & Loss: Must automatically have 2026-01-01 to 2026-02-28
    Livewire::actingAs($this->adminUser)
        ->test(ProfitLoss::class)
        ->assertSet('startDate', '2026-01-01')
        ->assertSet('endDate', '2026-02-28');

    // 4. Visit Balance Sheet: Must automatically have asOfDate equal to 2026-02-28
    Livewire::actingAs($this->adminUser)
        ->test(BalanceSheet::class)
        ->assertSet('asOfDate', '2026-02-28');

    // 5. Visit Journal Index: Must automatically have 2026-01-01 to 2026-02-28
    Livewire::actingAs($this->adminUser)
        ->test(JournalIndex::class)
        ->assertSet('startDate', '2026-01-01')
        ->assertSet('endDate', '2026-02-28');

    // 6. Update period from another menu (e.g. General Ledger to March 2026)
    Livewire::actingAs($this->adminUser)
        ->test(GeneralLedger::class)
        ->set('startDate', '2026-03-01')
        ->set('endDate', '2026-03-31')
        ->assertSet('startDate', '2026-03-01')
        ->assertSet('endDate', '2026-03-31');

    // 7. Visit Dashboard again: Must automatically follow to 2026-03-01 and 2026-03-31
    Livewire::actingAs($this->adminUser)
        ->test(DashboardIndex::class)
        ->assertSet('startDate', '2026-03-01')
        ->assertSet('endDate', '2026-03-31');
});
