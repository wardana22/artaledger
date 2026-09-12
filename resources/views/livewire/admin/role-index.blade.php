<div class="p-4 sm:p-6 space-y-5">
    <!-- TOP NAV TABS WITH LIVE COUNTER -->
    <x-user-access-nav active="roles" />

    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-500/30 flex items-center justify-center shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg md:text-xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">
                        Kelola Peran & Hak Akses (Dynamic RBAC)
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Atur peran dinamis dan matriks otorisasi izin (*Permissions*) per modul secara terpusat.
                    </p>
                </div>
            </div>
        </div>

        @can('admin.roles')
            <button 
                wire:click="openCreateRoleModal"
                class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-xs rounded-xl shadow-md shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:-translate-y-0.5 transition-all duration-200 gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Tambah Peran Baru</span>
            </button>
        @endcan
    </div>

    <!-- FLASH MESSAGES -->
    @if (session()->has('message'))
        <div class="p-3.5 bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 rounded-xl text-xs font-semibold flex items-center gap-2.5 shadow-xs animate-fadeIn">
            <div class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-3.5 bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-400 rounded-xl text-xs font-semibold flex items-center gap-2.5 shadow-xs animate-fadeIn">
            <div class="w-5 h-5 rounded-full bg-rose-500/20 flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- ROLES TABLE CARD -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-2xs overflow-hidden">
        <div class="p-4 bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xs font-extrabold text-slate-700 dark:text-slate-200 uppercase tracking-wider">
                    Daftar Peran Dinamis (Roles)
                </span>
                <span class="px-2 py-0.5 text-[11px] font-mono font-bold rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                    {{ $roles->count() }} Peran
                </span>
            </div>
            <div class="relative w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                <input 
                    wire:model.live.debounce.300ms="search" 
                    type="text" 
                    placeholder="Cari peran..." 
                    class="w-full pl-8 pr-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500/40 dark:text-slate-200 font-medium"
                />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50/60 dark:bg-slate-800/40 uppercase font-bold text-[11px] text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3 w-12 text-center">#</th>
                        <th class="px-4 py-3 w-64">Nama Peran (Role)</th>
                        <th class="px-4 py-3">Hak Akses Modul (Permissions)</th>
                        <th class="px-4 py-3 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse ($roles as $index => $role)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-4 py-3 text-center text-slate-400 font-mono text-xs">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @php
                                        $isRoot = $role->name === 'Super Admin';
                                        $badgeStyle = match ($role->name) {
                                            'Super Admin' => 'bg-purple-500/10 text-purple-700 dark:text-purple-300 border-purple-500/30',
                                            'Akuntan / Finance Manager' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/30',
                                            'Staf Keuangan' => 'bg-sky-500/10 text-sky-700 dark:text-sky-300 border-sky-500/30',
                                            'Auditor / Viewer' => 'bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/30',
                                            default => 'bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border-indigo-500/30',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-xl border {{ $badgeStyle }}">
                                        {{ $role->name }}
                                    </span>
                                    @if ($isRoot)
                                        <span class="px-1.5 py-0.2 text-[9px] font-extrabold uppercase rounded-md bg-purple-600 text-white shadow-2xs">System Root</span>
                                    @endif
                                </div>
                                <span class="text-[11px] text-slate-400 mt-1 block">
                                    {{ $role->permissions->count() }} izin aktif
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto p-0.5">
                                    @forelse ($role->permissions as $perm)
                                        <span class="px-2 py-0.5 text-[11px] font-medium rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700">
                                            {{ $permissionLabels[$perm->name] ?? $perm->name }}
                                        </span>
                                    @empty
                                        <span class="text-[11px] text-slate-400 italic">Tanpa izin modul</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    @can('admin.roles')
                                        <!-- Edit Role Button (Hover Solid Indigo) -->
                                        <button 
                                            wire:click="openEditRoleModal({{ $role->id }})"
                                            title="Edit Izin Peran {{ $role->name }}"
                                            class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 shadow-2xs hover:shadow-md hover:shadow-indigo-500/25 transition-all duration-200 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        
                                        <!-- Delete Role Button (Hover Solid Rose, Protected for Super Admin) -->
                                        @if ($role->name !== 'Super Admin')
                                            <button 
                                                wire:click="deleteRole({{ $role->id }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus peran '{{ $role->name }}'?"
                                                title="Hapus Peran"
                                                class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-rose-600 hover:text-white hover:border-rose-600 shadow-2xs hover:shadow-md hover:shadow-rose-500/25 transition-all duration-200 cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-400">
                                <p class="text-xs font-semibold">Belum ada peran dinamis dibuat.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL FORM PERAN & HAK AKSES -->
    @if ($showRoleModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs transition-opacity animate-fadeIn">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl max-w-3xl w-full max-h-[92vh] flex flex-col overflow-hidden">
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                {{ $editingRoleId ? 'Edit Peran: ' . $roleName : 'Tambah Peran Dinamis Baru' }}
                            </h3>
                            <p class="text-[11px] text-slate-400">Tentukan nama peran dan pilih hak akses modul (*permissions*) yang diizinkan.</p>
                        </div>
                    </div>
                    <button wire:click="$set('showRoleModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body (Form) -->
                <form wire:submit.prevent="saveRole" class="p-6 space-y-5 overflow-y-auto flex-1">
                    <!-- Nama Peran -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                            Nama Peran (Role Name) *
                        </label>
                        <input 
                            wire:model="roleName"
                            type="text" 
                            placeholder="Contoh: Manajer Keuangan / Kasir / Auditor Khusus" 
                            class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs md:text-sm font-bold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 focus:outline-none"
                            @if ($editingRoleId && $roleName === 'Super Admin') readonly @endif
                        />
                        @if ($editingRoleId && $roleName === 'Super Admin')
                            <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-1">Peran Super Admin adalah peran inti sistem dan tidak dapat diubah namanya maupun dikosongkan izinnya.</p>
                        @endif
                        @error('roleName') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Groups Permissions Section -->
                    <div class="space-y-3.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                    Matriks Hak Akses Modul (Permissions)
                                </label>
                                <span class="text-[11px] text-slate-400">Centang fitur yang dapat diakses oleh peran ini</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button 
                                    type="button" 
                                    wire:click="selectAllSystemPermissions" 
                                    class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                    Pilih Semua Izin
                                </button>
                                <span class="text-slate-300 dark:text-slate-700">|</span>
                                <button 
                                    type="button" 
                                    wire:click="clearAllSystemPermissions" 
                                    class="text-[11px] font-bold text-slate-500 hover:underline">
                                    Kosongkan
                                </button>
                                <span class="px-2 py-0.5 text-[10px] font-mono font-bold rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                    {{ count($selectedPermissions) }} Dipilih
                                </span>
                            </div>
                        </div>

                        <!-- Accordion/Cards Per Modul -->
                        <div class="space-y-3.5">
                            @foreach ($groupedPermissions as $moduleName => $subModules)
                                @php
                                    // Collect all permission names in this module
                                    $allModulePerms = [];
                                    foreach ($subModules as $perms) {
                                        foreach ($perms as $p) {
                                            $allModulePerms[] = $p->name;
                                        }
                                    }
                                    $moduleSelectedCount = count(array_intersect($allModulePerms, $selectedPermissions));
                                    $moduleTotalCount = count($allModulePerms);
                                    $isAllModuleSelected = $moduleTotalCount > 0 && $moduleSelectedCount === $moduleTotalCount;
                                @endphp

                                <div class="p-4 bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800 rounded-2xl space-y-3">
                                    <div class="flex items-center justify-between border-b border-slate-200/60 dark:border-slate-700/60 pb-2">
                                        <span class="text-xs font-extrabold tracking-wider text-indigo-600 dark:text-indigo-400 uppercase">
                                            {{ $moduleName }}
                                        </span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-mono font-bold text-slate-400">
                                                {{ $moduleSelectedCount }}/{{ $moduleTotalCount }}
                                            </span>
                                            <button 
                                                type="button" 
                                                wire:click="toggleModulePermissions(@js($allModulePerms))"
                                                class="text-[11px] font-bold px-2 py-0.5 rounded-md transition-colors {{ $isAllModuleSelected ? 'bg-rose-500/10 text-rose-600 hover:bg-rose-500/20' : 'bg-indigo-500/10 text-indigo-600 hover:bg-indigo-500/20' }}">
                                                {{ $isAllModuleSelected ? 'Batalkan Modul' : 'Pilih Modul Ini' }}
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-3 pl-1">
                                        @foreach ($subModules as $subModuleName => $perms)
                                            @if ($perms->count() > 0)
                                                <div class="space-y-1.5">
                                                    <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 block">
                                                        {{ $subModuleName }}
                                                    </span>
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 pl-1">
                                                        @foreach ($perms as $perm)
                                                            <label class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-white dark:hover:bg-slate-800/80 cursor-pointer transition-all border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
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
                        </div>
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
                            wire:loading.attr="disabled"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-500/25 transition-all flex items-center gap-1.5 cursor-pointer">
                            <svg wire:loading class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Simpan Peran & Hak Akses</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
