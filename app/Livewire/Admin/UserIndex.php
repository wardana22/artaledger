<?php

namespace App\Livewire\Admin;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Manajemen Pengguna & Penugasan Unit - ArtaLedger')]
class UserIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    public int $perPage = 10;

    public bool $showFormModal = false;

    public bool $showResetPasswordModal = false;

    public ?int $editingUserId = null;

    // Form fields
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public array $selectedRoles = [];

    public array $selectedUnits = [];

    public function mount(): void
    {
        if (auth()->check() && ! auth()->user()->can('admin.users')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }
    }

    // Reset password fields
    public ?int $resetUserId = null;

    public string $newPassword = '';

    public string $newPassword_confirmation = '';

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

    public function openCreateModal(): void
    {
        $this->resetUserForm();
        $this->showFormModal = true;
    }

    public function openEditModal(int $userId): void
    {
        $this->resetUserForm();
        $user = User::with(['roles', 'units'])->findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->selectedUnits = $user->units->pluck('id')->toArray();
        $this->showFormModal = true;
    }

    public function saveUser(): void
    {
        if (auth()->check() && ! auth()->user()->hasPermissionTo('settings.manage_roles')) {
            session()->flash('error', 'Akses ditolak! Anda tidak memiliki izin untuk mengelola pengguna.');

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

        $user = User::updateOrCreate(
            ['id' => $this->editingUserId],
            $userData
        );

        $user->syncRoles($this->selectedRoles);
        $user->units()->sync($this->selectedUnits);

        session()->flash('message', $this->editingUserId ? "Data pengguna '{$user->name}' berhasil diperbarui." : "Pengguna baru '{$user->name}' berhasil ditambahkan.");
        $this->showFormModal = false;
        $this->resetUserForm();
    }

    public function openResetPasswordModal(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->resetUserId = $user->id;
        $this->newPassword = '';
        $this->newPassword_confirmation = '';
        $this->resetValidation();
        $this->showResetPasswordModal = true;
    }

    public function saveResetPassword(): void
    {
        if (auth()->check() && ! auth()->user()->hasPermissionTo('settings.manage_roles')) {
            session()->flash('error', 'Akses ditolak! Anda tidak memiliki izin untuk mereset password.');

            return;
        }

        $this->validate([
            'newPassword' => 'required|string|min:8|confirmed',
        ]);

        $user = User::findOrFail($this->resetUserId);
        $user->update([
            'password' => Hash::make($this->newPassword),
        ]);

        session()->flash('message', "Password untuk pengguna '{$user->name}' berhasil diperbarui.");
        $this->showResetPasswordModal = false;
    }

    public function deleteUser(int $userId): void
    {
        if (auth()->check() && ! auth()->user()->hasPermissionTo('settings.manage_roles')) {
            session()->flash('error', 'Akses ditolak! Anda tidak memiliki izin untuk menghapus pengguna.');

            return;
        }

        if ($userId === auth()->id()) {
            session()->flash('error', 'Gagal menghapus! Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');

            return;
        }

        $user = User::findOrFail($userId);
        $user->delete();

        session()->flash('message', "Pengguna '{$user->name}' berhasil dihapus.");
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
        $this->resetValidation();
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

        return view('livewire.admin.user-index', [
            'users' => $users,
            'allRoles' => $allRoles,
            'allUnits' => $allUnits,
        ]);
    }
}
