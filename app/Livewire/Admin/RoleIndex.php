<?php

namespace App\Livewire\Admin;

use App\Models\User;
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

    public bool $showUserRoleModal = false;

    public ?int $editingRoleId = null;

    public ?int $selectedUserId = null;

    public string $roleName = '';

    public array $selectedPermissions = [];

    public array $selectedUserRoles = [];

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

    public function openUserRoleModal(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->selectedUserId = $user->id;
        $this->selectedUserRoles = $user->roles->pluck('name')->toArray();
        $this->showUserRoleModal = true;
    }

    public function saveUserRoles(): void
    {
        $user = User::findOrFail($this->selectedUserId);
        $user->syncRoles($this->selectedUserRoles);

        session()->flash('message', "Peran untuk pengguna '{$user->name}' berhasil diperbarui.");
        $this->showUserRoleModal = false;
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

        $users = User::with('roles')->get();

        return view('livewire.admin.role-index', [
            'roles' => $roles,
            'groupedPermissions' => $groupedPermissions,
            'users' => $users,
        ]);
    }
}
