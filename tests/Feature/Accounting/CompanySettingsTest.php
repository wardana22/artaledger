<?php

use App\Livewire\Accounting\Settings\CompanySettingsIndex;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $this->company = Company::firstOrCreate(['id' => 1], [
        'code' => 'ALT',
        'name' => 'PT Arta Ledger Test',
        'app_name' => 'ArtaLedger',
    ]);

    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('Super Admin');
});

test('super admin can render company settings page', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CompanySettingsIndex::class)
        ->assertStatus(200)
        ->assertSee('Pengaturan Branding')
        ->assertSee('ArtaLedger');
});

test('super admin can update company application name and company metadata', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CompanySettingsIndex::class)
        ->set('app_name', 'ArtaLedger Enterprise')
        ->set('name', 'PT Arta Ledger Indonesia Mandiri')
        ->set('code', 'ALIM')
        ->set('email', 'corporate@artaledger.com')
        ->set('phone', '+62 21 555 8899')
        ->set('address', 'Gedung Arta Tower Lt. 12, Jakarta')
        ->set('tax_number', '01.234.567.8-012.000')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('companies', [
        'id' => $this->company->id,
        'app_name' => 'ArtaLedger Enterprise',
        'name' => 'PT Arta Ledger Indonesia Mandiri',
        'code' => 'ALIM',
        'email' => 'corporate@artaledger.com',
        'phone' => '+62 21 555 8899',
    ]);
});

test('super admin can upload and remove company logo', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('company-logo.png', 200, 200);

    Livewire::actingAs($this->adminUser)
        ->test(CompanySettingsIndex::class)
        ->set('logo', $file)
        ->call('save')
        ->assertHasNoErrors();

    $company = Company::first();
    expect($company->logo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($company->logo_path);

    // Remove logo
    Livewire::actingAs($this->adminUser)
        ->test(CompanySettingsIndex::class)
        ->call('removeLogo')
        ->assertHasNoErrors();

    $company->refresh();
    expect($company->logo_path)->toBeNull();
});

test('unauthorized user without permission receives 403 on company settings', function () {
    $regularUser = User::factory()->create();

    Livewire::actingAs($regularUser)
        ->test(CompanySettingsIndex::class)
        ->assertStatus(403);
});

test('super admin can configure financial report signers', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CompanySettingsIndex::class)
        ->assertSee('Penandatangan Laporan')
        ->set('prepared_by_name', 'Budi Santoso')
        ->set('prepared_by_title', 'Senior Staff Akuntansi')
        ->set('reviewed_by_name', 'Dewi Lestari, S.E.')
        ->set('reviewed_by_title', 'Head of Accounting & Tax')
        ->set('approved_by_name', 'Hendra Wijaya, MBA')
        ->set('approved_by_title', 'VP Finance & Strategy')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('companies', [
        'id' => $this->company->id,
        'prepared_by_name' => 'Budi Santoso',
        'prepared_by_title' => 'Senior Staff Akuntansi',
        'reviewed_by_name' => 'Dewi Lestari, S.E.',
        'reviewed_by_title' => 'Head of Accounting & Tax',
        'approved_by_name' => 'Hendra Wijaya, MBA',
        'approved_by_title' => 'VP Finance & Strategy',
    ]);
});
