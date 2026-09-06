<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <span>Akuntansi</span>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-indigo-600 dark:text-indigo-400 font-medium">Aset Tetap</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Register Aset Tetap & Amortisasi
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Pengelolaan inventaris aset berwujud, nilai buku, dan jadwal depresiasi garis lurus (Straight-Line).
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button wire:click="openCategoryManagerModal" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                Kelola Kategori
            </button>

            <button wire:click="openBatchLabelModal"
                    title="Cetak label barcode semua aset yang difilter"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-emerald-200 dark:border-emerald-800/60 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-semibold text-sm hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print Label
            </button>

            <a href="{{ route('accounting.fixed-assets.depreciation') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-indigo-200 dark:border-indigo-800/60 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-semibold text-sm hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-all shadow-sm">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Eksekusi Penyusutan Bulanan
            </a>

            <button wire:click="openCreateModal" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-semibold text-sm shadow-md shadow-indigo-500/20 hover:shadow-indigo-500/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Aset Tetap
            </button>
        </div>
    </div>

    <!-- Alert Flash Notifications -->
    @if (session()->has('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Perolehan -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 rounded-2xl shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Harga Perolehan</span>
                <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900 dark:text-white font-mono">
                    Rp {{ number_format($totalCost, 0, ',', '.') }}
                </div>
                <div class="text-xs text-slate-400 mt-1">Nilai bruto seluruh aset terdaftar</div>
            </div>
        </div>

        <!-- Akumulasi Penyusutan -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 rounded-2xl shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Akumulasi Penyusutan</span>
                <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 font-mono">
                    Rp {{ number_format($totalAccum, 0, ',', '.') }}
                </div>
                <div class="text-xs text-slate-400 mt-1">Total beban yang telah dibukukan</div>
            </div>
        </div>

        <!-- Nilai Buku Bersih (Net Book Value) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 rounded-2xl shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Nilai Buku Bersih (NBV)</span>
                <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                    Rp {{ number_format($totalBookValue, 0, ',', '.') }}
                </div>
                <div class="text-xs text-slate-400 mt-1">Nilai tercatat di Laporan Neraca</div>
            </div>
        </div>

        <!-- Total Aset Aktif -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 rounded-2xl shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Aset Aktif Disusutkan</span>
                <span class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 font-mono">
                    {{ $totalActive }} <span class="text-sm font-normal text-slate-500">Unit</span>
                </div>
                <div class="text-xs text-slate-400 mt-1">Aset dengan masa manfaat berjalan</div>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <!-- Search -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" 
                       placeholder="Cari kode, nama aset, PIC..." 
                       class="w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
            </div>

            <!-- Kategori -->
            <div>
                <select wire:model.live="selectedCategoryId" 
                        class="w-full py-2 px-3 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">Semua Kategori Aset</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Unit -->
            <div>
                <select wire:model.live="selectedUnitId" 
                        class="w-full py-2 px-3 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">Semua Unit Kerja</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            <div>
                <select wire:model.live="selectedStatus" 
                        class="w-full py-2 px-3 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif Disusutkan</option>
                    <option value="fully_depreciated">Habis Disusutkan</option>
                    <option value="disposed">Dilepas / Dijual</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/60 text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">
                        <th class="py-3.5 px-4">Kode & Nama Aset</th>
                        <th class="py-3.5 px-4">Kategori & Unit</th>
                        <th class="py-3.5 px-4">Tgl Perolehan</th>
                        <th class="py-3.5 px-4 text-right">Harga Perolehan</th>
                        <th class="py-3.5 px-4 text-right">Beban / Bln</th>
                        <th class="py-3.5 px-4 text-right">Akumulasi Susut</th>
                        <th class="py-3.5 px-4 text-right">Nilai Buku</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-sm">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $asset->name }}</div>
                                <div class="font-mono text-xs text-indigo-600 dark:text-indigo-400">{{ $asset->asset_code }}</div>
                                @if($asset->location)
                                    <div class="text-[11px] text-slate-400 flex items-center gap-1 mt-0.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                        {{ $asset->location }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ $asset->category->name }}
                                </span>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    {{ $asset->unit->name }} ({{ $asset->unit->code }})
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300">
                                <div>{{ $asset->acquisition_date->format('d/m/Y') }}</div>
                                <div class="text-xs text-slate-400">{{ $asset->useful_life_months }} bln ({{ round($asset->useful_life_months / 12, 1) }} thn)</div>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-medium text-slate-900 dark:text-white">
                                Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-xs text-slate-600 dark:text-slate-400">
                                Rp {{ number_format($asset->monthly_depreciation_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-amber-600 dark:text-amber-400 font-medium">
                                Rp {{ number_format($asset->accumulated_depreciation, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($asset->book_value, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($asset->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Aktif
                                    </span>
                                @elseif($asset->status === 'fully_depreciated')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60">
                                        Habis Susut
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                        Dilepas
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Tombol Label/Barcode -->
                                    <button wire:click="openLabelModal({{ $asset->id }})"
                                            title="Cetak Label Barcode Aset"
                                            class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 transition-all duration-200 hover:bg-emerald-600 hover:text-white hover:border-emerald-600 shadow-2xs hover:shadow-md hover:shadow-emerald-500/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 3.5a.5.5 0 11-1 0 .5.5 0 011 0zM6 20h4"/></svg>
                                    </button>

                                    <!-- Tombol Jadwal -->
                                    <button wire:click="viewSchedule({{ $asset->id }})" 
                                            title="Lihat Jadwal Amortisasi"
                                            class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 transition-all duration-200 hover:bg-sky-600 hover:text-white hover:border-sky-600 shadow-2xs hover:shadow-md hover:shadow-sky-500/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </button>

                                    <!-- Tombol Edit -->
                                    <button wire:click="openEditModal({{ $asset->id }})" 
                                            title="Edit Data Aset"
                                            class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 transition-all duration-200 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 shadow-2xs hover:shadow-md hover:shadow-indigo-500/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>

                                    <!-- Tombol Hapus -->
                                    <button wire:click="deleteAsset({{ $asset->id }})" 
                                            wire:confirm="Yakin ingin menghapus aset ini? Pastikan belum ada transaksi jurnal penyusutan."
                                            title="Hapus Aset"
                                            class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 transition-all duration-200 hover:bg-rose-600 hover:text-white hover:border-rose-600 shadow-2xs hover:shadow-md hover:shadow-rose-500/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <p class="text-base font-semibold text-slate-600 dark:text-slate-300">Belum Ada Aset Tetap Terdaftar</p>
                                <p class="text-xs text-slate-400 mt-1">Daftarkan inventaris aset tetap perusahaan untuk memulai kalkulasi amortisasi otomatis.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assets->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $assets->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Tambah / Edit Aset -->
    @if($showAssetModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200"
             wire:click.self="$set('showAssetModal', false)"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="relative bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl w-full max-w-2xl border border-slate-200 dark:border-slate-800 my-8">
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        {{ $editingAssetId ? 'Edit Data Aset Tetap' : 'Daftarkan Aset Tetap Baru' }}
                    </h3>
                    <button wire:click="$set('showAssetModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveAsset">
                    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                        <!-- Unit & Kategori -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Unit Kerja *</label>
                                <select wire:model="unit_id" class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->code }})</option>
                                    @endforeach
                                </select>
                                @error('unit_id') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Kategori Aset *</label>
                                    <button type="button" wire:click="openCreateCategoryModal" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Kategori Baru
                                    </button>
                                </div>
                                <select wire:model.live="asset_category_id" class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->useful_life_years }} thn)</option>
                                    @endforeach
                                </select>
                                @error('asset_category_id') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Nama & Kode -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Nama Aset *</label>
                                <input type="text" wire:model="name" placeholder="Contoh: Toyota Hilux 2.4 G M/T" 
                                       class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                                @error('name') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Kode Aset (Opsional)</label>
                                <input type="text" wire:model="asset_code" placeholder="Otomatis jika kosong" 
                                       class="w-full text-sm font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <!-- Tanggal Perolehan & Mulai Penyusutan -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Tanggal Perolehan *</label>
                                <input type="date" wire:model="acquisition_date" 
                                       class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                                @error('acquisition_date') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Mulai Disusutkan *</label>
                                <input type="date" wire:model="start_depreciation_date" 
                                       class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                                @error('start_depreciation_date') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Harga Perolehan, Residu, Masa Manfaat -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Harga Perolehan (Rp) *</label>
                                <input type="number" step="any" wire:model.live="acquisition_cost" 
                                       class="w-full text-sm font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                                @error('acquisition_cost') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Nilai Residu (Rp)</label>
                                <input type="number" step="any" wire:model.live="salvage_value" 
                                       class="w-full text-sm font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Masa Manfaat (Bulan) *</label>
                                <input type="number" wire:model.live="useful_life_months" 
                                       class="w-full text-sm font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                                @error('useful_life_months') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Live Calculation Banner -->
                        <div class="p-3.5 rounded-xl bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-950/40 dark:to-blue-950/40 border border-indigo-100 dark:border-indigo-900/50 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="p-2 rounded-lg bg-indigo-600 text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-indigo-900 dark:text-indigo-300">Estimasi Beban Garis Lurus:</div>
                                    <div class="text-xs text-indigo-600 dark:text-indigo-400">Formula: (Harga Perolehan - Residu) / Masa Manfaat Bulan</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold font-mono text-indigo-700 dark:text-indigo-300">
                                    Rp {{ number_format($this->estimatedMonthly, 0, ',', '.') }}
                                </div>
                                <div class="text-[11px] text-slate-500">per bulan</div>
                            </div>
                        </div>

                        <!-- Lokasi, PIC, Serial Number -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Lokasi Fisik</label>
                                <input type="text" wire:model="location" placeholder="e.g. Kantor Lt. 2" 
                                       class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Penanggung Jawab (PIC)</label>
                                <input type="text" wire:model="person_in_charge" placeholder="e.g. Budi Santoso" 
                                       class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">No. Seri / Rangka</label>
                                <input type="text" wire:model="serial_number" placeholder="e.g. MH1JM..." 
                                       class="w-full text-sm font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <!-- Catatan -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Catatan Tambahan</label>
                            <textarea wire:model="notes" rows="2" placeholder="Keterangan spesifikasi teknis, kondisi fisik, dll..."
                                      class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>

                        <!-- Foto Aset -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Foto Aset</label>
                            <div class="relative">
                                @if($photo)
                                    {{-- Preview foto baru --}}
                                    <div class="relative rounded-xl overflow-hidden border-2 border-indigo-400 dark:border-indigo-600">
                                        <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="w-full h-40 object-cover">
                                        <div class="absolute top-2 right-2">
                                            <button type="button" wire:click="$set('photo', null)"
                                                    class="p-1.5 rounded-lg bg-rose-600 text-white shadow-lg hover:bg-rose-700 transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                        <div class="absolute bottom-0 inset-x-0 bg-indigo-600/80 text-white text-[10px] text-center py-1 font-medium">Foto baru — belum disimpan</div>
                                    </div>
                                @elseif($existingPhotoPath)
                                    {{-- Foto existing --}}
                                    <div class="relative rounded-xl overflow-hidden border-2 border-slate-200 dark:border-slate-700">
                                        <img src="{{ Storage::url($existingPhotoPath) }}" alt="Foto Aset" class="w-full h-40 object-cover">
                                        <div class="absolute top-2 right-2">
                                            <label class="cursor-pointer p-1.5 rounded-lg bg-indigo-600 text-white shadow-lg hover:bg-indigo-700 transition-all block">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                <input type="file" wire:model="photo" accept="image/*" class="hidden">
                                            </label>
                                        </div>
                                        <div class="absolute bottom-0 inset-x-0 bg-slate-900/60 text-slate-200 text-[10px] text-center py-1">Foto saat ini — klik ✏️ untuk ganti</div>
                                    </div>
                                @else
                                    {{-- Upload zone --}}
                                    <label class="cursor-pointer flex flex-col items-center justify-center gap-2 h-28 rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 hover:border-indigo-400 hover:bg-indigo-50/30 dark:hover:bg-indigo-950/20 transition-all">
                                        <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-xs text-slate-400">Klik untuk upload foto aset</span>
                                        <span class="text-[10px] text-slate-300 dark:text-slate-600">JPG, PNG, WebP — max 2MB</span>
                                        <input type="file" wire:model="photo" accept="image/*" class="hidden">
                                    </label>
                                @endif
                                @error('photo') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex items-center justify-end gap-3">
                        <button type="button" wire:click="$set('showAssetModal', false)" 
                                class="px-4 py-2 text-sm font-semibold rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-5 py-2 text-sm font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-500/20 transition-all">
                            {{ $editingAssetId ? 'Simpan Perubahan' : 'Daftarkan Aset' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal Jadwal Amortisasi Penyusutan (Amortization Schedule) -->
    @if($showScheduleModal && $selectedAssetForSchedule)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200"
             wire:click.self="closeScheduleModal"
             aria-labelledby="modal-schedule" role="dialog" aria-modal="true">
            <div class="relative bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl w-full max-w-3xl border border-slate-200 dark:border-slate-800 my-8">
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Jadwal Penyusutan Garis Lurus (Straight-Line)
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ $selectedAssetForSchedule->name }} ({{ $selectedAssetForSchedule->asset_code }})
                            </p>
                        </div>
                        <button wire:click="closeScheduleModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Summary Info Box -->
                    <div class="p-6 bg-slate-50/75 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                        <div>
                            <span class="text-slate-400 block">Harga Perolehan:</span>
                            <span class="font-bold font-mono text-slate-900 dark:text-white text-sm">Rp {{ number_format($selectedAssetForSchedule->acquisition_cost, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Nilai Residu:</span>
                            <span class="font-bold font-mono text-slate-900 dark:text-white text-sm">Rp {{ number_format($selectedAssetForSchedule->salvage_value, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Masa Manfaat:</span>
                            <span class="font-bold text-slate-900 dark:text-white text-sm">{{ $selectedAssetForSchedule->useful_life_months }} Bulan</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Sisa Nilai Buku:</span>
                            <span class="font-bold font-mono text-emerald-600 dark:text-emerald-400 text-sm">Rp {{ number_format($selectedAssetForSchedule->book_value, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Schedule Table -->
                    <div class="p-6 max-h-[60vh] overflow-y-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">
                                    <th class="py-2.5 px-3">Bulan</th>
                                    <th class="py-2.5 px-3">Periode</th>
                                    <th class="py-2.5 px-3 text-right">Beban Depresiasi</th>
                                    <th class="py-2.5 px-3 text-right">Akumulasi Susut</th>
                                    <th class="py-2.5 px-3 text-right">Nilai Buku</th>
                                    <th class="py-2.5 px-3 text-center">Status Jurnal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($scheduleData as $row)
                                    <tr class="{{ $row['is_posted'] ? 'bg-emerald-50/40 dark:bg-emerald-950/20' : '' }}">
                                        <td class="py-2.5 px-3 font-semibold text-slate-700 dark:text-slate-300">#{{ $row['month_no'] }}</td>
                                        <td class="py-2.5 px-3 font-mono text-slate-900 dark:text-white">{{ $row['period'] }}</td>
                                        <td class="py-2.5 px-3 text-right font-mono text-slate-900 dark:text-white font-medium">
                                            Rp {{ number_format($row['depreciation_amount'], 0, ',', '.') }}
                                        </td>
                                        <td class="py-2.5 px-3 text-right font-mono text-amber-600 dark:text-amber-400">
                                            Rp {{ number_format($row['accumulated_depreciation'], 0, ',', '.') }}
                                        </td>
                                        <td class="py-2.5 px-3 text-right font-mono text-emerald-600 dark:text-emerald-400 font-bold">
                                            Rp {{ number_format($row['book_value'], 0, ',', '.') }}
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            @if($row['is_posted'])
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300">
                                                    ✓ Terposting
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-500">
                                                    Rencana
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-slate-400">
                                            Aset ini tidak memiliki jadwal penyusutan (non-depreciable).
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex justify-end">
                        <button type="button" wire:click="closeScheduleModal" 
                                class="px-4 py-2 text-sm font-semibold rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 transition-all">
                            Tutup
                        </button>
                    </div>
                </div>
        </div>
    @endif

    <!-- MODAL 1: KELOLA MASTER KATEGORI ASET (LIST & CRUD ACTIONS) -->
    @if($showCategoryManagerModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200"
             wire:click.self="closeCategoryManagerModal"
             aria-labelledby="modal-category-manager" role="dialog" aria-modal="true">
            <div class="relative bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl w-full max-w-4xl border border-slate-200 dark:border-slate-800 my-8">
                <!-- Header -->
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            Master Kategori Aset Tetap & Pemetaan COA
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Konfigurasi kelompok aset tetap, masa manfaat standar, dan akun pembukuan otomatis.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="openCreateCategoryModal" 
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Kategori Baru
                        </button>
                        <button wire:click="closeCategoryManagerModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 p-1.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Table Categories -->
                <div class="p-6 max-h-[60vh] overflow-y-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">
                                <th class="py-2.5 px-3">Kode & Nama</th>
                                <th class="py-2.5 px-3 text-center">Masa Manfaat</th>
                                <th class="py-2.5 px-3">Akun Aset & Akumulasi</th>
                                <th class="py-2.5 px-3">Akun Beban</th>
                                <th class="py-2.5 px-3 text-center">Aset Terdaftar</th>
                                <th class="py-2.5 px-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($allCategories as $c)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="py-3 px-3">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $c->name }}</div>
                                        <div class="font-mono text-[11px] text-indigo-600 dark:text-indigo-400 font-semibold">{{ $c->code }}</div>
                                        @if($c->description)
                                            <div class="text-[11px] text-slate-400 mt-0.5">{{ Str::limit($c->description, 45) }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                            {{ $c->useful_life_years }} Thn
                                        </span>
                                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $c->useful_life_years * 12 }} Bulan</div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="text-slate-800 dark:text-slate-200 font-medium">
                                            <span class="text-blue-600 font-bold">A:</span> {{ $c->assetAccount?->code ?? '-' }} {{ $c->assetAccount?->name }}
                                        </div>
                                        <div class="text-slate-500 dark:text-slate-400 mt-0.5">
                                            <span class="text-amber-600 font-bold">K:</span> {{ $c->accumulatedDepreciationAccount?->code ?? '-' }} {{ $c->accumulatedDepreciationAccount?->name }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="text-slate-800 dark:text-slate-200 font-medium">
                                            <span class="text-emerald-600 font-bold">B:</span> {{ $c->depreciationExpenseAccount?->code ?? '-' }} {{ $c->depreciationExpenseAccount?->name }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="font-mono font-semibold {{ $c->fixed_assets_count > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}">
                                            {{ $c->fixed_assets_count }} Unit
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" wire:click="openEditCategoryModal({{ $c->id }})" 
                                                    title="Edit Kategori"
                                                    class="p-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                            @if($c->fixed_assets_count === 0)
                                                <button type="button" wire:click="deleteCategory({{ $c->id }})" 
                                                        wire:confirm="Yakin ingin menghapus kategori '{{ $c->name }}'?"
                                                        title="Hapus Kategori"
                                                        class="p-1.5 rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            @else
                                                <span title="Kategori sedang digunakan oleh {{ $c->fixed_assets_count }} aset (tidak dapat dihapus)" class="p-1.5 text-slate-300 dark:text-slate-600 cursor-not-allowed">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">
                                        Belum ada kategori aset tetap yang terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex justify-end">
                    <button type="button" wire:click="closeCategoryManagerModal" 
                            class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 transition-all">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 2: FORM TAMBAH / EDIT KATEGORI ASET -->
    @if($showCategoryFormModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200"
             wire:click.self="closeCategoryFormModal"
             aria-labelledby="modal-category-form" role="dialog" aria-modal="true">
            <div class="relative bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl w-full max-w-lg border border-slate-200 dark:border-slate-800 my-8">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        {{ $editingCategoryId ? 'Edit Kategori Aset Tetap' : 'Tambah Kategori Aset Tetap Baru' }}
                    </h3>
                    <button type="button" wire:click="closeCategoryFormModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveCategory">
                    <div class="p-5 space-y-3.5 max-h-[70vh] overflow-y-auto text-xs">
                        <!-- Kode & Nama -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Kode *</label>
                                <input type="text" wire:model="cat_code" placeholder="e.g. KAT-ELK" 
                                       class="w-full text-xs font-mono uppercase rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                                @error('cat_code') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Nama Kategori *</label>
                                <input type="text" wire:model="cat_name" placeholder="e.g. Peralatan Elektronik & IT" 
                                       class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                                @error('cat_name') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Masa Manfaat & Nilai Residu -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Masa Manfaat (Tahun) *</label>
                                <input type="number" wire:model="cat_useful_life_years" min="0" max="100" 
                                       class="w-full text-xs font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                                <span class="text-[11px] text-slate-400 mt-0.5 block">Isi 0 jika non-depreciable (seperti Tanah).</span>
                                @error('cat_useful_life_years') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Nilai Residu Default (%)</label>
                                <input type="number" step="0.01" wire:model="cat_salvage_percentage" min="0" max="100" 
                                       class="w-full text-xs font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                                <span class="text-[11px] text-slate-400 mt-0.5 block">Persentase estimasi nilai sisa (default 0%).</span>
                            </div>
                        </div>

                        <!-- Pemetaan Akun COA -->
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-2.5">
                            <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Pemetaan Akun Buku Besar (COA Mapping)
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Akun Aset Tetap (12.xx)</label>
                                <select wire:model="cat_asset_account_id" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Pilih Akun Aset Tetap --</option>
                                    @foreach($assetAccounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Akun Akumulasi Penyusutan (12.10.x)</label>
                                <select wire:model="cat_accumulated_account_id" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Pilih Akun Akumulasi Penyusutan --</option>
                                    @foreach($accumAccounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Akun Beban Penyusutan (68.xx)</label>
                                <select wire:model="cat_expense_account_id" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Pilih Akun Beban Penyusutan --</option>
                                    @foreach($expenseAccounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Deskripsi / Keterangan</label>
                            <textarea wire:model="cat_description" rows="2" placeholder="Keterangan cakupan aset untuk kelompok kategori ini..."
                                      class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>

                    <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex justify-end gap-2.5">
                        <button type="button" wire:click="closeCategoryFormModal" 
                                class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-5 py-2 text-xs font-bold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-500/20 transition-all">
                            {{ $editingCategoryId ? 'Simpan Perubahan' : 'Tambah Kategori' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ============================================================= --}}
    {{-- MODAL: PRINT LABEL TUNGGAL (Single Asset Barcode Label)        --}}
    {{-- ============================================================= --}}
    @if($showLabelModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm"
             wire:click.self="closeLabelModal">
            <div class="relative z-10 w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 bg-gradient-to-r from-slate-50 to-white dark:from-slate-800/60 dark:to-slate-900">
                    <div class="flex items-center gap-2.5">
                        <span class="p-1.5 rounded-lg bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        </span>
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-sm">Cetak Label Aset</h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Preview label sebelum dicetak</p>
                        </div>
                    </div>
                    <button wire:click="closeLabelModal" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Label Preview --}}
                <div class="p-5">
                    <div id="print-label-area" class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl p-4 bg-white">
                        {{-- Label Card --}}
                        <div class="label-card-single font-sans">
                            {{-- Header Logo --}}
                            <div class="flex items-center gap-1.5 pb-2 mb-2 border-b border-slate-200">
                                <svg class="w-4 h-4 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span class="font-bold text-indigo-700 text-sm tracking-wide">ArtaLedger</span>
                                <span class="ml-auto text-[10px] text-slate-400 font-mono">ASET TETAP</span>
                            </div>

                            {{-- Asset Info --}}
                            <div class="mb-2">
                                <div class="font-mono font-bold text-lg text-slate-900 tracking-wider">{{ $labelAssetData['code'] ?? '' }}</div>
                                <div class="font-semibold text-slate-800 text-sm leading-tight">{{ $labelAssetData['name'] ?? '' }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $labelAssetData['category'] ?? '' }} &bull; {{ $labelAssetData['unit'] ?? '' }}</div>
                            </div>

                            <div class="grid grid-cols-2 gap-x-3 text-[11px] mb-3">
                                <div><span class="text-slate-400">Lokasi:</span> <span class="text-slate-700 font-medium">{{ $labelAssetData['location'] ?? '-' }}</span></div>
                                <div><span class="text-slate-400">S/N:</span> <span class="text-slate-700 font-medium">{{ $labelAssetData['serial'] ?? '-' }}</span></div>
                                <div><span class="text-slate-400">Tgl Perolehan:</span> <span class="text-slate-700 font-medium">{{ $labelAssetData['acquisition'] ?? '-' }}</span></div>
                                <div><span class="text-slate-400">Nilai Buku:</span> <span class="text-slate-700 font-medium">{{ $labelAssetData['book_value'] ?? '-' }}</span></div>
                            </div>

                            {{-- Barcodes --}}
                            <div class="flex items-end justify-between gap-3 pt-2 border-t border-slate-100">
                                <div class="flex flex-col items-center gap-1">
                                    <div id="qr-single" class="w-[90px] h-[90px]"></div>
                                    <span class="text-[9px] text-slate-400">Scan Info</span>
                                </div>
                                <div class="flex flex-col items-center gap-1 flex-1">
                                    <svg id="barcode-single" class="max-w-full"></svg>
                                    <span class="text-[9px] text-slate-400">Kode Aset</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-400 text-center mt-3">
                        Ukuran label: 8×5 cm &bull; Cocok untuk stiker perforasi standar
                    </p>
                </div>

                {{-- Footer Buttons --}}
                <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex justify-end gap-2.5">
                    <button wire:click="closeLabelModal"
                            class="px-4 py-2 text-sm font-semibold rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        Tutup
                    </button>
                    <button onclick="window.print()"
                            class="inline-flex items-center gap-2 px-5 py-2 text-sm font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-500/20 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print Label
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================= --}}
    {{-- MODAL: PRINT LABEL BATCH (All/Filtered Assets Barcode Grid)   --}}
    {{-- ============================================================= --}}
    @if($showBatchLabelModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto flex items-start justify-center p-4 pt-8 bg-slate-900/70 backdrop-blur-sm">
            <div class="relative z-10 w-full max-w-5xl bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 bg-gradient-to-r from-slate-50 to-white dark:from-slate-800/60 dark:to-slate-900 sticky top-0 z-10">
                    <div class="flex items-center gap-2.5">
                        <span class="p-1.5 rounded-lg bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        </span>
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-sm">Batch Print Label Aset</h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                {{ count($batchLabelData) }} label &bull; Layout A4 (4 kolom × 5 baris = 20 label/halaman)
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="window.print()"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-500/20 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Print {{ count($batchLabelData) }} Label
                        </button>
                        <button wire:click="closeBatchLabelModal" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Batch Label Grid --}}
                <div class="p-5 max-h-[75vh] overflow-y-auto">
                    @if(count($batchLabelData) === 0)
                        <div class="text-center py-12 text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                            <p class="font-semibold">Tidak ada aset yang cocok dengan filter aktif.</p>
                        </div>
                    @else
                        <div id="print-label-area" class="label-grid grid grid-cols-4 gap-3">
                            @foreach($batchLabelData as $idx => $item)
                                <div class="label-card border border-dashed border-slate-200 rounded-lg p-2.5 bg-white text-[10px] font-sans">
                                    {{-- Mini Header --}}
                                    <div class="flex items-center gap-1 pb-1 mb-1 border-b border-slate-100">
                                        <svg class="w-2.5 h-2.5 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        <span class="font-bold text-indigo-700 text-[9px] tracking-wide">ArtaLedger</span>
                                    </div>
                                    {{-- Code & Name --}}
                                    <div class="font-mono font-bold text-xs text-slate-900 mb-0.5">{{ $item['code'] }}</div>
                                    <div class="text-slate-700 font-semibold leading-tight mb-0.5 truncate">{{ $item['name'] }}</div>
                                    <div class="text-slate-400 text-[9px] mb-1 truncate">{{ $item['category'] }}</div>
                                    {{-- Barcodes --}}
                                    <div class="flex items-end gap-1.5 pt-1 border-t border-slate-100">
                                        <div id="qr-batch-{{ $idx }}" class="w-[56px] h-[56px] flex-shrink-0"></div>
                                        <div class="flex-1 overflow-hidden">
                                            <svg id="barcode-batch-{{ $idx }}" class="w-full"></svg>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================= --}}
    {{-- JS ENGINE: QRCode.js + JsBarcode (client-side, no server)     --}}
    {{-- CSS @media print: only label area visible when printing        --}}
    {{-- ============================================================= --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.6/JsBarcode.all.min.js" defer></script>

    <style>
        @media print {
            /* Hide everything except the label area */
            body > *:not(#app-root),
            body * { visibility: hidden; }

            #print-label-area,
            #print-label-area * { visibility: visible; }

            #print-label-area {
                position: fixed;
                inset: 0;
                margin: 0;
                padding: 8mm;
                background: white;
            }

            /* Batch: A4 grid 4 columns */
            .label-grid {
                display: grid !important;
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 4mm !important;
            }

            .label-card {
                border: 1px solid #cbd5e1 !important;
                border-radius: 4px !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function generateSingleLabel(data) {
                const qrEl = document.getElementById('qr-single');
                const bcEl = document.getElementById('barcode-single');
                if (!qrEl || !bcEl) return;

                // Clear previous
                qrEl.innerHTML = '';

                // QR Code: URL ke halaman publik scan aset
                new QRCode(qrEl, {
                    text: data.scan_url || data.code,
                    width: 90,
                    height: 90,
                    colorDark: '#1e293b',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H,
                });

                // Barcode 1D Code128: only asset code
                try {
                    JsBarcode(bcEl, data.code, {
                        format: 'CODE128',
                        width: 1.8,
                        height: 45,
                        displayValue: true,
                        fontSize: 11,
                        textMargin: 3,
                        margin: 4,
                        background: '#ffffff',
                        lineColor: '#1e293b',
                    });
                } catch (e) {
                    console.warn('Barcode generation error:', e);
                }
            }

            function generateBatchLabels(items) {
                items.forEach(function (item, index) {
                    const qrEl = document.getElementById('qr-batch-' + index);
                    const bcEl = document.getElementById('barcode-batch-' + index);

                    if (qrEl) {
                        qrEl.innerHTML = '';
                        new QRCode(qrEl, {
                            text: item.scan_url || item.code,
                            width: 56,
                            height: 56,
                            colorDark: '#1e293b',
                            colorLight: '#ffffff',
                            correctLevel: QRCode.CorrectLevel.M,
                        });
                    }

                    if (bcEl) {
                        try {
                            JsBarcode(bcEl, item.code, {
                                format: 'CODE128',
                                width: 1.2,
                                height: 28,
                                displayValue: true,
                                fontSize: 8,
                                textMargin: 2,
                                margin: 2,
                                background: '#ffffff',
                                lineColor: '#1e293b',
                            });
                        } catch (e) {
                            console.warn('Barcode error for', item.code, e);
                        }
                    }
                });
            }

            // Livewire event listeners (wait for Livewire to boot)
            document.addEventListener('livewire:initialized', function () {
                Livewire.on('labelModalOpened', function (event) {
                    const data = event.data ?? event[0]?.data ?? event;
                    setTimeout(function () { generateSingleLabel(data); }, 250);
                });

                Livewire.on('batchLabelModalOpened', function (event) {
                    const items = event.items ?? event[0]?.items ?? event;
                    setTimeout(function () { generateBatchLabels(items); }, 300);
                });
            });
        });
    </script>
</div>
