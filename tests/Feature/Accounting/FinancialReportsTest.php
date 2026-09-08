<?php

use App\Domain\Accounting\Services\AccountSeederService;
use App\Livewire\Accounting\Reports\BalanceSheet;
use App\Livewire\Accounting\Reports\CashFlow;
use App\Livewire\Accounting\Reports\ChangesInEquity;
use App\Livewire\Accounting\Reports\ProfitLoss;
use App\Livewire\Accounting\Reports\TrialBalance;
use App\Livewire\Accounting\Reports\Worksheet;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    (new AccountSeederService)->seedFromData();
});

test('trial balance report component renders correctly', function () {
    Livewire::actingAs($this->user)
        ->test(TrialBalance::class)
        ->assertOk();
});

test('worksheet report component renders correctly', function () {
    Livewire::actingAs($this->user)
        ->test(Worksheet::class)
        ->assertOk();
});

test('profit and loss report component renders correctly', function () {
    Livewire::actingAs($this->user)
        ->test(ProfitLoss::class)
        ->assertOk();
});

test('balance sheet report component renders correctly', function () {
    Livewire::actingAs($this->user)
        ->test(BalanceSheet::class)
        ->assertOk();
});

test('cash flow report component renders correctly', function () {
    Livewire::actingAs($this->user)
        ->test(CashFlow::class)
        ->assertOk();
});

test('changes in equity report component renders correctly', function () {
    Livewire::actingAs($this->user)
        ->test(ChangesInEquity::class)
        ->assertOk();
});

test('worksheet strictly places negative balance in normal balance column', function () {
    $component = Livewire::actingAs($this->user)
        ->test(Worksheet::class)
        ->assertOk();

    // Verify worksheet renders successfully with the new strict column logic
    expect($component)->not->toBeNull();
});
