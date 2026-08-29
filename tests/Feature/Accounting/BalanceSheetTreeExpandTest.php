<?php

use App\Livewire\Accounting\Reports\BalanceSheet;
use App\Models\Account;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->company = Company::create(['name' => 'PT Test Balance Sheet', 'code' => 'PTBS']);
    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    $this->actingAs($this->user);
});

test('balance sheet component renders successfully and displays level 1 2 3 accounts by default', function () {
    $assetParent = Account::create([
        'company_id' => $this->company->id,
        'code' => '11',
        'name' => 'ASET LANCAR',
        'type' => 'asset',
        'normal_balance' => 'debit',
        'report_type' => 'neraca',
        'is_group' => true,
        'level' => 2,
    ]);

    $assetChild = Account::create([
        'company_id' => $this->company->id,
        'code' => '11.01',
        'name' => 'Kas dan Bank',
        'type' => 'asset',
        'normal_balance' => 'debit',
        'report_type' => 'neraca',
        'is_group' => true,
        'parent_id' => $assetParent->id,
        'level' => 3,
    ]);

    $assetSubChild = Account::create([
        'company_id' => $this->company->id,
        'code' => '11.01.001',
        'name' => 'Kas Kecil Operational',
        'type' => 'asset',
        'normal_balance' => 'debit',
        'report_type' => 'neraca',
        'is_group' => false,
        'parent_id' => $assetChild->id,
        'level' => 4,
    ]);

    $entry = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-BS-001',
        'entry_date' => date('Y-01-15'),
        'description' => 'Kas Kecil Test',
        'status' => 'posted',
        'created_by' => $this->user->id,
    ]);

    JournalLine::create([
        'journal_entry_id' => $entry->id,
        'account_id' => $assetSubChild->id,
        'debit' => 2500000,
        'credit' => 0,
    ]);

    Livewire::test(BalanceSheet::class)
        ->assertStatus(200)
        ->assertSee('ASET LANCAR')
        ->assertSee('Kas dan Bank')
        ->assertDontSee('Kas Kecil Operational');
});

test('balance sheet toggleAccount expands child level 4 accounts', function () {
    $assetParent = Account::create([
        'company_id' => $this->company->id,
        'code' => '12',
        'name' => 'ASET TETAP',
        'type' => 'asset',
        'normal_balance' => 'debit',
        'report_type' => 'neraca',
        'is_group' => true,
        'level' => 2,
    ]);

    $assetChild = Account::create([
        'company_id' => $this->company->id,
        'code' => '12.01',
        'name' => 'Peralatan Medis',
        'type' => 'asset',
        'normal_balance' => 'debit',
        'report_type' => 'neraca',
        'is_group' => true,
        'parent_id' => $assetParent->id,
        'level' => 3,
    ]);

    $assetSubChild = Account::create([
        'company_id' => $this->company->id,
        'code' => '12.01.001',
        'name' => 'USG 4D Machine',
        'type' => 'asset',
        'normal_balance' => 'debit',
        'report_type' => 'neraca',
        'is_group' => false,
        'parent_id' => $assetChild->id,
        'level' => 4,
    ]);

    $entry = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-BS-002',
        'entry_date' => date('Y-01-20'),
        'description' => 'USG Test',
        'status' => 'posted',
        'created_by' => $this->user->id,
    ]);

    JournalLine::create([
        'journal_entry_id' => $entry->id,
        'account_id' => $assetSubChild->id,
        'debit' => 150000000,
        'credit' => 0,
    ]);

    Livewire::test(BalanceSheet::class)
        ->assertDontSee('USG 4D Machine')
        ->call('toggleAccount', $assetChild->id)
        ->assertSee('USG 4D Machine')
        ->call('toggleAccount', $assetChild->id)
        ->assertDontSee('USG 4D Machine');
});
