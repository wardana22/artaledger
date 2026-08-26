<?php

use App\Livewire\Accounting\Reports\ProfitLoss;
use App\Models\Account;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->company = Company::create(['name' => 'PT Test Profit Loss', 'code' => 'PTPL']);
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('profit loss component renders successfully with 4-column excel audited layout', function () {
    $revParent = Account::create([
        'company_id' => $this->company->id,
        'code' => '41',
        'name' => 'PENDAPATAN KLINIK',
        'type' => 'revenue',
        'normal_balance' => 'credit',
        'report_type' => 'laba_rugi',
        'is_group' => true,
        'level' => 2,
    ]);

    $revChild = Account::create([
        'company_id' => $this->company->id,
        'code' => '41.01',
        'name' => 'Pendapatan Rawat Jalan',
        'type' => 'revenue',
        'normal_balance' => 'credit',
        'report_type' => 'laba_rugi',
        'is_group' => false,
        'parent_id' => $revParent->id,
        'level' => 3,
    ]);

    $expParent = Account::create([
        'company_id' => $this->company->id,
        'code' => '51',
        'name' => 'BEBAN GAJI OPERASIONAL',
        'type' => 'expense',
        'normal_balance' => 'debit',
        'report_type' => 'laba_rugi',
        'is_group' => true,
        'level' => 2,
    ]);

    $expChild = Account::create([
        'company_id' => $this->company->id,
        'code' => '51.01',
        'name' => 'Beban Gaji Dokter',
        'type' => 'expense',
        'normal_balance' => 'debit',
        'report_type' => 'laba_rugi',
        'is_group' => false,
        'parent_id' => $expParent->id,
        'level' => 3,
    ]);

    $entry = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-PL-001',
        'entry_date' => date('Y-01-15'),
        'description' => 'Pendapatan & Beban Test',
        'status' => 'posted',
        'created_by' => $this->user->id,
    ]);

    JournalLine::create([
        'journal_entry_id' => $entry->id,
        'account_id' => $revChild->id,
        'debit' => 0,
        'credit' => 50000000,
    ]);

    JournalLine::create([
        'journal_entry_id' => $entry->id,
        'account_id' => $expChild->id,
        'debit' => 20000000,
        'credit' => 0,
    ]);

    Livewire::test(ProfitLoss::class)
        ->assertStatus(200)
        ->assertSee('PENDAPATAN KLINIK')
        ->assertSee('Pendapatan Rawat Jalan')
        ->assertSee('BEBAN GAJI OPERASIONAL')
        ->assertSee('Beban Gaji Dokter')
        ->assertSee('LABA / RUGI KOTOR')
        ->assertSee('LABA (RUGI) BERSIH PERIODE BERJALAN');
});

test('profit loss toggleAccount expands level 4 child accounts', function () {
    $parentLevel3 = Account::create([
        'company_id' => $this->company->id,
        'code' => '52.01',
        'name' => 'Pemakaian Obat Medis',
        'type' => 'expense',
        'normal_balance' => 'debit',
        'report_type' => 'laba_rugi',
        'is_group' => true,
        'level' => 3,
    ]);

    $childLevel4 = Account::create([
        'company_id' => $this->company->id,
        'code' => '52.01.001',
        'name' => 'Pemakaian Obat Generik FKTP',
        'type' => 'expense',
        'normal_balance' => 'debit',
        'report_type' => 'laba_rugi',
        'is_group' => false,
        'parent_id' => $parentLevel3->id,
        'level' => 4,
    ]);

    $entry = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-PL-002',
        'entry_date' => date('Y-01-20'),
        'description' => 'Obat Test',
        'status' => 'posted',
        'created_by' => $this->user->id,
    ]);

    JournalLine::create([
        'journal_entry_id' => $entry->id,
        'account_id' => $childLevel4->id,
        'debit' => 12500000,
        'credit' => 0,
    ]);

    Livewire::test(ProfitLoss::class)
        ->assertDontSee('Pemakaian Obat Generik FKTP')
        ->call('toggleAccount', $parentLevel3->id)
        ->assertSee('Pemakaian Obat Generik FKTP')
        ->call('toggleAccount', $parentLevel3->id)
        ->assertDontSee('Pemakaian Obat Generik FKTP');
});
