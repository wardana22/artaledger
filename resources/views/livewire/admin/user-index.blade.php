<div class="p-4 sm:p-6 space-y-5">
    <!-- TOP NAV TABS WITH LIVE COUNTER -->
    <x-user-access-nav active="users" />

    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-500/30 flex items-center justify-center shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg md:text-xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">
                        Manajemen Pengguna & Hak Akses
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Kelola akun kredensial, peran otorisasi dinamis (*Dynamic Roles*), dan cakupan unit multi-tenant.
                    </p>
                </div>
            </div>
        </div>

        @can('admin.users')
            <button 
                wire:click="openCreateModal"
                class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-xs rounded-xl shadow-md shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:-translate-y-0.5 transition-all duration-200 gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Tambah Pengguna Baru</span>
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

    <!-- TOP KPI STAT CARDS (UI/UX PRO MAX) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <!-- Card 1: Total Pengguna -->
        <div class="p-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-2xs hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Pengguna</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 font-mono">{{ $totalUsers }}</span>
                <span class="text-[11px] font-semibold text-slate-400">Akun Terdaftar</span>
            </div>
        </div>

        <!-- Card 2: Super Admin -->
        <div class="p-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-2xs hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Super Admin</span>
                <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl font-extrabold text-purple-600 dark:text-purple-400 font-mono">{{ $superAdminCount }}</span>
                <span class="text-[11px] font-semibold text-slate-400">Hak Penuh (Root)</span>
            </div>
        </div>

        <!-- Card 3: Finance Manager -->
        <div class="p-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-2xs hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Manajer Keuangan</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">{{ $accountantCount }}</span>
                <span class="text-[11px] font-semibold text-slate-400">Akuntan & Reviewer</span>
            </div>
        </div>

        <!-- Card 4: Staf Unit Keuangan -->
        <div class="p-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-2xs hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Staf Keuangan Unit</span>
                <div class="w-8 h-8 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl font-extrabold text-sky-600 dark:text-sky-400 font-mono">{{ $staffCount }}</span>
                <span class="text-[11px] font-semibold text-slate-400">Entri Jurnal Cabang</span>
            </div>
        </div>
    </div>

    <!-- SEARCH & FILTER TOOLBAR -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-3.5 rounded-2xl shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto flex-1">
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
                    class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 dark:text-slate-200 transition-all font-medium"
                />
            </div>

            <!-- Role Filter Dropdown -->
            <div class="w-full sm:w-60">
                <select 
                    wire:model.live="roleFilter" 
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 dark:text-slate-200 font-medium transition-all"
                >
                    <option value="">Semua Peran (All Roles)</option>
                    @foreach ($allRoles as $r)
                        <option value="{{ $r->name }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if ($roleFilter !== '' || $search !== '')
            <button 
                wire:click="$set('roleFilter', ''); $set('search', '')" 
                class="px-3 py-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 bg-rose-500/10 hover:bg-rose-500/20 rounded-xl transition-all flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span>Reset Filter</span>
            </button>
        @endif
    </div>

    <!-- USER DATA TABLE CARD -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50/80 dark:bg-slate-800/60 uppercase font-bold text-[11px] text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3 w-12 text-center">#</th>
                        <th class="px-4 py-3 min-w-[220px]">Pengguna</th>
                        <th class="px-4 py-3 w-56">Peran (Role)</th>
                        <th class="px-4 py-3 min-w-[240px]">Cakupan Unit Multi-Tenant</th>
                        <th class="px-4 py-3 w-32 whitespace-nowrap">Terdaftar</th>
                        <th class="px-4 py-3 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse ($users as $index => $u)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-4 py-3 text-center text-slate-400 font-mono text-xs">
                                {{ $users->firstItem() + $index }}
                            </td>
                            
                            <!-- User Name & Email -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                        {{ $u->initials() }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <p class="font-bold text-slate-800 dark:text-slate-100 truncate">{{ $u->name }}</p>
                                            @if ($u->id === auth()->id())
                                                <span class="px-1.5 py-0.2 text-[9px] font-extrabold uppercase rounded-sm bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">Anda</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-slate-400 font-mono truncate">{{ $u->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Roles Badges -->
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($u->roles as $r)
                                        @php
                                            $roleColor = match ($r->name) {
                                                'Super Admin' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
                                                'Akuntan / Finance Manager' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                                'Staf Keuangan' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20',
                                                'Auditor / Viewer' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                                default => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg border {{ $roleColor }}">
                                            {{ $r->name }}
                                        </span>
                                    @empty
                                        <span class="text-[11px] text-slate-400 italic">Tanpa Peran</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Managed Units Badges -->
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @if ($u->units->count() === 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[11px] font-bold rounded-lg bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            🌐 Akses Global (Seluruh Unit)
                                        </span>
                                    @else
                                        @foreach ($u->units as $un)
                                            <span class="px-2 py-0.5 text-[10px] font-mono font-bold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                {{ $un->code }}
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                            </td>

                            <!-- Created At -->
                            <td class="px-4 py-3 text-slate-500 dark:text-slate-400 whitespace-nowrap text-xs font-mono">
                                {{ $u->created_at ? $u->created_at->format('d M Y') : '-' }}
                            </td>

                            <!-- Dynamic Action Icons Rule -->
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    @can('admin.users')
                                        <!-- Edit Action (Hover Solid Indigo) -->
                                        <button 
                                            wire:click="openEditModal({{ $u->id }})"
                                            title="Edit Data Pengguna & Penugasan Unit"
                                            class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 shadow-2xs hover:shadow-md hover:shadow-indigo-500/25 transition-all duration-200 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>

                                        <!-- Reset Password Action (Hover Solid Amber) -->
                                        <button 
                                            wire:click="openResetPasswordModal({{ $u->id }})"
                                            title="Reset Kata Sandi"
                                            class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-amber-500 hover:text-white hover:border-amber-500 shadow-2xs hover:shadow-md hover:shadow-amber-500/25 transition-all duration-200 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                            </svg>
                                        </button>

                                        <!-- Delete Action (Hover Solid Rose) -->
                                        @if ($u->id !== auth()->id())
                                            <button 
                                                wire:click="deleteUser({{ $u->id }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus akun pengguna '{{ $u->name }}'?"
                                                title="Hapus Akun Pengguna"
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
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <p class="text-xs font-semibold">Tidak ada data pengguna ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-custom-pagination :paginator="$users" />
    </div>

    <!-- MODAL CREATE / EDIT USER DENGAN SEGMENTED TAB (UI/UX PRO MAX) -->
    @if ($showFormModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs transition-opacity animate-fadeIn">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/70 dark:bg-slate-800/40">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                {{ $editingUserId ? 'Edit Data Pengguna' : 'Tambah Pengguna Baru' }}
                            </h3>
                            <p class="text-[11px] text-slate-400">Atur akun, peran otorisasi, dan penugasan unit perusahaan.</p>
                        </div>
                    </div>
                    <button wire:click="$set('showFormModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Sub-Nav Segments (Akun / Peran / Unit) -->
                <div class="px-6 pt-3 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2 bg-slate-50/40 dark:bg-slate-900/50 overflow-x-auto">
                    <button 
                        type="button" 
                        wire:click="setFormTab('account')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $activeFormTab === 'account' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                        <span>1. Akun & Kredensial</span>
                    </button>
                    <button 
                        type="button" 
                        wire:click="setFormTab('roles')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $activeFormTab === 'roles' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                        <span>2. Peran & Akses (Roles)</span>
                        <span class="px-1.5 py-0.2 text-[10px] font-mono rounded-md {{ $activeFormTab === 'roles' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                            {{ count($selectedRoles) }}
                        </span>
                    </button>
                    <button 
                        type="button" 
                        wire:click="setFormTab('units')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $activeFormTab === 'units' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                        <span>3. Cakupan Unit</span>
                        <span class="px-1.5 py-0.2 text-[10px] font-mono rounded-md {{ $activeFormTab === 'units' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                            {{ $isGlobalUnit ? 'Global' : count($selectedUnits) }}
                        </span>
                    </button>
                </div>

                <form wire:submit.prevent="saveUser" class="flex-1 overflow-y-auto p-6 space-y-4">
                    <!-- TAB 1: AKUN & KREDENSIAL -->
                    @if ($activeFormTab === 'account')
                        <div class="space-y-4 animate-fadeIn">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Nama Lengkap *</label>
                                    <input 
                                        wire:model="name" 
                                        type="text" 
                                        placeholder="Nama lengkap..." 
                                        class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs md:text-sm font-semibold focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 dark:text-slate-100" 
                                        required 
                                    />
                                    @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Alamat Email *</label>
                                    <input 
                                        wire:model="email" 
                                        type="email" 
                                        placeholder="email@artaledger.com" 
                                        class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs md:text-sm font-semibold focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 dark:text-slate-100" 
                                        required 
                                    />
                                    @error('email') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100 dark:border-slate-800">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">
                                        {{ $editingUserId ? 'Kata Sandi Baru (Opsional)' : 'Kata Sandi *' }}
                                    </label>
                                    <input 
                                        wire:model="password" 
                                        type="password" 
                                        placeholder="Minimal 8 karakter..." 
                                        class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs md:text-sm font-semibold focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 dark:text-slate-100" 
                                        {{ $editingUserId ? '' : 'required' }}
                                    />
                                    @error('password') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Konfirmasi Kata Sandi</label>
                                    <input 
                                        wire:model="password_confirmation" 
                                        type="password" 
                                        placeholder="Ulangi kata sandi..." 
                                        class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs md:text-sm font-semibold focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 dark:text-slate-100" 
                                        {{ $editingUserId ? '' : 'required' }}
                                    />
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- TAB 2: PERAN & AKSES (ROLES) -->
                    @if ($activeFormTab === 'roles')
                        <div class="space-y-3 animate-fadeIn">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">
                                    Pilih Peran Sistem (Roles)
                                </label>
                                <span class="text-[11px] text-slate-400">Pengguna dapat memiliki lebih dari satu peran</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                @foreach ($allRoles as $role)
                                    @php
                                        $isSuperAdminRole = $role->name === 'Super Admin';
                                        $canAssignSuperAdmin = auth()->user()->hasRole('Super Admin');
                                    @endphp
                                    <label class="relative flex items-start gap-3 p-3.5 rounded-2xl border transition-all cursor-pointer {{ in_array($role->name, $selectedRoles) ? 'bg-indigo-50/50 dark:bg-indigo-950/20 border-indigo-500/50 ring-1 ring-indigo-500/30' : 'bg-slate-50/70 dark:bg-slate-800/40 border-slate-200/80 dark:border-slate-700/80 hover:bg-slate-100/80' }}">
                                        <input 
                                            type="checkbox" 
                                            wire:model="selectedRoles" 
                                            value="{{ $role->name }}" 
                                            @if ($isSuperAdminRole && ! $canAssignSuperAdmin) disabled @endif
                                            class="mt-0.5 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 dark:bg-slate-700" 
                                        />
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="text-xs font-bold text-slate-800 dark:text-slate-100">{{ $role->name }}</span>
                                                @if ($isSuperAdminRole)
                                                    <span class="px-1.5 py-0.2 text-[9px] font-extrabold uppercase rounded-sm bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">Root</span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                {{ $role->permissions->count() }} izin modul terdaftar
                                            </p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('selectedRoles') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <!-- TAB 3: CAKUPAN UNIT MULTI-TENANT -->
                    @if ($activeFormTab === 'units')
                        <div class="space-y-4 animate-fadeIn">
                            <!-- Toggle Akses Global vs Spesifik -->
                            <div class="p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-slate-50/60 dark:bg-slate-800/40 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-100 block">
                                            Akses Global Seluruh Unit Perusahaan (Konsolidasi)
                                        </span>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                            Jika diaktifkan, pengguna dapat melihat dan memfilter transaksi dari seluruh unit tanpa batasan.
                                        </p>
                                    </div>
                                    <button 
                                        type="button" 
                                        wire:click="toggleGlobalUnitAccess"
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $isGlobalUnit ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-700' }}">
                                        <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out {{ $isGlobalUnit ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>
                                </div>
                            </div>

                            @if (! $isGlobalUnit)
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">
                                            Pilih Unit yang Dikelola Secara Khusus
                                        </label>
                                        <div class="flex items-center gap-2">
                                            <button 
                                                type="button" 
                                                wire:click="selectAllUnits" 
                                                class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                                Pilih Semua
                                            </button>
                                            <span class="text-slate-300 dark:text-slate-700">|</span>
                                            <button 
                                                type="button" 
                                                wire:click="unselectAllUnits" 
                                                class="text-[11px] font-bold text-slate-500 hover:underline">
                                                Kosongkan
                                            </button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-56 overflow-y-auto p-1">
                                        @foreach ($allUnits as $unit)
                                            <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-700/80 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer transition-colors {{ in_array($unit->id, $selectedUnits) ? 'bg-indigo-50/40 dark:bg-indigo-950/20 border-indigo-500/40' : 'bg-white dark:bg-slate-900' }}">
                                                <input 
                                                    type="checkbox" 
                                                    wire:model="selectedUnits" 
                                                    value="{{ $unit->id }}" 
                                                    class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 dark:bg-slate-700" 
                                                />
                                                <span class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $unit->code }}</span>
                                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300 truncate">{{ $unit->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Modal Actions Footer -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            @if ($activeFormTab !== 'account')
                                <button 
                                    type="button" 
                                    wire:click="setFormTab('{{ $activeFormTab === 'units' ? 'roles' : 'account' }}')"
                                    class="px-3.5 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 rounded-xl hover:bg-slate-200 transition-colors">
                                    &larr; Sebelumnya
                                </button>
                            @endif

                            @if ($activeFormTab !== 'units')
                                <button 
                                    type="button" 
                                    wire:click="setFormTab('{{ $activeFormTab === 'account' ? 'roles' : 'units' }}')"
                                    class="px-3.5 py-2 text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-500/10 hover:bg-indigo-500/20 rounded-xl transition-colors">
                                    Lanjut &rarr;
                                </button>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            <button 
                                type="button" 
                                wire:click="$set('showFormModal', false)" 
                                class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 dark:text-slate-400 transition-colors">
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
                                <span>{{ $editingUserId ? 'Perbarui Pengguna' : 'Simpan Pengguna' }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL RESET PASSWORD -->
    @if ($showResetPasswordModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs transition-opacity animate-fadeIn">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/70 dark:bg-slate-800/40">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Reset Kata Sandi Pengguna</h3>
                    </div>
                    <button wire:click="$set('showResetPasswordModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveResetPassword" class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Kata Sandi Baru *</label>
                        <input 
                            wire:model="newPassword" 
                            type="password" 
                            placeholder="Minimal 8 karakter..." 
                            class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs md:text-sm font-semibold focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 dark:text-slate-100" 
                            required 
                        />
                        @error('newPassword') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Konfirmasi Kata Sandi Baru *</label>
                        <input 
                            wire:model="newPassword_confirmation" 
                            type="password" 
                            placeholder="Ketik ulang kata sandi baru..." 
                            class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs md:text-sm font-semibold focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 dark:text-slate-100" 
                            required 
                        />
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                        <button 
                            type="button" 
                            wire:click="$set('showResetPasswordModal', false)" 
                            class="px-3.5 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 dark:text-slate-400 transition-colors">
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="px-4 py-2 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-md shadow-amber-500/25 transition-all flex items-center gap-1.5 cursor-pointer">
                            <svg wire:loading class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Simpan Kata Sandi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
