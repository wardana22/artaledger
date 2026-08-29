<?php

use App\Livewire\Accounting\Journals\JournalForm;
use App\Livewire\Accounting\Journals\JournalTemplateIndex;
use App\Models\Account;
use App\Models\Company;
use App\Models\JournalTemplate;
use App\Models\JournalTemplateLine;
use App\Models\JournalType;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->company = Company::firstOrCreate(['id' => 1], ['name' => 'PT Arta Ledger Test', 'code' => 'ALT']);
    $this->journalType = JournalType::firstOrCreate(['code' => 'JK'], ['name' => 'Jurnal Kas', 'is_active' => true]);

    $this->accSewa = Account::firstOrCreate(['code' => '51.01.01'], [
        'company_id' => $this->company->id,
        'name' => 'Beban Sewa Kantor',
        'type' => 'expense',
        'normal_balance' => 'debit',
        'report_type' => 'laba_rugi',
        'is_group' => false,
        'is_active' => true,
    ]);

    $this->accKas = Account::firstOrCreate(['code' => '11.01.01'], [
        'company_id' => $this->company->id,
        'name' => 'Kas Induk',
        'type' => 'asset',
        'normal_balance' => 'debit',
        'report_type' => 'neraca',
        'is_group' => false,
        'is_active' => true,
    ]);

    $this->staffUser = User::factory()->create(['name' => 'Staf Keuangan']);
    $staffRole = Role::where('name', 'Staf Keuangan')->first();
    if ($staffRole) {
        $this->staffUser->assignRole($staffRole);
    }
});

test('staff user can view journal template list and create new template', function () {
    Livewire::actingAs($this->staffUser)
        ->test(JournalTemplateIndex::class)
        ->set('template_code', 'TPL-SEWA-01')
        ->set('name', 'Template Sewa Bulanan')
        ->set('description', 'Beban Sewa Kantor Bulanan')
        ->set('journal_type_id', $this->journalType->id)
        ->set('lines', [
            ['account_id' => $this->accSewa->id, 'unit_id' => null, 'description' => 'Sewa', 'debit' => 0, 'credit' => 0],
            ['account_id' => $this->accKas->id, 'unit_id' => null, 'description' => 'Kas', 'debit' => 0, 'credit' => 0],
        ])
        ->call('saveTemplate')
        ->assertHasNoErrors();

    $tpl = JournalTemplate::where('template_code', 'TPL-SEWA-01')->first();
    expect($tpl)->not->toBeNull();
    expect($tpl->name)->toBe('Template Sewa Bulanan');
    expect($tpl->lines->count())->toBe(2);
});

test('selecting journal template pre-fills accounts into journal form', function () {
    $tpl = JournalTemplate::create([
        'company_id' => $this->company->id,
        'template_code' => 'TPL-LISTRIK',
        'name' => 'Template Beban Listrik',
        'description' => 'Pembayaran Listrik Bulanan',
        'journal_type_id' => $this->journalType->id,
        'created_by' => $this->staffUser->id,
    ]);

    JournalTemplateLine::create([
        'journal_template_id' => $tpl->id,
        'line_no' => 1,
        'account_id' => $this->accSewa->id,
        'debit' => 0,
        'credit' => 0,
    ]);

    JournalTemplateLine::create([
        'journal_template_id' => $tpl->id,
        'line_no' => 2,
        'account_id' => $this->accKas->id,
        'debit' => 0,
        'credit' => 0,
    ]);

    Livewire::actingAs($this->staffUser)
        ->test(JournalForm::class)
        ->set('selectedTemplateId', $tpl->id)
        ->assertSet('description', 'Pembayaran Listrik Bulanan')
        ->assertSet('journal_type_id', $this->journalType->id);
});
