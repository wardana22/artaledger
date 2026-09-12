<?php

namespace App\Livewire\Admin;

use App\Services\AuditLogService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Kelola Peran & Hak Akses (RBAC)')]
class RoleIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showRoleModal = false;

    public ?int $editingRoleId = null;

    public string $roleName = '';

    public array $selectedPermissions = [];

    public array $permissionLabels = [
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
        'journals.delete' => 'Hapus Jurnal (Draft & Terposting)',
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

        // Rekonsiliasi Bank Modul
        'reconciliation.view' => 'Lihat Rekonsiliasi Bank',
        'reconciliation.manage' => 'Kelola & Eksekusi Rekonsiliasi Bank',
        'reconciliation.upload' => 'Unggah Rekening Koran Bank',

        // Aset Tetap & Penyusutan Modul
        'assets.view' => 'Lihat Master Register Aset Tetap',
        'assets.create' => 'Tambah Data Aset Tetap Baru',
        'assets.edit' => 'Edit Data Aset Tetap',
        'assets.delete' => 'Hapus Data Aset Tetap',
        'assets.depreciate' => 'Eksekusi & Posting Penyusutan Aset Tetap',

        // Anggaran & Kontrol Biaya Modul
        'budgets.view' => 'Lihat Data Anggaran & Laporan Varian',
        'budgets.manage' => 'Kelola, Buat, Aktifkan, & Tutup Anggaran',

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

    public function mount(): void
    {
        $this->authorizeAdminRoles();
    }

    public function openCreateRoleModal(): void
    {
        $this->authorizeAdminRoles();
        $this->resetRoleForm();
        $this->showRoleModal = true;
    }

    public function openEditRoleModal(int $roleId): void
    {
        $this->authorizeAdminRoles();
        $this->resetRoleForm();
        $role = Role::findOrFail($roleId);
        $this->editingRoleId = $role->id;
        $this->roleName = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->showRoleModal = true;
    }

    public function toggleModulePermissions(array $permissionNames): void
    {
        $hasAll = count(array_intersect($permissionNames, $this->selectedPermissions)) === count($permissionNames);
        if ($hasAll) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $permissionNames));
        } else {
            $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $permissionNames)));
        }
    }

    public function selectAllSystemPermissions(): void
    {
        $this->selectedPermissions = Permission::pluck('name')->toArray();
    }

    public function clearAllSystemPermissions(): void
    {
        $this->selectedPermissions = [];
    }

    public function saveRole(): void
    {
        $this->authorizeAdminRoles();

        // System Protection: Super Admin role permissions cannot be modified
        if ($this->editingRoleId) {
            $targetRole = Role::findOrFail($this->editingRoleId);
            if ($targetRole->name === 'Super Admin') {
                session()->flash('error', 'Perlindungan Sistem: Hak akses peran bawaan Super Admin tidak dapat diubah.');

                return;
            }
        }

        $this->validate([
            'roleName' => 'required|string|max:100|unique:roles,name,'.$this->editingRoleId,
            'selectedPermissions' => 'array',
        ]);

        $isNew = ! $this->editingRoleId;
        $role = Role::updateOrCreate(
            ['id' => $this->editingRoleId],
            ['name' => trim($this->roleName), 'guard_name' => 'web']
        );

        $role->syncPermissions($this->selectedPermissions);

        AuditLogService::record(
            $isNew ? 'role.created' : 'role.updated',
            ($isNew ? 'Membuat peran dinamis baru: ' : 'Memperbarui hak akses peran: ').$role->name,
            $role
        );

        session()->flash('message', $this->editingRoleId ? "Peran '{$role->name}' berhasil diperbarui." : "Peran baru '{$role->name}' berhasil dibuat.");
        $this->showRoleModal = false;
        $this->resetRoleForm();
    }

    public function deleteRole(int $roleId): void
    {
        $this->authorizeAdminRoles();

        $role = Role::findOrFail($roleId);

        if ($role->name === 'Super Admin') {
            session()->flash('error', 'Peran Super Admin bawaan sistem tidak boleh dihapus.');

            return;
        }

        $roleName = $role->name;
        $role->delete();

        AuditLogService::record(
            'role.deleted',
            "Menghapus peran: {$roleName}",
            $role
        );

        session()->flash('message', "Peran '{$roleName}' berhasil dihapus.");
    }

    public function resetRoleForm(): void
    {
        $this->editingRoleId = null;
        $this->roleName = '';
        $this->selectedPermissions = [];
        $this->resetValidation();
    }

    protected function authorizeAdminRoles(): void
    {
        if (auth()->check() && ! auth()->user()->can('admin.roles') && ! auth()->user()->can('settings.manage_roles')) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengelola peran dan hak akses sistem.');
        }
    }

    public function render()
    {
        $roles = Role::with('permissions')
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();

        $allPermissions = Permission::orderBy('name')->get();

        $groupedPermissions = [
            '📁 MODUL 1: MASTER AKUNTANSI & PENGATURAN' => [
                '🔹 Master COA (Chart of Accounts)' => $allPermissions->filter(fn ($p) => str_starts_with($p->name, 'accounts.')),
                '🔹 Master Jenis Jurnal' => $allPermissions->filter(fn ($p) => $p->name === 'settings.journal_types'),
                '🔹 Master Unit Perusahaan' => $allPermissions->filter(fn ($p) => $p->name === 'settings.units'),
            ],
            '📁 MODUL 2: TRANSAKSI JURNAL & IMPORT' => [
                '🔹 Jurnal Umum & Penyesuaian' => $allPermissions->filter(fn ($p) => str_starts_with($p->name, 'journals.') && ! in_array($p->name, ['journals.import', 'settings.templates'])),
                '🔹 Import Jurnal Excel' => $allPermissions->filter(fn ($p) => $p->name === 'journals.import'),
                '🔹 Template Jurnal Berulang' => $allPermissions->filter(fn ($p) => $p->name === 'settings.templates'),
            ],
            '📁 MODUL 3: PERIODE AKUNTANSI & PENUTUPAN' => [
                '🔹 Periode Akuntansi & Lock Key' => $allPermissions->filter(fn ($p) => str_starts_with($p->name, 'periods.')),
            ],
            '📁 MODUL 4: ANGGARAN & KONTROL BIAYA' => [
                '🔹 Rencana Anggaran & Varian Biaya' => $allPermissions->filter(fn ($p) => str_starts_with($p->name, 'budgets.')),
            ],
            '📁 MODUL 5: REKONSILIASI BANK & ASET TETAP' => [
                '🔹 Rekonsiliasi Rekening Koran Bank' => $allPermissions->filter(fn ($p) => str_starts_with($p->name, 'reconciliation.')),
                '🔹 Register Aset Tetap & Penyusutan' => $allPermissions->filter(fn ($p) => str_starts_with($p->name, 'assets.')),
            ],
            '📁 MODUL 6: LAPORAN KEUANGAN' => [
                '🔹 Buku Besar (General & Subsidiary)' => $allPermissions->filter(fn ($p) => in_array($p->name, ['reports.general_ledger', 'reports.subsidiary_ledger'])),
                '🔹 Neraca & Kertas Kerja' => $allPermissions->filter(fn ($p) => in_array($p->name, ['reports.worksheet', 'reports.trial_balance', 'reports.balance_sheet'])),
                '🔹 Laba Rugi (Profit & Loss)' => $allPermissions->filter(fn ($p) => $p->name === 'reports.profit_loss'),
                '🔹 Arus Kas, Saldo Awal, & Ekuitas' => $allPermissions->filter(fn ($p) => in_array($p->name, ['reports.cash_flow', 'reports.opening_balance', 'reports.changes_in_equity'])),
                '🔹 Fitur Umum & Ekspor Laporan' => $allPermissions->filter(fn ($p) => in_array($p->name, ['reports.view', 'reports.export'])),
            ],
            '📁 MODUL 7: PENGATURAN SYSTEM' => [
                '🔹 Pengaturan Sistem Umum & Branding' => $allPermissions->filter(fn ($p) => in_array($p->name, ['settings.view', 'settings.manage', 'settings.company'])),
                '🔹 Pengaturan Dashboard & KPI' => $allPermissions->filter(fn ($p) => str_starts_with($p->name, 'dashboard.')),
            ],
            '📁 MODUL 8: MANAJEMEN PENGGUNA & SECURITY AUDIT' => [
                '🔹 Pengguna, Role Dinamis & Audit Log' => $allPermissions->filter(fn ($p) => str_starts_with($p->name, 'admin.') || $p->name === 'settings.manage_roles'),
            ],
        ];

        return view('livewire.admin.role-index', [
            'roles' => $roles,
            'groupedPermissions' => $groupedPermissions,
            'permissionLabels' => $this->permissionLabels,
        ]);
    }
}
