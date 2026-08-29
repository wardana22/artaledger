<?php

use App\Domain\Accounting\Services\AccountSeederService;
use App\Domain\Accounting\Services\JournalPostingService;
use App\Domain\Accounting\Services\JournalReversalService;
use App\Livewire\Accounting\Journals\AdjustmentForm;
use App\Livewire\Accounting\Journals\AdjustmentIndex;
use App\Livewire\Accounting\Periods\PeriodIndex;
use App\Livewire\Accounting\Reports\GeneralLedger;
use App\Livewire\Accounting\Reports\SubsidiaryLedger;
use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    (new AccountSeederService)->seedFromData();
    $this->company = Company::first();
});

test('can manage accounting periods via livewire', function () {
    Livewire::actingAs($this->user)
        ->test(PeriodIndex::class)
        ->assertOk()
        ->call('generateYearPeriods')
        ->assertHasNoErrors();

    expect(AccountingPeriod::count())->toBe(12);
});

test('journal posting service validates balance and posts entry', function () {
    $kas = Account::where('code', '11.01.01')->first();
    $bank = Account::where('code', '11.02.01')->first() ?? Account::where('code', '11.02')->first();

    // Make sure $bank is posting account
    if ($bank->is_group) {
        $bank->update(['is_group' => false]);
    }

    $service = new JournalPostingService;
    $entry = $service->postManualEntry(
        [
            'company_id' => $this->company->id,
            'entry_date' => date('Y-m-d'),
            'description' => 'Transfer Kas ke Bank',
        ],
        [
            ['account_id' => $bank->id, 'debit' => 500000, 'credit' => 0],
            ['account_id' => $kas->id, 'debit' => 0, 'credit' => 500000],
        ],
        $this->user->id
    );

    expect($entry->status)->toBe('posted');
    expect($entry->lines()->count())->toBe(2);
    expect($entry->is_balanced)->toBeTrue();
});

test('journal posting service throws exception on unbalanced entry', function () {
    $kas = Account::where('code', '11.01.01')->first();
    $bank = Account::where('code', '11.02.01')->first() ?? Account::where('code', '11.02')->first();

    $service = new JournalPostingService;

    $this->expectException(Exception::class);
    $service->postManualEntry(
        [
            'company_id' => $this->company->id,
            'entry_date' => date('Y-m-d'),
            'description' => 'Transfer Kas Unbalanced',
        ],
        [
            ['account_id' => $bank->id, 'debit' => 500000, 'credit' => 0],
            ['account_id' => $kas->id, 'debit' => 0, 'credit' => 100000],
        ],
        $this->user->id
    );
});

test('journal reversal service creates reversing entry', function () {
    $kas = Account::where('code', '11.01.01')->first();
    $bank = Account::where('code', '11.02.01')->first() ?? Account::where('code', '11.02')->first();
    if ($bank->is_group) {
        $bank->update(['is_group' => false]);
    }

    $postingService = new JournalPostingService;
    $entry = $postingService->postManualEntry(
        [
            'company_id' => $this->company->id,
            'entry_date' => date('Y-m-d'),
            'description' => 'Jurnal Utama',
        ],
        [
            ['account_id' => $bank->id, 'debit' => 500000, 'credit' => 0],
            ['account_id' => $kas->id, 'debit' => 0, 'credit' => 500000],
        ],
        $this->user->id
    );

    $reversalService = new JournalReversalService;
    $reversal = $reversalService->reverseJournalEntry($entry, $this->user->id, 'Pembatalan kesalahan');

    expect($entry->fresh()->status)->toBe('reversed');
    expect($reversal->status)->toBe('posted');
    expect((float) $reversal->lines()->where('account_id', $bank->id)->first()->credit)->toBe(500000.0);
});

test('general ledger report for header accounts renders correctly', function () {
    Livewire::actingAs($this->user)
        ->test(GeneralLedger::class)
        ->assertOk();
});

test('subsidiary ledger report for posting accounts renders correctly', function () {
    Livewire::actingAs($this->user)
        ->test(SubsidiaryLedger::class)
        ->assertOk();
});

test('journal posting service creates adjustment entry with AJP prefix', function () {
    $kas = Account::where('code', '11.01.01')->first();
    $bank = Account::where('code', '11.02.01')->first() ?? Account::where('code', '11.02')->first();
    if ($bank->is_group) {
        $bank->update(['is_group' => false]);
    }

    $service = new JournalPostingService;
    $entry = $service->postManualEntry(
        [
            'company_id' => $this->company->id,
            'entry_date' => date('Y-m-d'),
            'description' => 'Penyesuaian Penyusutan',
        ],
        [
            ['account_id' => $bank->id, 'debit' => 250000, 'credit' => 0],
            ['account_id' => $kas->id, 'debit' => 0, 'credit' => 250000],
        ],
        $this->user->id,
        'adjustment'
    );

    expect($entry->entry_type)->toBe('adjustment');
    expect($entry->entry_number)->toContain('AJP-');
});

test('adjustment index and form livewire components render correctly', function () {
    Livewire::actingAs($this->user)
        ->test(AdjustmentIndex::class)
        ->assertOk();

    Livewire::actingAs($this->user)
        ->test(AdjustmentForm::class)
        ->assertOk();
});
