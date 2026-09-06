<?php

use App\Domain\Accounting\Services\AccountSeederService;
use App\Models\Account;
use App\Models\Company;
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

    Company::firstOrCreate(['id' => 1], [
        'code' => 'ALT',
        'name' => 'PT Arta Ledger Test',
        'app_name' => 'ArtaLedger',
        'prepared_by_name' => 'Staff Akuntansi Test',
        'prepared_by_title' => 'Staff Keuangan',
        'reviewed_by_name' => 'Manager Test',
        'reviewed_by_title' => 'Accounting Lead',
        'approved_by_name' => 'Direktur Test',
        'approved_by_title' => 'CFO',
    ]);
});

test('unauthenticated user is redirected to login when accessing excel export', function () {
    $this->get(route('accounting.reports.export.excel', ['type' => 'profit-loss']))
        ->assertRedirect(route('login'));
});

test('authenticated user can export profit and loss report excel', function () {
    $response = $this->actingAs($this->user)
        ->get(route('accounting.reports.export.excel', [
            'type' => 'profit-loss',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'unit' => 'all',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

test('authenticated user can export balance sheet report excel', function () {
    $response = $this->actingAs($this->user)
        ->get(route('accounting.reports.export.excel', [
            'type' => 'balance-sheet',
            'as_of_date' => '2025-12-31',
            'unit' => 'all',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

test('authenticated user can export trial balance report excel', function () {
    $response = $this->actingAs($this->user)
        ->get(route('accounting.reports.export.excel', [
            'type' => 'trial-balance',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'unit' => 'all',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

test('authenticated user can export general ledger report excel', function () {
    $account = Account::group()->active()->first();

    $response = $this->actingAs($this->user)
        ->get(route('accounting.reports.export.excel', [
            'type' => 'general-ledger',
            'account_id' => $account ? $account->id : 1,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'unit' => 'all',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

test('authenticated user can export subsidiary ledger report excel', function () {
    $account = Account::posting()->active()->first();

    $response = $this->actingAs($this->user)
        ->get(route('accounting.reports.export.excel', [
            'type' => 'subsidiary-ledger',
            'account_id' => $account ? $account->id : 1,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'unit' => 'all',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

test('authenticated user can export cash flow report excel', function () {
    $response = $this->actingAs($this->user)
        ->get(route('accounting.reports.export.excel', [
            'type' => 'cash-flow',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'unit' => 'all',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

test('authenticated user can export changes in equity report excel', function () {
    $response = $this->actingAs($this->user)
        ->get(route('accounting.reports.export.excel', [
            'type' => 'changes-in-equity',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'unit' => 'all',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

test('authenticated user can export worksheet report excel', function () {
    $response = $this->actingAs($this->user)
        ->get(route('accounting.reports.export.excel', [
            'type' => 'worksheet',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'unit' => 'all',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

test('authenticated user can export opening balance report excel', function () {
    $response = $this->actingAs($this->user)
        ->get(route('accounting.reports.export.excel', [
            'type' => 'opening-balance',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

test('exporting invalid report type returns 404', function () {
    $this->actingAs($this->user)
        ->get(route('accounting.reports.export.excel', ['type' => 'unknown-report']))
        ->assertNotFound();
});
