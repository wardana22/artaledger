<div class="p-4 sm:p-5 space-y-3.5">
    <!-- TOP NAV TABS -->
    <x-user-access-nav active="roles" />

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

    <!-- ROLES TABLE CARD (FULL WIDTH) -->
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
                        <th class="px-4 py-2.5 w-56">Nama Peran (Role)</th>
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
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($role->permissions as $perm)
                                        <span class="px-2 py-0.5 text-[11px] font-medium rounded-md bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-500/20">
                                            {{ $permissionLabels[$perm->name] ?? $perm->name }}
                                        </span>
                                    @empty
                                        <span class="text-[11px] text-slate-400 italic">Tanpa izin modul</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-2.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button 
                                        wire:click="openEditRoleModal({{ $role->id }})"
                                        title="Edit Izin Peran {{ $role->name }}"
                                        class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 shadow-2xs hover:shadow-md hover:shadow-indigo-500/20 transition-all duration-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    
                                    @if ($role->name !== 'Super Admin')
                                        <button 
                                            wire:click="deleteRole({{ $role->id }})"
                                            wire:confirm="Apakah Anda yakin ingin menghapus peran '{{ $role->name }}'?"
                                            title="Hapus Peran"
                                            class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-rose-600 hover:text-white hover:border-rose-600 shadow-2xs hover:shadow-md hover:shadow-rose-500/20 transition-all duration-200">
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

    <!-- MODAL FORM PERAN & HAK AKSES -->
    @if ($showRoleModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden">
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">
                            {{ $editingRoleId ? 'Edit Hak Akses Peran: ' . $roleName : 'Tambah Peran Dinamis Baru' }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Atur nama peran dan tentukan akses fitur/modul yang diizinkan untuk peran ini.
                        </p>
                    </div>
                    <button wire:click="$set('showRoleModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body (Form) -->
                <form wire:submit="saveRole" class="p-6 space-y-5 overflow-y-auto flex-1">
                    <!-- Nama Peran -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                            Nama Peran (Role Name) *
                        </label>
                        <input 
                            wire:model="roleName"
                            type="text" 
                            placeholder="Contoh: Manajer Keuangan / Kasir / Auditor Khusus" 
                            class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs md:text-sm font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            @if ($editingRoleId && $roleName === 'Super Admin') readonly @endif
                        />
                        @error('roleName') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Groups Permissions -->
                    <div class="space-y-4">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                            Pilih Hak Akses Modul (Permissions)
                        </label>

                        @foreach ($groupedPermissions as $moduleName => $subModules)
                            <div class="p-3.5 bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800 rounded-xl space-y-3">
                                <div class="border-b border-slate-200/60 dark:border-slate-700/60 pb-1">
                                    <span class="text-xs font-extrabold tracking-wider text-indigo-600 dark:text-indigo-400 uppercase">
                                        {{ $moduleName }}
                                    </span>
                                </div>
                                <div class="space-y-3 pl-1">
                                    @foreach ($subModules as $subModuleName => $perms)
                                        @if ($perms->count() > 0)
                                            <div class="space-y-1.5">
                                                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 block">
                                                    {{ $subModuleName }}
                                                </span>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 pl-2">
                                                    @foreach ($perms as $perm)
                                                        <label class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800/80 cursor-pointer transition-all">
                                                            <input 
                                                                type="checkbox" 
                                                                wire:model="selectedPermissions" 
                                                                value="{{ $perm->name }}"
                                                                class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 dark:bg-slate-700"
                                                            />
                                                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">
                                                                {{ $permissionLabels[$perm->name] ?? $perm->name }}
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                        @error('selectedPermissions') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Modal Actions Footer -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                        <button 
                            type="button" 
                            wire:click="$set('showRoleModal', false)"
                            class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition-all">
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-500/20 transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Simpan Peran & Hak Akses
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
