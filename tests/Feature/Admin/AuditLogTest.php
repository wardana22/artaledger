<?php

use App\Domain\Accounting\Services\JournalPostingService;
use App\Livewire\Admin\AuditLogIndex;
use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\JournalType;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
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

    $this->staffUser = User::factory()->create(['name' => 'Staf Audit']);
    $staffRole = Role::where('name', 'Staf Keuangan')->first();
    if ($staffRole) {
        $this->staffUser->assignRole($staffRole);
    }
});

test('user login and logout events record audit logs automatically', function () {
    event(new Login('web', $this->staffUser, false));

    $loginLog = AuditLog::where('user_id', $this->staffUser->id)
        ->where('event_type', 'auth.login')
        ->first();

    expect($loginLog)->not->toBeNull();
    expect($loginLog->description)->toContain('berhasil Login');

    event(new Logout('web', $this->staffUser));

    $logoutLog = AuditLog::where('user_id', $this->staffUser->id)
        ->where('event_type', 'auth.logout')
        ->first();

    expect($logoutLog)->not->toBeNull();
    expect($logoutLog->description)->toContain('telah Logout');
});

test('journal posting records audit log entry with entry number', function () {
    $service = new JournalPostingService;
    $journal = $service->postManualEntry([
        'entry_date' => date('Y-m-d'),
        'journal_type_id' => $this->journalType->id,
        'description' => 'Uji Audit Posting Jurnal',
    ], [
        ['account_id' => $this->accKas->id, 'debit' => 1000000, 'credit' => 0],
        ['account_id' => $this->accModal->id, 'debit' => 0, 'credit' => 1000000],
    ], $this->staffUser->id);

    $log = AuditLog::where('event_type', 'journal.posted')
        ->where('auditable_id', $journal->id)
        ->first();

    expect($log)->not->toBeNull();
    expect($log->user_id)->toBe($this->staffUser->id);
    expect($log->description)->toContain($journal->entry_number);
});

test('audit log index livewire component displays records and filters correctly', function () {
    $this->staffUser->givePermissionTo('admin.audit_logs');
    event(new Login('web', $this->staffUser, false));

    Livewire::actingAs($this->staffUser)
        ->test(AuditLogIndex::class)
        ->set('eventFilter', 'auth')
        ->assertSee('AUTH.LOGIN')
        ->assertSee('Staf Audit');
});
