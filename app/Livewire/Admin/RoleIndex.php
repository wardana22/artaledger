<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Kelola Peran & Hak Akses (RBAC) - ArtaLedger')]
class RoleIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showRoleModal = false;

    public ?int $editingRoleId = null;

    public string $roleName = '';

    public array $selectedPermissions = [];

    public array $permissionLabels = [
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
        if (auth()->check() && ! auth()->user()->can('admin.roles') && ! auth()->user()->can('settings.manage_roles')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }
    }

    public function openCreateRoleModal(): void
    {
        $this->resetRoleForm();
        $this->showRoleModal = true;
    }

    public function openEditRoleModal(int $roleId): void
    {
        $this->resetRoleForm();
        $role = Role::findOrFail($roleId);
        $this->editingRoleId = $role->id;
        $this->roleName = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->showRoleModal = true;
    }

    public function saveRole(): void
    {
        $this->validate([
            'roleName' => 'required|string|max:100|unique:roles,name,'.$this->editingRoleId,
            'selectedPermissions' => 'array',
        ]);

        $role = Role::updateOrCreate(
            ['id' => $this->editingRoleId],
            ['name' => trim($this->roleName), 'guard_name' => 'web']
        );

        $role->syncPermissions($this->selectedPermissions);

        session()->flash('message', $this->editingRoleId ? "Peran '{$role->name}' berhasil diperbarui." : "Peran baru '{$role->name}' berhasil dibuat.");
        $this->showRoleModal = false;
        $this->resetRoleForm();
    }

    public function deleteRole(int $roleId): void
    {
        $role = Role::findOrFail($roleId);

        if ($role->name === 'Super Admin') {
            session()->flash('error', 'Peran Super Admin bawaan sistem tidak boleh dihapus.');

            return;
        }

        $role->delete();
        session()->flash('message', "Peran '{$role->name}' berhasil dihapus.");
    }

    public function resetRoleForm(): void
    {
        $this->editingRoleId = null;
        $this->roleName = '';
        $this->selectedPermissions = [];
        $this->resetValidation();
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
                '🔹 Tab: Master COA (Chart of Accounts)' => $allPermissions->filter(fn ($p) => str_starts_with($p->name, 'accounts.')),
                '🔹 Tab: Master Jenis Jurnal' => $allPermissions->filter(fn ($p) => $p->name === 'settings.journal_types'),
                '🔹 Tab: Master Unit Perusahaan' => $allPermissions->filter(fn ($p) => $p->name === 'settings.units'),
            ],
            '📁 MODUL 2: TRANSAKSI JURNAL & IMPORT' => [
                '🔹 Tab: Jurnal Umum & Penyesuaian' => $allPermissions->filter(fn ($p) => str_starts_with($p->name, 'journals.') && $p->name !== 'journals.import'),
                '🔹 Sub-Menu: Import Jurnal Excel' => $allPermissions->filter(fn ($p) => $p->name === 'journals.import'),
                '🔹 Sub-Menu: Template Jurnal Berulang' => $allPermissions->filter(fn ($p) => $p->name === 'settings.templates'),
            ],
            '📁 MODUL 3: PERIODE AKUNTANSI & PENUTUPAN' => [
                '🔹 Sub-Modul: Periode Akuntansi & Lock Key' => $allPermissions->filter(fn ($p) => str_starts_with($p->name, 'periods.')),
            ],
            '📁 MODUL 4: LAPORAN KEUANGAN (PER SUB-MODUL & TAB MENU)' => [
                '🔹 Sub-Modul: Buku Besar (General Ledger)' => $allPermissions->filter(fn ($p) => in_array($p->name, ['reports.general_ledger', 'reports.subsidiary_ledger'])),
                '🔹 Sub-Modul: Neraca & Kertas Kerja' => $allPermissions->filter(fn ($p) => in_array($p->name, ['reports.worksheet', 'reports.trial_balance', 'reports.balance_sheet'])),
                '🔹 Sub-Modul: Laba Rugi (Profit & Loss)' => $allPermissions->filter(fn ($p) => $p->name === 'reports.profit_loss'),
                '🔹 Sub-Modul: Arus Kas, Saldo Awal, & Perubahan Ekuitas' => $allPermissions->filter(fn ($p) => in_array($p->name, ['reports.cash_flow', 'reports.opening_balance', 'reports.changes_in_equity'])),
                '🔹 Fitur Umum Laporan Keuangan' => $allPermissions->filter(fn ($p) => in_array($p->name, ['reports.view', 'reports.export'])),
            ],
            '📁 MODUL 5: PENGATURAN SYSTEM' => [
                '🔹 Sub-Modul: Pengaturan Sistem Umum' => $allPermissions->filter(fn ($p) => in_array($p->name, ['settings.view', 'settings.manage'])),
            ],
            '📁 MODUL 6: MANAJEMEN PENGGUNA & SECURITY AUDIT' => [
                '🔹 Sub-Modul: User, Multi-Tenant Unit, Dynamic RBAC, & Audit Log' => $allPermissions->filter(fn ($p) => str_starts_with($p->name, 'admin.') || $p->name === 'settings.manage_roles'),
            ],
        ];

        return view('livewire.admin.role-index', [
            'roles' => $roles,
            'groupedPermissions' => $groupedPermissions,
            'permissionLabels' => $this->permissionLabels,
        ]);
    }
}
