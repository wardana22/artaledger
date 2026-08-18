<?php

use App\Domain\Accounting\Services\AccountSeederService;
use App\Livewire\Accounting\Accounts\AccountIndex;
use App\Models\Account;
use App\Models\Company;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('accounts can be seeded from scratch JSON files', function () {
    $service = new AccountSeederService;
    $count = $service->seedFromData();

    expect($count)->toBeGreaterThan(0);
    expect(Account::count())->toBeGreaterThan(0);

    $asset = Account::where('code', '1')->first();
    expect($asset)->not->toBeNull();
    expect($asset->is_group)->toBeTrue();
});

test('master coa page automatically logs in dev user if unauthenticated', function () {
    $this->get(route('accounting.accounts.index'))
        ->assertOk();
    expect(Auth::check())->toBeTrue();
});

test('authenticated user can view master coa page', function () {
    (new AccountSeederService)->seedFromData();

    $this->actingAs($this->user)
        ->get(route('accounting.accounts.index'))
        ->assertOk();
});

test('livewire account index component can render and filter accounts', function () {
    (new AccountSeederService)->seedFromData();

    Livewire::actingAs($this->user)
        ->test(AccountIndex::class)
        ->assertOk()
        ->set('search', 'KAS')
        ->assertSee('KAS')
        ->set('viewMode', 'tree')
        ->assertOk();
});

test('user can create a new account via livewire', function () {
    $company = Company::firstOrCreate(['code' => 'ARTALEDGER'], ['name' => 'PT ArtaLedger']);

    Livewire::actingAs($this->user)
        ->test(AccountIndex::class)
        ->set('code', '99.99.99')
        ->set('name', 'AKUN UJI PEST')
        ->set('normal_balance', 'debit')
        ->set('report_type', 'neraca')
        ->set('is_group', false)
        ->call('saveAccount')
        ->assertHasNoErrors();

    expect(Account::where('code', '99.99.99')->exists())->toBeTrue();
});

test('user can create a child account pre-filled from parent account', function () {
    (new AccountSeederService)->seedFromData();
    $parent = Account::where('code', '11.01')->first();

    Livewire::actingAs($this->user)
        ->test(AccountIndex::class)
        ->call('createChildAccount', $parent->id)
        ->assertSet('parent_id', $parent->id)
        ->assertSet('code', '11.01.')
        ->assertSet('normal_balance', $parent->normal_balance)
        ->assertSet('report_type', $parent->report_type)
        ->set('code', '11.01.99')
        ->set('name', 'KAS KHUSUS PEST')
        ->call('saveAccount')
        ->assertHasNoErrors();

    $child = Account::where('code', '11.01.99')->first();
    expect($child)->not->toBeNull();
    expect($child->parent_id)->toBe($parent->id);
});
