<?php

use App\Livewire\Accounting\Budgets\BudgetForm;
use App\Livewire\Accounting\Budgets\BudgetIndex;
use App\Models\Budget;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    Company::firstOrCreate(['id' => 1], [
        'name' => 'PT Arta Ledger Test',
        'code' => 'ALT',
    ]);
    $this->seed(RoleAndPermissionSeeder::class);
});

test('unauthorized user cannot view budget index page', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(BudgetIndex::class)
        ->assertForbidden();
});

test('authorized user with budgets.view can view budget index page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('budgets.view');

    Livewire::actingAs($user)
        ->test(BudgetIndex::class)
        ->assertSuccessful();
});

test('user without budgets.manage cannot open budget form or create budget', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('budgets.view');

    Livewire::actingAs($user)
        ->test(BudgetForm::class)
        ->assertForbidden();
});

test('authorized user with budgets.manage can create and activate budget', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['budgets.view', 'budgets.manage']);

    $budget = Budget::create([
        'fiscal_year' => 2026,
        'name' => 'Anggaran Operasional 2026',
        'status' => 'draft',
        'enforcement_mode' => 'warning_only',
        'warning_threshold_pct' => 80,
        'created_by' => $user->id,
    ]);

    Livewire::actingAs($user)
        ->test(BudgetIndex::class)
        ->call('activateBudget', $budget->id)
        ->assertSuccessful();

    expect($budget->fresh()->status)->toBe('active');
});

test('unauthorized user cannot export budget report pdf or excel', function () {
    $user = User::factory()->create();

    $budget = Budget::create([
        'fiscal_year' => 2026,
        'name' => 'Anggaran Test Export',
        'status' => 'active',
        'enforcement_mode' => 'warning_only',
        'warning_threshold_pct' => 80,
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('accounting.budgets.export.pdf', ['budgetId' => $budget->id]))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('accounting.budgets.export.excel', ['budgetId' => $budget->id]))
        ->assertForbidden();
});
