<div class="p-4 sm:p-5 space-y-3.5">
    <!-- TOP NAV TABS -->
    <x-user-access-nav active="users" />

    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                Manajemen Pengguna & Penugasan Unit
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Kelola akun pengguna, penugasan Peran (Roles), dan Unit Perusahaan yang dikelola secara terpusat.
            </p>
        </div>

        @can('settings.manage_roles')
            <button 
                wire:click="openCreateModal"
                class="inline-flex items-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-xs rounded-lg shadow-md shadow-indigo-500/20 transition-all duration-150 gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Pengguna Baru
            </button>
        @endcan
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

    <!-- SEARCH & FILTER TOOLBAR -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-3 rounded-xl shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <!-- Search input -->
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                <input 
                    wire:model.live.debounce.300ms="search" 
                    type="text" 
                    placeholder="Cari nama, email, atau unit..." 
                    class="w-full pl-9 pr-4 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-200 transition-all"
                />
            </div>

            <!-- Role Filter Dropdown -->
            <div class="w-full sm:w-60">
                <select 
                    wire:model.live="roleFilter" 
                    class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-200 font-medium transition-all"
                >
                    <option value="">Semua Peran (Roles)</option>
                    @foreach ($allRoles as $r)
                        <option value="{{ $r->name }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if ($roleFilter !== '' || $search !== '')
            <button 
                wire:click="$set('roleFilter', ''); $set('search', '')" 
                class="px-2.5 py-1 text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 bg-rose-500/10 hover:bg-rose-500/20 rounded-lg transition-all flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Reset Filter
            </button>
        @endif
    </div>

    <!-- USER DATA TABLE CARD -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-2.5 w-12 text-center">#</th>
                        <th class="px-4 py-2.5 min-w-[200px]">Pengguna</th>
                        <th class="px-4 py-2.5 w-48">Peran (Roles)</th>
                        <th class="px-4 py-2.5 min-w-[220px]">Unit yang Dikelola (Managed Units)</th>
                        <th class="px-4 py-2.5 w-32 whitespace-nowrap">Terdaftar</th>
                        <th class="px-4 py-2.5 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($users as $index => $u)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-4 py-2.5 text-center text-slate-400 font-mono">
                                {{ $users->firstItem() + $index }}
                            </td>
                            
                            <!-- User Name & Email -->
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ $u->initials() }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-800 dark:text-slate-100 truncate">{{ $u->name }}</p>
                                        <p class="text-[11px] text-slate-400 font-mono truncate">{{ $u->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Roles Badges -->
                            <td class="px-4 py-2.5">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($u->roles as $r)
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                            {{ $r->name }}
                                        </span>
                                    @empty
                                        <span class="text-[11px] text-slate-400 italic">Tanpa Peran</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Managed Units Badges -->
                            <td class="px-4 py-2.5">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($u->units as $un)
                                        <span class="px-2 py-0.5 text-[10px] font-bold font-mono rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                            {{ $un->code }} - {{ $un->name }}
                                        </span>
                                    @empty
                                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                            Semua Unit (Global)
                                        </span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Created At -->
                            <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400 whitespace-nowrap text-xs font-mono">
                                {{ $u->created_at ? $u->created_at->format('d M Y') : '-' }}
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-2.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    @can('settings.manage_roles')
                                        <button 
                                            wire:click="openEditModal({{ $u->id }})"
                                            title="Edit Data User & Penugasan Unit"
                                            class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 shadow-2xs hover:shadow-md hover:shadow-indigo-500/20 transition-all duration-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>

                                        <button 
                                            wire:click="openResetPasswordModal({{ $u->id }})"
                                            title="Reset Password Pengguna"
                                            class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-amber-500 hover:text-white hover:border-amber-500 shadow-2xs hover:shadow-md hover:shadow-amber-500/20 transition-all duration-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                            </svg>
                                        </button>

                                        @if ($u->id !== auth()->id())
                                            <button 
                                                wire:click="deleteUser({{ $u->id }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus akun pengguna '{{ $u->name }}'?"
                                                title="Hapus Pengguna"
                                                class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-rose-600 hover:text-white hover:border-rose-600 shadow-2xs hover:shadow-md hover:shadow-rose-500/20 transition-all duration-200">
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
                            <td colspan="6" class="p-6 text-center text-slate-400">
                                Tidak ada data pengguna ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-custom-pagination :paginator="$users" />
    </div>

    <!-- MODAL CREATE / EDIT USER WITH ROLE & MANAGED UNIT CHECKBOXES -->
    @if ($showFormModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-xl overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                        {{ $editingUserId ? 'Edit Data Pengguna & Penugasan Unit' : 'Tambah Pengguna Baru' }}
                    </h3>
                    <button wire:click="$set('showFormModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveUser" class="p-4 space-y-4">
                    <!-- NAME & EMAIL -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Nama Lengkap *</label>
                            <input 
                                wire:model="name" 
                                type="text" 
                                placeholder="Nama lengkap..." 
                                class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs md:text-sm font-bold focus:ring-2 focus:ring-indigo-500 dark:text-slate-100" 
                                required 
                            />
                            @error('name') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Alamat Email *</label>
                            <input 
                                wire:model="email" 
                                type="email" 
                                placeholder="email@artaledger.com" 
                                class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs md:text-sm font-bold focus:ring-2 focus:ring-indigo-500 dark:text-slate-100" 
                                required 
                            />
                            @error('email') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- PASSWORD (FOR NEW USER OR OPTIONAL EDIT) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">
                                {{ $editingUserId ? 'Password Baru (Opsional)' : 'Password *' }}
                            </label>
                            <input 
                                wire:model="password" 
                                type="password" 
                                placeholder="Minimal 8 karakter..." 
                                class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs md:text-sm focus:ring-2 focus:ring-indigo-500 dark:text-slate-100" 
                                {{ $editingUserId ? '' : 'required' }} 
                            />
                            @error('password') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Konfirmasi Password</label>
                            <input 
                                wire:model="password_confirmation" 
                                type="password" 
                                placeholder="Ulangi password..." 
                                class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs md:text-sm focus:ring-2 focus:ring-indigo-500 dark:text-slate-100" 
                            />
                        </div>
                    </div>

                    <!-- ROLES CHECKBOXES -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Pilih Peran (Roles)</label>
                        <div class="flex flex-wrap gap-2 p-2 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/80">
                            @foreach ($allRoles as $r)
                                <label class="flex items-center gap-1.5 px-2.5 py-1 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer text-xs font-bold text-slate-800 dark:text-slate-200">
                                    <input 
                                        type="checkbox" 
                                        wire:model="selectedRoles" 
                                        value="{{ $r->name }}" 
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <span>{{ $r->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- MANAGED UNITS CHECKBOXES -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Unit Perusahaan yang Dikelola (Managed Units)</label>
                        <p class="text-[11px] text-slate-400 mb-1">Jika tidak ada unit yang dicentang, pengguna dianggap memiliki akses ke <strong>Semua Unit (Global)</strong>.</p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/80 max-h-36 overflow-y-auto">
                            @foreach ($allUnits as $un)
                                <label class="flex items-center gap-2 p-1.5 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer text-xs font-medium text-slate-800 dark:text-slate-200">
                                    <input 
                                        type="checkbox" 
                                        wire:model="selectedUnits" 
                                        value="{{ $un->id }}" 
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">[{{ $un->code }}]</span>
                                    <span class="truncate">{{ $un->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                        <button 
                            type="button" 
                            wire:click="$set('showFormModal', false)" 
                            class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg">
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-md shadow-indigo-500/20 transition-all">
                            Simpan Data Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL RESET PASSWORD -->
    @if ($showResetPasswordModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                        Reset Password Pengguna
                    </h3>
                    <button wire:click="$set('showResetPasswordModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveResetPassword" class="p-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Password Baru *</label>
                        <input 
                            wire:model="newPassword" 
                            type="password" 
                            placeholder="Minimal 8 karakter..." 
                            class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs md:text-sm focus:ring-2 focus:ring-indigo-500 dark:text-slate-100" 
                            required 
                        />
                        @error('newPassword') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Konfirmasi Password Baru *</label>
                        <input 
                            wire:model="newPassword_confirmation" 
                            type="password" 
                            placeholder="Ulangi password baru..." 
                            class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs md:text-sm focus:ring-2 focus:ring-indigo-500 dark:text-slate-100" 
                            required 
                        />
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                        <button 
                            type="button" 
                            wire:click="$set('showResetPasswordModal', false)" 
                            class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg">
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg shadow-md shadow-amber-500/20 transition-all">
                            Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
