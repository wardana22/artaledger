<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Standard System Permissions per Module
        $permissions = [
            // Dashboard & Analytics Modul
            'dashboard.view' => 'Lihat Halaman Dashboard',
            'dashboard.settings' => 'Kelola Pengaturan Dashboard',
            'dashboard.kpis.manage' => 'Kelola (CRUD) Kartu KPI Finansial',

            // Master COA Modul
            'accounts.view' => 'Lihat Master COA',
            'accounts.create' => 'Tambah Akun Baru',
            'accounts.edit' => 'Edit Akun',
            'accounts.delete' => 'Hapus Akun',

            // Transaksi Jurnal & Import Modul
            'journals.view' => 'Lihat Jurnal Transaksi',
            'journals.create' => 'Buat Jurnal Baru',
            'journals.edit' => 'Edit Draft Jurnal',
            'journals.post' => 'Setujui & Posting Jurnal',
            'journals.delete' => 'Hapus Jurnal Transaksi',
            'journals.import' => 'Import Jurnal Excel',

            // Periode Akuntansi Modul
            'periods.view' => 'Lihat Periode Akuntansi',
            'periods.manage' => 'Kelola & Tutup Periode Akuntansi',
            'periods.manage_keys' => 'Kelola Lock Key Periode (SuperAdmin)',

            // Laporan Keuangan Sub-Modul Permissions
            'reports.general_ledger' => 'Lihat Buku Besar Header',
            'reports.subsidiary_ledger' => 'Lihat Buku Besar Pembantu',
            'reports.worksheet' => 'Lihat Neraca Lajur 10-Kolom',
            'reports.trial_balance' => 'Lihat Neraca Saldo',
            'reports.balance_sheet' => 'Lihat Laporan Neraca Klasifikasi',
            'reports.profit_loss' => 'Lihat Laporan Laba Rugi',
            'reports.cash_flow' => 'Lihat Laporan Arus Kas',
            'reports.opening_balance' => 'Lihat & Input Saldo Awal',
            'reports.changes_in_equity' => 'Lihat Laporan Perubahan Ekuitas',
            'reports.view' => 'Lihat Seluruh Laporan Keuangan (Global)',
            'reports.export' => 'Ekspor Laporan Keuangan (Excel/PDF)',

            // Master Pengaturan Modul
            'settings.view' => 'Lihat Pengaturan System',
            'settings.company' => 'Kelola Branding & Pengaturan Perusahaan',
            'settings.units' => 'Kelola Unit Perusahaan',
            'settings.journal_types' => 'Kelola Jenis Jurnal',
            'settings.templates' => 'Kelola Template Jurnal',
            'settings.manage' => 'Kelola Master Unit & Jenis Jurnal',

            // Manajemen Pengguna & Security Audit Modul
            'admin.users' => 'Kelola Pengguna & Penugasan Unit',
            'admin.roles' => 'Kelola Peran & Hak Akses (RBAC)',
            'admin.audit_logs' => 'Lihat Audit Log Aktivitas',
            'settings.manage_roles' => 'Kelola Peran & Hak Akses',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // Create Default Roles
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $accountant = Role::firstOrCreate(['name' => 'Akuntan / Finance Manager']);
        $accountant->givePermissionTo([
            'dashboard.view', 'dashboard.settings', 'dashboard.kpis.manage',
            'accounts.view', 'accounts.create', 'accounts.edit', 'accounts.delete',
            'journals.view', 'journals.create', 'journals.edit', 'journals.post', 'journals.delete', 'journals.import',
            'periods.view', 'periods.manage',
            'reports.general_ledger', 'reports.subsidiary_ledger', 'reports.worksheet', 'reports.trial_balance',
            'reports.balance_sheet', 'reports.profit_loss', 'reports.cash_flow', 'reports.opening_balance',
            'reports.changes_in_equity', 'reports.view', 'reports.export',
            'settings.view', 'settings.units', 'settings.journal_types', 'settings.templates', 'settings.manage',
        ]);

        $staff = Role::firstOrCreate(['name' => 'Staf Keuangan']);
        $staff->givePermissionTo([
            'accounts.view',
            'journals.view', 'journals.create', 'journals.edit',
            'periods.view',
            'reports.general_ledger', 'reports.profit_loss', 'reports.view',
            'settings.view', 'settings.journal_types', 'settings.templates',
        ]);

        $auditor = Role::firstOrCreate(['name' => 'Auditor / Viewer']);
        $auditor->givePermissionTo([
            'accounts.view',
            'journals.view',
            'periods.view',
            'reports.general_ledger', 'reports.subsidiary_ledger', 'reports.trial_balance', 'reports.balance_sheet',
            'reports.profit_loss', 'reports.cash_flow', 'reports.changes_in_equity', 'reports.view', 'reports.export',
            'admin.audit_logs',
        ]);

        // Assign Super Admin role to default admin user if present
        $user = User::where('email', 'admin@artaledger.com')->first();
        if ($user) {
            $user->assignRole($superAdmin);
        }
    }
}
