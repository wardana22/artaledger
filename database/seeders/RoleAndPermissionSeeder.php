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

        // Standard System Permissions
        $permissions = [
            // Master COA Modul
            'accounts.view' => 'Melihat Daftar & Pohon Master COA',
            'accounts.create' => 'Menambah Akun COA Baru',
            'accounts.edit' => 'Mengedit Akun COA',
            'accounts.delete' => 'Menghapus Akun COA',

            // Transaksi Jurnal Modul
            'journals.view' => 'Melihat Daftar Jurnal Umum & Penyesuaian',
            'journals.create' => 'Membuat Transaksi Jurnal Baru',
            'journals.edit' => 'Mengedit Draft Jurnal',
            'journals.post' => 'Menyetujui & Memposting Jurnal',
            'journals.delete' => 'Menghapus Jurnal',

            // Laporan Keuangan Modul
            'reports.view' => 'Melihat Laporan Keuangan & Buku Besar',
            'reports.export' => 'Mengekspor Laporan Keuangan (Excel/PDF)',

            // Pengaturan Modul
            'settings.view' => 'Melihat Pengaturan Sistem (Unit & Jenis Jurnal)',
            'settings.manage' => 'Kelola Master Unit & Jenis Jurnal',
            'settings.manage_roles' => 'Kelola Peran (Roles) & Hak Akses Dinamis',
            'periods.manage_keys' => 'Kelola & Melihat Kunci Rahasia Penutupan Periode',
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
            'accounts.view', 'accounts.create', 'accounts.edit', 'accounts.delete',
            'journals.view', 'journals.create', 'journals.edit', 'journals.post', 'journals.delete',
            'reports.view', 'reports.export',
            'settings.view', 'settings.manage',
        ]);

        $staff = Role::firstOrCreate(['name' => 'Staf Keuangan']);
        $staff->givePermissionTo([
            'accounts.view',
            'journals.view', 'journals.create', 'journals.edit',
            'reports.view',
            'settings.view',
        ]);

        $auditor = Role::firstOrCreate(['name' => 'Auditor / Viewer']);
        $auditor->givePermissionTo([
            'accounts.view',
            'journals.view',
            'reports.view', 'reports.export',
        ]);

        // Assign Super Admin role to default user
        $user = User::first();
        if ($user) {
            $user->assignRole($superAdmin);
        }
    }
}
