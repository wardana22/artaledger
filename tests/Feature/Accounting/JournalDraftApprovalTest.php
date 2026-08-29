<?php

use App\Domain\Accounting\Services\JournalPostingService;
use App\Livewire\Accounting\Journals\JournalForm;
use App\Livewire\Accounting\Journals\JournalIndex;
use App\Models\Account;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalType;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->company = Company::firstOrCreate(['id' => 1], ['name' => 'PT Arta Ledger Test', 'code' => 'ALT']);
    $this->journalType = JournalType::firstOrCreate(['code' => 'JK'], ['name' => 'Jurnal Kas', 'is_active' => true]);

    $this->accKas = Account::firstOrCreate(['code' => '11.01.01'], [
        'company_id' => $this->company->id,
        'name' => 'Kas Induk',
        'type' => 'asset',
        'normal_balance' => 'debit',
        'report_type' => 'neraca',
        'is_group' => false,
        'is_active' => true,
    ]);

    $this->accModal = Account::firstOrCreate(['code' => '31.01.01'], [
        'company_id' => $this->company->id,
        'name' => 'Modal Pemilik',
        'type' => 'equity',
        'normal_balance' => 'credit',
        'report_type' => 'neraca',
        'is_group' => false,
        'is_active' => true,
    ]);

    $this->staffUser = User::factory()->create(['name' => 'Staf Keuangan']);
    $staffRole = Role::where('name', 'Staf Keuangan')->first();
    if ($staffRole) {
        $this->staffUser->assignRole($staffRole);
    }

    $this->managerUser = User::factory()->create(['name' => 'Manager Keuangan']);
    $managerRole = Role::where('name', 'Super Admin')->first();
    if ($managerRole) {
        $this->managerUser->assignRole($managerRole);
    }
});

test('manual journal entries default to draft status via livewire form', function () {
    Livewire::actingAs($this->staffUser)
        ->test(JournalForm::class)
        ->set('entry_date', date('Y-m-d'))
        ->set('journal_type_id', $this->journalType->id)
        ->set('description', 'Setoran Modal Uji Draft')
        ->set('lines', [
            ['account_id' => $this->accKas->id, 'unit_id' => null, 'description' => 'Kas', 'debit' => 5000000, 'credit' => 0],
            ['account_id' => $this->accModal->id, 'unit_id' => null, 'description' => 'Modal', 'debit' => 0, 'credit' => 5000000],
        ])
        ->call('saveDraft')
        ->assertHasNoErrors();

    $entry = JournalEntry::where('description', 'Setoran Modal Uji Draft')->first();
    expect($entry)->not->toBeNull();
    expect($entry->status)->toBe('draft');
    expect($entry->posted_at)->toBeNull();
});

test('manager with journals.post permission can approve and post draft entries', function () {
    $service = new JournalPostingService;
    $draft = $service->createDraftEntry([
        'entry_date' => date('Y-m-d'),
        'journal_type_id' => $this->journalType->id,
        'description' => 'Draft Siap Post',
    ], [
        ['account_id' => $this->accKas->id, 'debit' => 1000000, 'credit' => 0],
        ['account_id' => $this->accModal->id, 'debit' => 0, 'credit' => 1000000],
    ], $this->staffUser->id);

    expect($draft->status)->toBe('draft');

    Livewire::actingAs($this->managerUser)
        ->test(JournalIndex::class)
        ->call('postJournal', $draft->id)
        ->assertHasNoErrors();

    $draft->refresh();
    expect($draft->status)->toBe('posted');
    expect($draft->posted_by)->toBe($this->managerUser->id);
    expect($draft->posted_at)->not->toBeNull();
});

test('draft journals are excluded from posted scopes in reports', function () {
    $service = new JournalPostingService;
    $service->createDraftEntry([
        'entry_date' => date('Y-m-d'),
        'journal_type_id' => $this->journalType->id,
        'description' => 'Draft Terisolasi',
    ], [
        ['account_id' => $this->accKas->id, 'debit' => 2500000, 'credit' => 0],
        ['account_id' => $this->accModal->id, 'debit' => 0, 'credit' => 2500000],
    ], $this->staffUser->id);

    $postedEntries = JournalEntry::posted()->get();
    expect($postedEntries->pluck('description'))->not->toContain('Draft Terisolasi');
});

test('journal index component correctly binds draft status from query string', function () {
    Livewire::actingAs($this->managerUser)
        ->withQueryParams(['status' => 'draft'])
        ->test(JournalIndex::class)
        ->assertSet('statusFilter', 'draft')
        ->assertSee('Jurnal Draft');
});

test('jurnal umum tab excludes draft entries when statusFilter is all', function () {
    $service = new JournalPostingService;
    $service->createDraftEntry([
        'entry_date' => date('Y-m-d'),
        'journal_type_id' => $this->journalType->id,
        'description' => 'Draft Rahasia Tidak Boleh Muncul di Jurnal Umum',
    ], [
        ['account_id' => $this->accKas->id, 'debit' => 1000000, 'credit' => 0],
        ['account_id' => $this->accModal->id, 'debit' => 0, 'credit' => 1000000],
    ], $this->staffUser->id);

    Livewire::actingAs($this->managerUser)
        ->test(JournalIndex::class)
        ->set('statusFilter', 'all')
        ->assertDontSee('Draft Rahasia Tidak Boleh Muncul di Jurnal Umum');
});

test('posted journal entries cannot be edited or deleted directly due to audit compliance', function () {
    $service = new JournalPostingService;
    $posted = $service->postManualEntry([
        'entry_date' => date('Y-m-d'),
        'journal_type_id' => $this->journalType->id,
        'description' => 'Jurnal Terposting Terkunci Audit',
    ], [
        ['account_id' => $this->accKas->id, 'debit' => 1000000, 'credit' => 0],
        ['account_id' => $this->accModal->id, 'debit' => 0, 'credit' => 1000000],
    ], $this->managerUser->id);

    expect(fn () => $service->updateManualEntry($posted, [
        'entry_date' => date('Y-m-d'),
        'description' => 'Mencoba Edit Posted',
    ], [
        ['account_id' => $this->accKas->id, 'debit' => 2000000, 'credit' => 0],
        ['account_id' => $this->accModal->id, 'debit' => 0, 'credit' => 2000000],
    ]))->toThrow(Exception::class);

    expect(fn () => $service->deleteJournalEntry($posted))->toThrow(Exception::class);
});

test('user can open audit detail modal to inspect journal audit trail', function () {
    $service = new JournalPostingService;
    $posted = $service->postManualEntry([
        'entry_date' => date('Y-m-d'),
        'journal_type_id' => $this->journalType->id,
        'description' => 'Jurnal Inspeksi Audit Detail',
    ], [
        ['account_id' => $this->accKas->id, 'debit' => 1500000, 'credit' => 0],
        ['account_id' => $this->accModal->id, 'debit' => 0, 'credit' => 1500000],
    ], $this->managerUser->id);

    Livewire::actingAs($this->managerUser)
        ->test(JournalIndex::class)
        ->call('viewJournalDetail', $posted->id)
        ->assertSet('showDetailModal', true)
        ->assertSee('Informasi Jejak Audit')
        ->assertSee('Jurnal Inspeksi Audit Detail');
});
