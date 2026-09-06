<?php

use App\Domain\Accounting\Services\AccountSeederService;
use App\Domain\Accounting\Services\FinancialReportPdfService;
use App\Livewire\Accounting\Reports\TrialBalance;
use App\Models\Account;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->user = User::factory()->create();
    $adminRole = Role::where('name', 'Super Admin')->first();
    if ($adminRole) {
        $this->user->assignRole($adminRole);
    }
    (new AccountSeederService)->seedFromData();
});

test('trial balance is balanced for january 2025 and 21.02.02 has positive credit balance', function () {
    Livewire::actingAs($this->user)
        ->test(TrialBalance::class)
        ->set('startDate', '2025-01-01')
        ->set('endDate', '2025-01-31')
        ->set('unitFilter', 'all')
        ->assertViewHas('isBalanced', true)
        ->assertSee('BALANCED (SEIMBANG)');

    // Verify account 21.02.02 has positive credit balance
    $acc153 = Account::where('code', '21.02.02')->first();
    expect($acc153)->not->toBeNull();
    expect((float) $acc153->opening_balance)->toBeGreaterThan(0);
});

test('trial balance is balanced across monthly periods of 2025', function () {
    $months = [
        ['2025-01-01', '2025-01-31'],
        ['2025-02-01', '2025-02-28'],
        ['2025-03-01', '2025-03-31'],
        ['2025-04-01', '2025-04-30'],
        ['2025-05-01', '2025-05-31'],
        ['2025-06-01', '2025-06-30'],
    ];

    foreach ($months as [$start, $end]) {
        Livewire::actingAs($this->user)
            ->test(TrialBalance::class)
            ->set('startDate', $start)
            ->set('endDate', $end)
            ->set('unitFilter', 'all')
            ->assertViewHas('isBalanced', true);
    }
});

test('financial report pdf service calculates balanced trial balance data', function () {
    $service = app(FinancialReportPdfService::class);
    $data = $service->getTrialBalanceData('2025-01-01', '2025-01-31', 'all', $this->user);

    expect($data['isBalanced'])->toBeTrue();
    expect(abs($data['totalDebit'] - $data['totalCredit']))->toBeLessThan(1.0);
});
