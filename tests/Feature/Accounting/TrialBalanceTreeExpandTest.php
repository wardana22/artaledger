<?php

use App\Livewire\Accounting\Reports\TrialBalance;
use App\Models\Account;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->company = Company::create(['name' => 'PT Test Indonesia', 'code' => 'PTTI']);
    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    $this->actingAs($this->user);
});

test('trial balance component renders successfully and displays level 1 2 3 accounts by default', function () {
    $parent = Account::create([
        'company_id' => $this->company->id,
        'code' => '51',
        'name' => 'BEBAN OPERASIONAL',
        'type' => 'expense',
        'normal_balance' => 'debit',
        'report_type' => 'laba_rugi',
        'is_group' => true,
        'level' => 2,
    ]);

    $child = Account::create([
        'company_id' => $this->company->id,
        'code' => '51.01',
        'name' => 'Beban Gaji',
        'type' => 'expense',
        'normal_balance' => 'debit',
        'report_type' => 'laba_rugi',
        'is_group' => true,
        'parent_id' => $parent->id,
        'level' => 3,
    ]);

    $subChild = Account::create([
        'company_id' => $this->company->id,
        'code' => '51.01.001',
        'name' => 'Gaji Dokter',
        'type' => 'expense',
        'normal_balance' => 'debit',
        'report_type' => 'laba_rugi',
        'is_group' => false,
        'parent_id' => $child->id,
        'level' => 4,
    ]);

    $entry = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-TEST-001',
        'entry_date' => date('Y-01-15'),
        'description' => 'Test Gaji',
        'status' => 'posted',
        'created_by' => $this->user->id,
    ]);

    JournalLine::create([
        'journal_entry_id' => $entry->id,
        'account_id' => $subChild->id,
        'debit' => 5000000,
        'credit' => 0,
    ]);

    Livewire::test(TrialBalance::class)
        ->assertStatus(200)
        ->assertSee('BEBAN OPERASIONAL')
        ->assertSee('Beban Gaji')
        ->assertDontSee('Gaji Dokter');
});

test('trial balance toggleAccount expands child level 4 accounts', function () {
    $parent = Account::create([
        'company_id' => $this->company->id,
        'code' => '52',
        'name' => 'BEBAN PEMELIHARAAN',
        'type' => 'expense',
        'normal_balance' => 'debit',
        'report_type' => 'laba_rugi',
        'is_group' => true,
        'level' => 2,
    ]);

    $child = Account::create([
        'company_id' => $this->company->id,
        'code' => '52.01',
        'name' => 'Beban Gedung',
        'type' => 'expense',
        'normal_balance' => 'debit',
        'report_type' => 'laba_rugi',
        'is_group' => true,
        'parent_id' => $parent->id,
        'level' => 3,
    ]);

    $subChild = Account::create([
        'company_id' => $this->company->id,
        'code' => '52.01.001',
        'name' => 'Pemeliharaan AC',
        'type' => 'expense',
        'normal_balance' => 'debit',
        'report_type' => 'laba_rugi',
        'is_group' => false,
        'parent_id' => $child->id,
        'level' => 4,
    ]);

    $entry = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-TEST-002',
        'entry_date' => date('Y-01-20'),
        'description' => 'Test AC',
        'status' => 'posted',
        'created_by' => $this->user->id,
    ]);

    JournalLine::create([
        'journal_entry_id' => $entry->id,
        'account_id' => $subChild->id,
        'debit' => 1500000,
        'credit' => 0,
    ]);

    Livewire::test(TrialBalance::class)
        ->assertDontSee('Pemeliharaan AC')
        ->call('toggleAccount', $child->id)
        ->assertSee('Pemeliharaan AC')
        ->call('toggleAccount', $child->id)
        ->assertDontSee('Pemeliharaan AC');
});
