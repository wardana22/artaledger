<?php

use App\Models\Account;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');

    $this->company = Company::firstOrCreate(
        ['code' => 'ARTALEDGER'],
        [
            'id' => 1,
            'name' => 'PT ArtaLedger Enterprise',
            'fiscal_year_start' => 1,
            'is_active' => true,
        ]
    );

    $this->unit = Unit::firstOrCreate(
        ['id' => 1],
        [
            'company_id' => $this->company->id,
            'name' => 'Kantor Pusat',
            'code' => 'KP',
            'is_active' => true,
        ]
    );

    $this->kasAccount = Account::create([
        'company_id' => $this->company->id,
        'code' => '11.01.001',
        'name' => 'Kas Operasional',
        'type' => 'KAS',
        'normal_balance' => 'debit',
        'report_type' => 'neraca',
        'is_group' => false,
        'is_active' => true,
    ]);

    $this->bebanAccount = Account::create([
        'company_id' => $this->company->id,
        'code' => '61.07.001',
        'name' => 'Beban Jasa Audit',
        'type' => 'BEBAN',
        'normal_balance' => 'debit',
        'report_type' => 'laba_rugi',
        'is_group' => false,
        'is_active' => true,
    ]);

    // Create a sample posted journal entry
    $this->entry1 = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-2026-01-000001',
        'document_number' => 'DOC-001',
        'entry_date' => '2026-01-15',
        'description' => 'Pembayaran Beban Audit',
        'status' => 'posted',
        'source_type' => 'manual',
        'posted_by' => $this->user->id,
        'posted_at' => now(),
    ]);

    JournalLine::create([
        'journal_entry_id' => $this->entry1->id,
        'account_id' => $this->bebanAccount->id,
        'unit_id' => $this->unit->id,
        'description' => 'Beban Audit',
        'debit' => 5000000,
        'credit' => 0,
    ]);

    JournalLine::create([
        'journal_entry_id' => $this->entry1->id,
        'account_id' => $this->kasAccount->id,
        'unit_id' => $this->unit->id,
        'description' => 'Beban Audit Kas',
        'debit' => 0,
        'credit' => 5000000,
    ]);
});

test('user with journals.view permission can export journal register pdf', function () {
    $response = $this->actingAs($this->user)
        ->get(route('accounting.journals.export.pdf', [
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'status' => 'all',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});

test('user with journals.view permission can export journal register excel', function () {
    $response = $this->actingAs($this->user)
        ->get(route('accounting.journals.export.excel', [
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'status' => 'all',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

test('unauthorized user cannot export journal register', function () {
    $guestUser = User::factory()->create();

    $this->actingAs($guestUser)
        ->get(route('accounting.journals.export.pdf'))
        ->assertForbidden();

    $this->actingAs($guestUser)
        ->get(route('accounting.journals.export.excel'))
        ->assertForbidden();
});
