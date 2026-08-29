<div class="p-4 sm:p-5 space-y-3.5">
    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                Kelola Peran & Hak Akses (Dynamic RBAC)
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Buat peran dinamis dan sesuaikan daftar izin (*permissions*) per modul secara fleksibel.
            </p>
        </div>

        <button 
            wire:click="openCreateRoleModal"
            class="inline-flex items-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-xs rounded-lg shadow-md shadow-indigo-500/20 transition-all duration-150 gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Peran Baru
        </button>
    </div>

    <!-- FLASH MESSAGES -->
    @if (session()->has('message'))
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 rounded-xl text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-3 bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 rounded-xl text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- GRID LAYOUT: ROLES TABLE & USER ASSIGNMENT -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        <!-- LEFT 2 COLUMNS: ROLES TABLE -->
        <div class="lg:col-span-2 space-y-3.5">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                <div class="p-3 bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider">
                        Daftar Peran Dinamis (Roles)
                    </span>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                        {{ $roles->count() }} Peran Terdaftar
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-4 py-2.5 w-12 text-center">#</th>
                                <th class="px-4 py-2.5 w-48">Nama Peran (Role)</th>
                                <th class="px-4 py-2.5">Hak Akses Modul (Permissions)</th>
                                <th class="px-4 py-2.5 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($roles as $index => $role)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="px-4 py-2.5 text-center text-slate-400 font-mono">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-4 py-2.5 font-bold text-slate-800 dark:text-slate-100">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-2 py-0.5 text-xs font-mono font-bold rounded-md bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                                {{ $role->name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <div class="flex flex-wrap gap-1 max-w-md">
                                            @forelse ($role->permissions->take(6) as $perm)
                                                <span class="px-1.5 py-0.5 text-[10px] font-mono font-semibold rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                    {{ $perm->name }}
                                                </span>
                                            @empty
                                                <span class="text-[11px] text-slate-400 italic">Tanpa izin modul</span>
                                            @endforelse

                                            @if ($role->permissions->count() > 6)
                                                <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                                    +{{ $role->permissions->count() - 6 }} Lainnya
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-2.5 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button 
                                                wire:click="openEditRoleModal({{ $role->id }})"
                                                title="Edit Izin Peran {{ $role->name }}"
                                                class="p-1.5 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-500/30 rounded-lg transition-all shadow-2xs">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            
                                            @if ($role->name !== 'Super Admin')
                                                <button 
                                                    wire:click="deleteRole({{ $role->id }})"
                                                    wire:confirm="Apakah Anda yakin ingin menghapus peran '{{ $role->name }}'?"
                                                    title="Hapus Peran"
                                                    class="p-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 rounded-lg transition-all shadow-2xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-6 text-center text-slate-400">
                                        Belum ada peran dinamis dibuat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT 1 COLUMN: USER ROLES ASSIGNMENT -->
        <div class="space-y-3.5">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs p-4 space-y-3">
                <h3 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Penetapan Peran Pengguna
                </h3>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($users as $user)
                        <div class="py-2.5 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-100">{{ $user->name }}</p>
                                <p class="text-[11px] text-slate-400 font-mono">{{ $user->email }}</p>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @forelse ($user->roles as $uRole)
                                        <span class="px-1.5 py-0.5 text-[9px] font-bold uppercase rounded bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                            {{ $uRole->name }}
                                        </span>
                                    @empty
                                        <span class="text-[10px] text-slate-400 italic">Tanpa Peran</span>
                                    @endforelse
                                </div>
                            </div>

                            <button 
                                wire:click="openUserRoleModal({{ $user->id }})"
                                class="px-2.5 py-1 text-[11px] font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 bg-indigo-500/10 hover:bg-indigo-500/20 rounded-md transition-all">
                                Ubah Peran
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CREATE / EDIT ROLE WITH PERMISSION CHECKBOXES -->
    @if ($showRoleModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-2xl overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                        {{ $editingRoleId ? 'Edit Peran & Hak Akses' : 'Buat Peran Dinamis Baru' }}
                    </h3>
                    <button wire:click="$set('showRoleModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveRole" class="p-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Nama Peran (Role) *</label>
                        <input 
                            wire:model="roleName" 
                            type="text" 
                            placeholder="misal: Staf Kasir / Tax Officer / Akuntan Senior" 
                            class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs md:text-sm font-bold focus:ring-2 focus:ring-indigo-500 dark:text-slate-100" 
                            required 
                        />
                        @error('roleName') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- PERMISSION CHECKBOXES GROUPED BY MODULE -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Pilih Hak Akses Modul (Permissions)</label>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-72 overflow-y-auto p-1">
                            @foreach ($groupedPermissions as $groupName => $perms)
                                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/80 space-y-2">
                                    <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500 inline-block"></span>
                                        {{ $groupName }}
                                    </p>

                                    <div class="space-y-1.5">
                                        @foreach ($perms as $p)
                                            <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700 dark:text-slate-300 hover:text-indigo-600">
                                                <input 
                                                    type="checkbox" 
                                                    wire:model="selectedPermissions" 
                                                    value="{{ $p->name }}" 
                                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                                />
                                                <span class="font-mono text-[11px] font-semibold">{{ $p->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                        <button 
                            type="button" 
                            wire:click="$set('showRoleModal', false)" 
                            class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg">
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-md shadow-indigo-500/20 transition-all">
                            Simpan Peran & Izin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL USER ROLE ASSIGNMENT -->
    @if ($showUserRoleModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                        Tetapkan Peran Pengguna
                    </h3>
                    <button wire:click="$set('showUserRoleModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveUserRoles" class="p-4 space-y-4">
                    <p class="text-xs text-slate-500">Pilih satu atau lebih peran untuk pengguna ini:</p>

                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach ($roles as $r)
                            <label class="flex items-center gap-2 p-2 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer text-xs font-bold text-slate-800 dark:text-slate-200">
                                <input 
                                    type="checkbox" 
                                    wire:model="selectedUserRoles" 
                                    value="{{ $r->name }}" 
                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <span>{{ $r->name }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                        <button 
                            type="button" 
                            wire:click="$set('showUserRoleModal', false)" 
                            class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg">
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-md shadow-indigo-500/20 transition-all">
                            Simpan Peran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
