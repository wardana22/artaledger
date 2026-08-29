<?php

use App\Domain\Accounting\Services\JournalPostingService;
use App\Livewire\Accounting\Journals\JournalIndex;
use App\Livewire\Accounting\Reports\ProfitLoss;
use App\Models\Account;
use App\Models\Company;
use App\Models\JournalType;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->company = Company::firstOrCreate(['id' => 1], ['name' => 'PT Arta Ledger Test', 'code' => 'ALT']);
    $this->journalType = JournalType::firstOrCreate(['code' => 'JK'], ['name' => 'Jurnal Kas', 'is_active' => true]);

    $this->unitKP = Unit::firstOrCreate(['code' => 'KP'], ['company_id' => $this->company->id, 'name' => 'Kantor Pusat', 'is_active' => true]);
    $this->unitRST = Unit::firstOrCreate(['code' => 'RST'], ['company_id' => $this->company->id, 'name' => 'RS Tandun', 'is_active' => true]);

    $this->accKas = Account::firstOrCreate(['code' => '11.01.01'], [
        'company_id' => $this->company->id,
        'name' => 'Kas Induk',
        'type' => 'asset',
        'normal_balance' => 'debit',
        'report_type' => 'neraca',
        'is_group' => false,
        'is_active' => true,
    ]);

    $this->accRevenue = Account::firstOrCreate(['code' => '41.01.01'], [
        'company_id' => $this->company->id,
        'name' => 'Pendapatan Jasa',
        'type' => 'revenue',
        'normal_balance' => 'credit',
        'report_type' => 'laba_rugi',
        'is_group' => false,
        'is_active' => true,
    ]);

    // Create Staff assigned ONLY to KP
    $this->staffKP = User::factory()->create(['name' => 'Staf KP User', 'email' => 'staf.kp.test@artaledger.com']);
    $staffRole = Role::where('name', 'Staf Keuangan')->first();
    if ($staffRole) {
        $this->staffKP->assignRole($staffRole);
    }
    $this->staffKP->units()->sync([$this->unitKP->id]);

    // Create Super Admin
    $this->superAdmin = User::factory()->create(['name' => 'Super Admin User', 'email' => 'admin.test@artaledger.com']);
    $adminRole = Role::where('name', 'Super Admin')->first();
    if ($adminRole) {
        $this->superAdmin->assignRole($adminRole);
    }
});

test('user helper methods properly return allowed units and ids', function () {
    $staffKP = $this->staffKP->fresh(['roles', 'units']);
    $superAdmin = $this->superAdmin->fresh(['roles', 'units']);

    expect($staffKP->hasGlobalUnitAccess())->toBeFalse();
    expect($staffKP->allowedUnitIds())->toBe([$this->unitKP->id]);
    expect($staffKP->allowedUnits()->pluck('id')->toArray())->toBe([$this->unitKP->id]);

    expect($superAdmin->hasGlobalUnitAccess())->toBeTrue();
    expect($superAdmin->allowedUnitIds())->toBe([]);
});

test('staf KP only sees KP journal entries on journal index', function () {
    $service = new JournalPostingService;

    // Create KP Journal Entry
    $journalKP = $service->postManualEntry([
        'entry_date' => date('Y-m-d'),
        'journal_type_id' => $this->journalType->id,
        'description' => 'Transaksi Khusus Unit KP',
    ], [
        ['account_id' => $this->accKas->id, 'unit_id' => $this->unitKP->id, 'debit' => 5000000, 'credit' => 0],
        ['account_id' => $this->accRevenue->id, 'unit_id' => $this->unitKP->id, 'debit' => 0, 'credit' => 5000000],
    ]);

    // Create RST Journal Entry
    $journalRST = $service->postManualEntry([
        'entry_date' => date('Y-m-d'),
        'journal_type_id' => $this->journalType->id,
        'description' => 'Transaksi Khusus Unit RST',
    ], [
        ['account_id' => $this->accKas->id, 'unit_id' => $this->unitRST->id, 'debit' => 3000000, 'credit' => 0],
        ['account_id' => $this->accRevenue->id, 'unit_id' => $this->unitRST->id, 'debit' => 0, 'credit' => 3000000],
    ]);

    // Staf KP sees ONLY KP entry
    Livewire::actingAs($this->staffKP)
        ->test(JournalIndex::class)
        ->assertSee('Transaksi Khusus Unit KP')
        ->assertDontSee('Transaksi Khusus Unit RST');

    // SuperAdmin sees BOTH entries
    Livewire::actingAs($this->superAdmin)
        ->test(JournalIndex::class)
        ->assertSee('Transaksi Khusus Unit KP')
        ->assertSee('Transaksi Khusus Unit RST');
});

test('financial report profit loss isolates numbers according to assigned user unit', function () {
    $service = new JournalPostingService;

    // Post KP revenue = 10,000,000
    $service->postManualEntry([
        'entry_date' => date('Y-m-d'),
        'journal_type_id' => $this->journalType->id,
        'description' => 'Pendapatan Unit KP',
    ], [
        ['account_id' => $this->accKas->id, 'unit_id' => $this->unitKP->id, 'debit' => 10000000, 'credit' => 0],
        ['account_id' => $this->accRevenue->id, 'unit_id' => $this->unitKP->id, 'debit' => 0, 'credit' => 10000000],
    ]);

    // Post RST revenue = 20,000,000
    $service->postManualEntry([
        'entry_date' => date('Y-m-d'),
        'journal_type_id' => $this->journalType->id,
        'description' => 'Pendapatan Unit RST',
    ], [
        ['account_id' => $this->accKas->id, 'unit_id' => $this->unitRST->id, 'debit' => 20000000, 'credit' => 0],
        ['account_id' => $this->accRevenue->id, 'unit_id' => $this->unitRST->id, 'debit' => 0, 'credit' => 20000000],
    ]);

    // Staf KP sees net profit = 10,000,000
    Livewire::actingAs($this->staffKP)
        ->test(ProfitLoss::class)
        ->assertViewHas('netProfit', 10000000.0);

    // Super Admin sees net profit = 30,000,000
    Livewire::actingAs($this->superAdmin)
        ->test(ProfitLoss::class)
        ->assertViewHas('netProfit', 30000000.0);
});
