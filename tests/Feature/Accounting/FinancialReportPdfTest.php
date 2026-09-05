<?php

use App\Domain\Accounting\Services\AccountSeederService;
use App\Models\Account;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
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

test('unauthenticated user is redirected to login when accessing pdf export', function () {
    $this->get(route('accounting.reports.export.pdf', ['type' => 'profit-loss']))
        ->assertRedirect(route('login'));
});

test('authenticated user can export profit and loss report pdf', function () {
    $response = $this->actingAs($this->user)
        ->get(route('accounting.reports.export.pdf', [
            'type' => 'profit-loss',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'unit' => 'all',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    expect(strlen($response->getContent()))->toBeGreaterThan(500);
});

test('authenticated user can export balance sheet report pdf', function () {
    $response = $this->actingAs($this->user)
        ->get(route('accounting.reports.export.pdf', [
            'type' => 'balance-sheet',
            'as_of_date' => '2025-12-31',
            'unit' => 'all',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    expect(strlen($response->getContent()))->toBeGreaterThan(500);
});

test('authenticated user can export trial balance report pdf', function () {
    $response = $this->actingAs($this->user)
        ->get(route('accounting.reports.export.pdf', [
            'type' => 'trial-balance',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'unit' => 'all',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    expect(strlen($response->getContent()))->toBeGreaterThan(500);
});

test('authenticated user can export general ledger report pdf', function () {
    $account = Account::group()->active()->first();

    $response = $this->actingAs($this->user)
        ->get(route('accounting.reports.export.pdf', [
            'type' => 'general-ledger',
            'account_id' => $account->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'unit' => 'all',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    expect(strlen($response->getContent()))->toBeGreaterThan(500);
});

test('invalid report type returns 404', function () {
    $this->actingAs($this->user)
        ->get(route('accounting.reports.export.pdf', ['type' => 'non-existent-report']))
        ->assertNotFound();
});
