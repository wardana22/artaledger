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
        'accounts.view' => 'Lihat Master COA',
        'accounts.create' => 'Tambah Akun Baru',
        'accounts.edit' => 'Edit Akun',
        'accounts.delete' => 'Hapus Akun',
        'journals.view' => 'Lihat Jurnal Transaksi',
        'journals.create' => 'Buat Jurnal Baru',
        'journals.edit' => 'Edit Draft Jurnal',
        'journals.post' => 'Setujui & Posting Jurnal',
        'journals.delete' => 'Hapus Jurnal Transaksi',
        'reports.view' => 'Lihat Laporan Keuangan',
        'reports.export' => 'Ekspor Laporan (Excel/PDF)',
        'settings.view' => 'Lihat Pengaturan System',
        'settings.manage' => 'Kelola Unit & Jenis Jurnal',
        'settings.manage_roles' => 'Kelola Peran & Hak Akses',
    ];

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
            'Master COA' => $allPermissions->filter(fn ($p) => str_starts_with($p->name, 'accounts.')),
            'Transaksi Jurnal' => $allPermissions->filter(fn ($p) => str_starts_with($p->name, 'journals.')),
            'Laporan Keuangan' => $allPermissions->filter(fn ($p) => str_starts_with($p->name, 'reports.')),
            'Pengaturan System' => $allPermissions->filter(fn ($p) => str_starts_with($p->name, 'settings.')),
        ];

        return view('livewire.admin.role-index', [
            'roles' => $roles,
            'groupedPermissions' => $groupedPermissions,
            'permissionLabels' => $this->permissionLabels,
        ]);
    }
}
