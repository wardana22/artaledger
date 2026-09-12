<?php

namespace App\Livewire\Admin;

use App\Models\Unit;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Manajemen Pengguna & Penugasan Unit')]
class UserIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    public int $perPage = 10;

    public bool $showFormModal = false;

    public bool $showResetPasswordModal = false;

    public ?int $editingUserId = null;

    // Segmented Modal Tab: 'account' | 'roles' | 'units'
    public string $activeFormTab = 'account';

    // Form fields
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public array $selectedRoles = [];

    // Explicit Unit Scope (true = Akses Global Seluruh Unit, false = Unit Tertentu)
    public bool $isGlobalUnit = false;

    public array $selectedUnits = [];

    // Reset password fields
    public ?int $resetUserId = null;

    public string $newPassword = '';

    public string $newPassword_confirmation = '';

    public function mount(): void
    {
        if (auth()->check() && ! auth()->user()->can('admin.users')) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengelola pengguna.');
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function setFormTab(string $tab): void
    {
        if (in_array($tab, ['account', 'roles', 'units'])) {
            $this->activeFormTab = $tab;
        }
    }

    public function openCreateModal(): void
    {
        $this->authorizeAdminUsers();
        $this->resetUserForm();
        $this->activeFormTab = 'account';
        $this->showFormModal = true;
    }

    public function openEditModal(int $userId): void
    {
        $this->authorizeAdminUsers();
        $this->resetUserForm();

        $user = User::with(['roles', 'units'])->findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();

        // If user has 0 assigned units in unit_user table, treat as Global Access
        $this->isGlobalUnit = $user->units->count() === 0;
        $this->selectedUnits = $user->units->pluck('id')->toArray();

        $this->activeFormTab = 'account';
        $this->showFormModal = true;
    }

    public function selectAllUnits(): void
    {
        $this->selectedUnits = Unit::pluck('id')->toArray();
    }

    public function unselectAllUnits(): void
    {
        $this->selectedUnits = [];
    }

    public function toggleGlobalUnitAccess(): void
    {
        $this->isGlobalUnit = ! $this->isGlobalUnit;
        if ($this->isGlobalUnit) {
            $this->selectedUnits = [];
        }
    }

    public function saveUser(): void
    {
        $this->authorizeAdminUsers();

        // Anti-Privilege Escalation: Only existing Super Admin can assign Super Admin role
        if (! auth()->user()->hasRole('Super Admin') && in_array('Super Admin', $this->selectedRoles)) {
            $this->addError('selectedRoles', 'Akses Ditolak: Hanya Super Admin yang berwenang memberikan peran Super Admin.');

            return;
        }

        // Anti-Self Lockout: Super Admin cannot revoke Super Admin role from themselves
        if ($this->editingUserId === auth()->id() && auth()->user()->hasRole('Super Admin') && ! in_array('Super Admin', $this->selectedRoles)) {
            $this->addError('selectedRoles', 'Perlindungan Sistem: Anda tidak dapat mencabut peran Super Admin dari akun Anda sendiri.');

            return;
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$this->editingUserId,
            'selectedRoles' => 'array',
            'selectedUnits' => 'array',
        ];

        if (! $this->editingUserId) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        $this->validate($rules);

        $userData = [
            'name' => trim($this->name),
            'email' => strtolower(trim($this->email)),
        ];

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        $isNew = ! $this->editingUserId;
        $user = User::updateOrCreate(
            ['id' => $this->editingUserId],
            $userData
        );

        // Sync Roles
        $user->syncRoles($this->selectedRoles);

        // Sync Units: if Global Unit Access is checked, clear specific units (0 units = global)
        if ($this->isGlobalUnit) {
            $user->units()->sync([]);
        } else {
            $user->units()->sync($this->selectedUnits);
        }

        AuditLogService::record(
            $isNew ? 'user.created' : 'user.updated',
            ($isNew ? 'Menambahkan pengguna baru: ' : 'Memperbarui data pengguna: ').$user->name.' ('.$user->email.')',
            $user
        );

        session()->flash('message', $this->editingUserId ? "Data pengguna '{$user->name}' berhasil diperbarui." : "Pengguna baru '{$user->name}' berhasil ditambahkan ke sistem.");
        $this->showFormModal = false;
        $this->resetUserForm();
    }

    public function openResetPasswordModal(int $userId): void
    {
        $this->authorizeAdminUsers();
        $user = User::findOrFail($userId);
        $this->resetUserId = $user->id;
        $this->newPassword = '';
        $this->newPassword_confirmation = '';
        $this->resetValidation();
        $this->showResetPasswordModal = true;
    }

    public function saveResetPassword(): void
    {
        $this->authorizeAdminUsers();

        $this->validate([
            'newPassword' => 'required|string|min:8|confirmed',
        ]);

        $user = User::findOrFail($this->resetUserId);
        $user->update([
            'password' => Hash::make($this->newPassword),
        ]);

        AuditLogService::record(
            'user.password_reset',
            "Mereset kata sandi untuk pengguna: {$user->name} ({$user->email})",
            $user
        );

        session()->flash('message', "Password untuk pengguna '{$user->name}' berhasil diperbarui.");
        $this->showResetPasswordModal = false;
    }

    public function deleteUser(int $userId): void
    {
        $this->authorizeAdminUsers();

        if ($userId === auth()->id()) {
            session()->flash('error', 'Gagal menghapus! Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');

            return;
        }

        $user = User::findOrFail($userId);

        // Protect Super Admin default account if needed
        if ($user->email === 'admin@artaledger.com') {
            session()->flash('error', 'Akun administrator bawaan sistem tidak dapat dihapus.');

            return;
        }

        $userName = $user->name;
        $userEmail = $user->email;

        AuditLogService::record(
            'user.deleted',
            "Menghapus akun pengguna: {$userName} ({$userEmail})",
            $user
        );

        $user->delete();

        session()->flash('message', "Pengguna '{$userName}' berhasil dihapus dari sistem.");
    }

    public function resetUserForm(): void
    {
        $this->editingUserId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedRoles = [];
        $this->selectedUnits = [];
        $this->isGlobalUnit = false;
        $this->activeFormTab = 'account';
        $this->resetValidation();
    }

    protected function authorizeAdminUsers(): void
    {
        if (auth()->check() && ! auth()->user()->can('admin.users')) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengelola data pengguna.');
        }
    }

    public function render()
    {
        $users = User::with(['roles', 'units'])
            ->when($this->search !== '', function ($q) {
                $q->where(function ($sq) {
                    $sq->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhereHas('units', function ($uq) {
                            $uq->where('name', 'like', "%{$this->search}%")
                                ->orWhere('code', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->roleFilter !== '', function ($q) {
                $q->whereHas('roles', fn ($rq) => $rq->where('name', $this->roleFilter));
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        $allRoles = Role::orderBy('name')->get();
        $allUnits = Unit::orderBy('code')->get();

        // KPI Metrik Cepat
        $totalUsers = User::count();
        $superAdminCount = User::role('Super Admin')->count();
        $accountantCount = User::role('Akuntan / Finance Manager')->count();
        $staffCount = User::role('Staf Keuangan')->count();

        return view('livewire.admin.user-index', [
            'users' => $users,
            'allRoles' => $allRoles,
            'allUnits' => $allUnits,
            'totalUsers' => $totalUsers,
            'superAdminCount' => $superAdminCount,
            'accountantCount' => $accountantCount,
            'staffCount' => $staffCount,
        ]);
    }
}
