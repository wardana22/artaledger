<?php

use App\Livewire\Accounting\OpeningBalance\OpeningBalanceIndex;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\AccountingPeriodSeeder;
use Database\Seeders\AccountSeeder;
use Database\Seeders\JournalTypeSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\SaldoAwalSeeder;
use Database\Seeders\UnitSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    $this->actingAs($this->user);

    $this->company = Company::firstOrCreate([
        'code' => 'AL',
    ], [
        'name' => 'PT Arta Ledger',
    ]);

    $this->seed([
        AccountSeeder::class,
        JournalTypeSeeder::class,
        UnitSeeder::class,
        AccountingPeriodSeeder::class,
        SaldoAwalSeeder::class,
    ]);
});

it('can render opening balance report page', function () {
    Livewire::test(OpeningBalanceIndex::class)
        ->assertStatus(200)
        ->assertSee('Laporan Saldo Awal')
        ->assertSee('Neraca Murni (Post-Closing)');
});

it('can switch view mode between balance_sheet and all', function () {
    Livewire::test(OpeningBalanceIndex::class)
        ->assertSet('viewMode', 'balance_sheet')
        ->set('viewMode', 'all')
        ->assertSet('viewMode', 'all')
        ->assertSee('Neraca Saldo Kumulatif (Pre-Closing)');
});

it('can export opening balance report to pdf and excel with mode', function () {
    $responsePdf = $this->get(route('accounting.reports.export.pdf', [
        'type' => 'opening-balance',
        'mode' => 'balance_sheet',
    ]));
    $responsePdf->assertStatus(200);

    $responseExcel = $this->get(route('accounting.reports.export.excel', [
        'type' => 'opening-balance',
        'mode' => 'all',
    ]));
    $responseExcel->assertStatus(200);
});
