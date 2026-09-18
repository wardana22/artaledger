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
            'prepared_by_name' => 'Staff Akuntansi Test',
            'prepared_by_title' => 'Bagian Keuangan',
            'reviewed_by_name' => 'Manager Akuntansi Test',
            'reviewed_by_title' => 'Accounting Lead',
            'approved_by_name' => 'Direktur Keuangan Test',
            'approved_by_title' => 'Chief Financial Officer (CFO)',
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
        'name' => 'Beban Jasa Audit & Konsultansi',
        'type' => 'BEBAN',
        'normal_balance' => 'debit',
        'report_type' => 'laba_rugi',
        'is_group' => false,
        'is_active' => true,
    ]);

    // Create a sample journal entry
    $this->entry = JournalEntry::create([
        'company_id' => $this->company->id,
        'entry_number' => 'JU-2026-01-000284',
        'document_number' => 'KM-01.002/2026',
        'entry_date' => '2026-01-31',
        'description' => 'Biaya Jasa Audit Tahun 2025',
        'status' => 'posted',
        'source_type' => 'manual',
        'posted_by' => $this->user->id,
        'posted_at' => now(),
    ]);

    JournalLine::create([
        'journal_entry_id' => $this->entry->id,
        'account_id' => $this->bebanAccount->id,
        'unit_id' => $this->unit->id,
        'description' => 'Biaya Jasa Audit Tahun 2025',
        'debit' => 28952259.17,
        'credit' => 0,
    ]);

    JournalLine::create([
        'journal_entry_id' => $this->entry->id,
        'account_id' => $this->kasAccount->id,
        'unit_id' => $this->unit->id,
        'description' => 'Biaya Jasa Audit Tahun 2025',
        'debit' => 0,
        'credit' => 28952259.17,
    ]);
});

test('user with journals.view permission can export journal voucher pdf', function () {
    $response = $this->actingAs($this->user)
        ->get(route('accounting.journals.pdf', $this->entry->id));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});

test('unauthorized user cannot export journal voucher pdf', function () {
    $guestUser = User::factory()->create(); // no roles/permissions

    $response = $this->actingAs($guestUser)
        ->get(route('accounting.journals.pdf', $this->entry->id));

    $response->assertForbidden();
});

test('journal voucher pdf can be downloaded via query param', function () {
    $response = $this->actingAs($this->user)
        ->get(route('accounting.journals.pdf', ['id' => $this->entry->id, 'download' => 1]));

    $response->assertOk();
    $response->assertHeader('content-disposition', 'attachment; filename=Bukti-Jurnal-'.$this->entry->id.'.pdf');
});
