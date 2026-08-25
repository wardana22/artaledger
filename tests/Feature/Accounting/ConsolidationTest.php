<?php

use App\Livewire\Accounting\OpeningBalance\OpeningBalanceIndex;
use App\Livewire\Accounting\Reports\GeneralLedger;
use App\Livewire\Accounting\Reports\TrialBalance;
use App\Livewire\Accounting\Reports\Worksheet;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\Unit;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->company = Company::create(['name' => 'PT Perkebunan Nusantara V', 'code' => 'PTPNV']);
    $this->period = AccountingPeriod::create([
        'company_id' => $this->company->id,
        'year' => 2025,
        'month' => 1,
        'start_date' => '2025-01-01',
        'end_date' => '2025-01-31',
        'status' => 'open',
    ]);
});

test('opening balance report renders in consolidation mode by default', function () {
    Livewire::actingAs($this->user)
        ->test(OpeningBalanceIndex::class)
        ->assertStatus(200)
        ->assertSet('unitFilter', 'all');
});

test('opening balance report allows filtering by specific unit', function () {
    $unit = Unit::first() ?? Unit::create(['code' => 'KP', 'name' => 'Kantor Pusat']);

    Livewire::actingAs($this->user)
        ->test(OpeningBalanceIndex::class)
        ->set('unitFilter', (string) $unit->id)
        ->assertSet('unitFilter', (string) $unit->id)
        ->assertStatus(200);
});

test('general ledger report renders with consolidated unit filter', function () {
    Livewire::actingAs($this->user)
        ->test(GeneralLedger::class)
        ->assertStatus(200)
        ->assertSet('unitFilter', 'all');
});

test('trial balance report renders with consolidated unit filter', function () {
    Livewire::actingAs($this->user)
        ->test(TrialBalance::class)
        ->assertStatus(200)
        ->assertSet('unitFilter', 'all');
});

test('worksheet report renders with consolidated unit filter', function () {
    Livewire::actingAs($this->user)
        ->test(Worksheet::class)
        ->assertStatus(200)
        ->assertSet('unitFilter', 'all');
});
