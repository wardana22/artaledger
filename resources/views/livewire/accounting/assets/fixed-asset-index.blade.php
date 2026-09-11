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
                                <div x-data="{
                                    raw: @entangle('acquisition_cost').live,
                                    formatted: '',
                                    scale: '',
                                    updateFormatted() {
                                        this.formatted = window.formatMoneyId(this.raw);
                                        this.scale = window.getTerbilangScale(this.raw);
                                    },
                                    onInput(e) {
                                        let clean = e.target.value.replace(/[^0-9]/g, '');
                                        this.raw = clean === '' ? 0 : parseFloat(clean);
                                        this.formatted = window.formatMoneyId(clean);
                                        this.scale = window.getTerbilangScale(this.raw);
                                    }
                                }" x-init="updateFormatted(); $watch('raw', () => updateFormatted())" class="relative">
                                    <input 
                                        type="text" 
                                        inputmode="numeric"
                                        x-model="formatted" 
                                        @input="onInput($event)"
                                        class="w-full text-sm font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500"
                                        placeholder="0"
                                    >
                                    <template x-if="scale">
                                        <div class="text-[10px] font-medium text-indigo-600 dark:text-indigo-400 text-right mt-1 tracking-tight truncate" x-text="scale"></div>
                                    </template>
                                </div>
                                @error('acquisition_cost') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Nilai Residu (Rp)</label>
                                <div x-data="{
                                    raw: @entangle('salvage_value').live,
                                    formatted: '',
                                    scale: '',
                                    updateFormatted() {
                                        this.formatted = window.formatMoneyId(this.raw);
                                        this.scale = window.getTerbilangScale(this.raw);
                                    },
                                    onInput(e) {
                                        let clean = e.target.value.replace(/[^0-9]/g, '');
                                        this.raw = clean === '' ? 0 : parseFloat(clean);
                                        this.formatted = window.formatMoneyId(clean);
                                        this.scale = window.getTerbilangScale(this.raw);
                                    }
                                }" x-init="updateFormatted(); $watch('raw', () => updateFormatted())" class="relative">
                                    <input 
                                        type="text" 
                                        inputmode="numeric"
                                        x-model="formatted" 
                                        @input="onInput($event)"
                                        class="w-full text-sm font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500"
                                        placeholder="0"
                                    >
                                    <template x-if="scale">
                                        <div class="text-[10px] font-medium text-indigo-600 dark:text-indigo-400 text-right mt-1 tracking-tight truncate" x-text="scale"></div>
                                    </template>
                                </div>
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
    @php
        $company = \App\Models\Company::first();
        $companyLogoUrl = $company?->logo_url;
        $companyName = $company?->name ?? 'PT ArtaLedger Enterprise';
        $appName = $company?->app_name ?? 'E-counting';
    @endphp
    @if($showLabelModal)
        <div x-data="{ paper: 'a4' }"
             class="fixed inset-0 z-[60] overflow-y-auto flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm"
             wire:click.self="closeLabelModal">
            <div class="relative z-10 w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 bg-gradient-to-r from-slate-50 to-white dark:from-slate-800/60 dark:to-slate-900">
                    <div class="flex items-center gap-2.5">
                        <span class="p-1.5 rounded-lg bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        </span>
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-sm">Cetak Label Barcode Aset</h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Format stiker presisi &bull; Siap cetak tanpa atur margin</p>
                        </div>
                    </div>
                    <button wire:click="closeLabelModal" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Format Selector --}}
                <div class="px-5 pt-3 pb-0 flex items-center justify-between">
                    <div class="flex items-center gap-1 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl text-xs w-full">
                        <button type="button" @click="paper = 'a4'"
                                :class="paper === 'a4' ? 'bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
                                class="flex-1 py-1.5 px-3 rounded-lg transition-all flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Kertas A4 (Garis Potong)
                        </button>
                        <button type="button" @click="paper = 'thermal'"
                                :class="paper === 'thermal' ? 'bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
                                class="flex-1 py-1.5 px-3 rounded-lg transition-all flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            Roll Thermal (80×50 mm)
                        </button>
                    </div>
                </div>

                {{-- Label Preview --}}
                <div class="p-5">
                    <div id="print-single-label" class="border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl p-3.5 bg-white max-w-sm mx-auto shadow-sm">
                        {{-- Label Card --}}
                        <div id="print-single-label-card" class="label-card-single font-sans text-slate-900" style="width: 100%; box-sizing: border-box;">
                            {{-- Header Branding --}}
                            <div class="flex items-center justify-between border-b-2 border-slate-900 pb-1 mb-2" style="border-bottom: 2px solid #0f172a; padding-bottom: 3px; margin-bottom: 5px;">
                                <div class="flex items-center gap-1.5" style="display: flex; align-items: center; gap: 5px; max-width: 55mm; overflow: hidden;">
                                    @if ($companyLogoUrl)
                                        <img src="{{ $companyLogoUrl }}" alt="{{ $appName }}" style="width: 15px; height: 15px; object-fit: contain; border-radius: 3px; display: inline-block; flex-shrink: 0; background: #fff;" />
                                    @else
                                        <span class="bg-indigo-600 text-white font-black text-[9px] px-1.5 py-0.5 rounded tracking-wider" style="background-color: #4f46e5; color: #ffffff; font-weight: 900; font-size: 7.5px; padding: 1px 3.5px; border-radius: 2px; letter-spacing: 0.5px; flex-shrink: 0;">ARTA</span>
                                    @endif
                                    <span class="font-black text-slate-900 text-xs tracking-wide truncate" style="font-weight: 900; color: #0f172a; font-size: 8.5pt; letter-spacing: 0.2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">{{ $companyName }}</span>
                                </div>
                                <span class="text-[9px] font-bold text-slate-700 font-mono tracking-wider bg-slate-100 px-2 py-0.5 rounded border border-slate-200" style="font-size: 6.5pt; font-weight: 700; color: #334155; font-family: monospace; background-color: #f1f5f9; padding: 1.5px 5px; border-radius: 2px; border: 1px solid #e2e8f0;">ASET TETAP</span>
                            </div>

                            {{-- Middle Section: QR Code on Left, Info on Right --}}
                            <div class="flex items-start gap-2.5 mb-2" style="display: flex; align-items: flex-start; gap: 8px; margin-bottom: 4px;">
                                {{-- QR Code --}}
                                <div class="flex flex-col items-center flex-shrink-0" style="display: flex; flex-direction: column; align-items: center; flex-shrink: 0;">
                                    <div id="qr-single" style="width: 58px; height: 58px;"></div>
                                    <span class="text-[8px] font-bold text-slate-500 uppercase mt-0.5" style="font-size: 5.5pt; font-weight: 700; color: #64748b; letter-spacing: 0.5px; text-transform: uppercase; margin-top: 1px;">Scan Info</span>
                                </div>

                                {{-- Details --}}
                                <div class="flex-1 min-w-0" style="flex: 1; min-width: 0;">
                                    <div class="font-mono font-black text-sm text-slate-900 tracking-wider leading-none" style="font-family: monospace; font-weight: 900; font-size: 10pt; color: #0f172a; line-height: 1.1; letter-spacing: 0.3px;">{{ $labelAssetData['code'] ?? '' }}</div>
                                    <div class="font-bold text-slate-800 text-xs leading-tight mt-1 truncate" style="font-weight: 700; font-size: 8pt; color: #1e293b; line-height: 1.2; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $labelAssetData['name'] ?? '' }}</div>
                                    <div class="text-[10px] text-slate-500 mt-0.5 truncate" style="font-size: 6.5pt; color: #64748b; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $labelAssetData['category'] ?? '' }} &bull; {{ $labelAssetData['unit'] ?? '' }}</div>

                                    <div class="mt-1.5 pt-1 border-t border-slate-100 text-[10px] text-slate-600 leading-snug" style="margin-top: 3px; padding-top: 2px; border-top: 1px solid #f1f5f9; font-size: 6.5pt; line-height: 1.3; color: #334155;">
                                        <div><span style="color: #94a3b8;">Lok:</span> <strong style="color: #1e293b;">{{ $labelAssetData['location'] ?? '-' }}</strong> &bull; <span style="color: #94a3b8;">PIC:</span> <strong style="color: #1e293b;">{{ $labelAssetData['pic'] ?? '-' }}</strong></div>
                                        <div><span style="color: #94a3b8;">S/N:</span> <strong style="color: #1e293b;">{{ $labelAssetData['serial'] ?? '-' }}</strong> &bull; <span style="color: #94a3b8;">Tgl:</span> <strong style="color: #1e293b;">{{ $labelAssetData['acquisition'] ?? '-' }}</strong></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Bottom Section: Full Width 1D Barcode --}}
                            <div class="pt-1.5 border-t border-slate-200 text-center" style="border-top: 1px solid #cbd5e1; padding-top: 2px; margin-top: auto; text-align: center;">
                                <svg id="barcode-single" style="width: 100%; max-height: 30px; display: block; margin: 0 auto;"></svg>
                            </div>
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-400 text-center mt-3">
                        <span x-show="paper === 'a4'">Format Kertas A4 &bull; Dicetak dengan panduan garis potong rapi (80 × 50 mm)</span>
                        <span x-show="paper === 'thermal'" x-cloak>Format Roll Thermal &bull; Dimensi 80 × 50 mm 1:1 pas di stiker roll</span>
                    </p>
                </div>

                {{-- Footer Buttons --}}
                <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex justify-end gap-2.5">
                    <button wire:click="closeLabelModal"
                            class="px-4 py-2 text-sm font-semibold rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        Tutup
                    </button>
                    <button type="button" @click="printAssetLabel('single', paper)"
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
        <div x-data="{ paper: 'a4' }"
             class="fixed inset-0 z-[60] overflow-y-auto flex items-start justify-center p-4 pt-8 bg-slate-900/70 backdrop-blur-sm">
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
                                <span x-show="paper === 'a4'">{{ count($batchLabelData) }} label &bull; Layout Kertas A4 (Grid 2 kolom &bull; Stiker 80×50 mm &bull; maks 10 label/lembar)</span>
                                <span x-show="paper === 'thermal'" x-cloak>{{ count($batchLabelData) }} label &bull; Roll Thermal (1 stiker 80×50mm beruntun)</span>
                            </p>
                        </div>
                    </div>

                    {{-- Format switcher & print button --}}
                    <div class="flex items-center gap-2.5">
                        <div class="flex items-center gap-1 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl text-xs">
                            <button type="button" @click="paper = 'a4'"
                                    :class="paper === 'a4' ? 'bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
                                    class="py-1 px-3 rounded-lg transition-all flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Kertas A4
                            </button>
                            <button type="button" @click="paper = 'thermal'"
                                    :class="paper === 'thermal' ? 'bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
                                    class="py-1 px-3 rounded-lg transition-all flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                Roll Thermal
                            </button>
                        </div>

                        <button type="button" @click="printAssetLabel('batch', paper)"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-500/20 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Print {{ count($batchLabelData) }} Label
                        </button>

                        <button wire:click="closeBatchLabelModal" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Batch Label Preview Content --}}
                <div class="p-5 max-h-[75vh] overflow-y-auto">
                    @if(count($batchLabelData) === 0)
                        <div class="text-center py-12 text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                            <p class="font-semibold">Tidak ada aset yang cocok dengan filter aktif.</p>
                        </div>
                    @else
                        {{-- 1. Mode Kertas A4 (Grid 2 Kolom, Stiker 80x50 mm) --}}
                        <div x-show="paper === 'a4'" id="print-batch-a4-labels" class="batch-label-grid grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl mx-auto">
                            @foreach($batchLabelData as $idx => $item)
                                <div class="batch-label-card border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl p-3 bg-white text-slate-900 font-sans shadow-sm" style="box-sizing: border-box; width: 100%;">
                                    {{-- Header Logo --}}
                                    <div class="flex items-center justify-between border-b-2 border-slate-900 pb-1 mb-2" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #0f172a; padding-bottom: 3px; margin-bottom: 5px;">
                                        <div class="flex items-center gap-1.5" style="display: flex; align-items: center; gap: 5px; max-width: 55mm; overflow: hidden;">
                                            @if ($companyLogoUrl)
                                                <img src="{{ $companyLogoUrl }}" alt="{{ $appName }}" style="width: 15px; height: 15px; object-fit: contain; border-radius: 3px; display: inline-block; flex-shrink: 0; background: #fff;" />
                                            @else
                                                <span class="bg-indigo-600 text-white font-black text-[9px] px-1.5 py-0.5 rounded tracking-wider" style="background-color: #4f46e5; color: #ffffff; font-weight: 900; font-size: 7.5px; padding: 1px 3.5px; border-radius: 2px; letter-spacing: 0.5px; flex-shrink: 0;">ARTA</span>
                                            @endif
                                            <span class="font-black text-slate-900 text-xs tracking-wide truncate" style="font-weight: 900; color: #0f172a; font-size: 8.5pt; letter-spacing: 0.2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">{{ $companyName }}</span>
                                        </div>
                                        <span class="text-[9px] font-bold text-slate-700 font-mono tracking-wider bg-slate-100 px-2 py-0.5 rounded border border-slate-200" style="font-size: 6.5pt; font-weight: 700; color: #334155; font-family: monospace; background-color: #f1f5f9; padding: 1.5px 5px; border-radius: 2px; border: 1px solid #e2e8f0;">ASET TETAP</span>
                                    </div>

                                    {{-- Middle: QR Code on Left, Info on Right --}}
                                    <div class="flex items-start gap-2.5 mb-2" style="display: flex; align-items: flex-start; gap: 8px; margin-bottom: 4px;">
                                        {{-- QR Code --}}
                                        <div class="flex flex-col items-center flex-shrink-0" style="display: flex; flex-direction: column; align-items: center; flex-shrink: 0;">
                                            <div id="qr-batch-a4-{{ $idx }}" style="width: 58px; height: 58px;"></div>
                                            <span class="text-[8px] font-bold text-slate-500 uppercase mt-0.5" style="font-size: 5.5pt; font-weight: 700; color: #64748b; letter-spacing: 0.5px; text-transform: uppercase; margin-top: 1px;">Scan Info</span>
                                        </div>

                                        {{-- Details --}}
                                        <div class="flex-1 min-w-0" style="flex: 1; min-width: 0;">
                                            <div class="font-mono font-black text-sm text-slate-900 tracking-wider leading-none" style="font-family: monospace; font-weight: 900; font-size: 10pt; color: #0f172a; line-height: 1.1; letter-spacing: 0.3px;">{{ $item['code'] }}</div>
                                            <div class="font-bold text-slate-800 text-xs leading-tight mt-1 truncate" style="font-weight: 700; font-size: 8pt; color: #1e293b; line-height: 1.2; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $item['name'] }}</div>
                                            <div class="text-[10px] text-slate-500 mt-0.5 truncate" style="font-size: 6.5pt; color: #64748b; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $item['category'] }} &bull; {{ $item['unit'] }}</div>

                                            <div class="mt-1.5 pt-1 border-t border-slate-100 text-[10px] text-slate-600 leading-snug" style="margin-top: 3px; padding-top: 2px; border-top: 1px solid #f1f5f9; font-size: 6.5pt; line-height: 1.3; color: #334155;">
                                                <div><span style="color: #94a3b8;">Lok:</span> <strong style="color: #1e293b;">{{ $item['location'] }}</strong> &bull; <span style="color: #94a3b8;">PIC:</span> <strong style="color: #1e293b;">{{ $item['pic'] ?? '-' }}</strong></div>
                                                <div><span style="color: #94a3b8;">S/N:</span> <strong style="color: #1e293b;">{{ $item['serial'] }}</strong> &bull; <span style="color: #94a3b8;">Tgl:</span> <strong style="color: #1e293b;">{{ $item['acquisition'] ?? '-' }}</strong></div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Bottom Section: Full Width 1D Barcode --}}
                                    <div class="pt-1.5 border-t border-slate-200 text-center" style="border-top: 1px solid #cbd5e1; padding-top: 2px; margin-top: auto; text-align: center;">
                                        <svg id="barcode-batch-a4-{{ $idx }}" style="width: 100%; max-height: 30px; display: block; margin: 0 auto;"></svg>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- 2. Mode Roll Thermal (80x50 mm) --}}
                        <div x-show="paper === 'thermal'" x-cloak id="print-batch-thermal-labels" class="flex flex-col items-center gap-4 py-2">
                            @foreach($batchLabelData as $idx => $item)
                                <div class="thermal-batch-card w-full max-w-sm border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl p-3 bg-white text-slate-900 font-sans shadow-sm" style="box-sizing: border-box;">
                                    {{-- Header Logo --}}
                                    <div class="flex items-center justify-between border-b-2 border-slate-900 pb-1 mb-2" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #0f172a; padding-bottom: 3px; margin-bottom: 5px;">
                                        <div class="flex items-center gap-1.5" style="display: flex; align-items: center; gap: 5px; max-width: 55mm; overflow: hidden;">
                                            @if ($companyLogoUrl)
                                                <img src="{{ $companyLogoUrl }}" alt="{{ $appName }}" style="width: 15px; height: 15px; object-fit: contain; border-radius: 3px; display: inline-block; flex-shrink: 0; background: #fff;" />
                                            @else
                                                <span class="bg-indigo-600 text-white font-black text-[9px] px-1.5 py-0.5 rounded tracking-wider" style="background-color: #4f46e5; color: #ffffff; font-weight: 900; font-size: 7.5px; padding: 1px 3.5px; border-radius: 2px; letter-spacing: 0.5px; flex-shrink: 0;">ARTA</span>
                                            @endif
                                            <span class="font-black text-slate-900 text-xs tracking-wide truncate" style="font-weight: 900; color: #0f172a; font-size: 8.5pt; letter-spacing: 0.2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">{{ $companyName }}</span>
                                        </div>
                                        <span class="text-[9px] font-bold text-slate-700 font-mono tracking-wider bg-slate-100 px-2 py-0.5 rounded border border-slate-200" style="font-size: 6.5pt; font-weight: 700; color: #334155; font-family: monospace; background-color: #f1f5f9; padding: 1.5px 5px; border-radius: 2px; border: 1px solid #e2e8f0;">ASET TETAP</span>
                                    </div>

                                    {{-- Middle: QR Code on Left, Info on Right --}}
                                    <div class="flex items-start gap-2.5 mb-2" style="display: flex; align-items: flex-start; gap: 8px; margin-bottom: 4px;">
                                        {{-- QR Code --}}
                                        <div class="flex flex-col items-center flex-shrink-0" style="display: flex; flex-direction: column; align-items: center; flex-shrink: 0;">
                                            <div id="qr-batch-th-{{ $idx }}" style="width: 58px; height: 58px;"></div>
                                            <span class="text-[8px] font-bold text-slate-500 uppercase mt-0.5" style="font-size: 5.5pt; font-weight: 700; color: #64748b; letter-spacing: 0.5px; text-transform: uppercase; margin-top: 1px;">Scan Info</span>
                                        </div>

                                        {{-- Details --}}
                                        <div class="flex-1 min-w-0" style="flex: 1; min-width: 0;">
                                            <div class="font-mono font-black text-sm text-slate-900 tracking-wider leading-none" style="font-family: monospace; font-weight: 900; font-size: 10pt; color: #0f172a; line-height: 1.1; letter-spacing: 0.3px;">{{ $item['code'] }}</div>
                                            <div class="font-bold text-slate-800 text-xs leading-tight mt-1 truncate" style="font-weight: 700; font-size: 8pt; color: #1e293b; line-height: 1.2; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $item['name'] }}</div>
                                            <div class="text-[10px] text-slate-500 mt-0.5 truncate" style="font-size: 6.5pt; color: #64748b; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $item['category'] }} &bull; {{ $item['unit'] }}</div>

                                            <div class="mt-1.5 pt-1 border-t border-slate-100 text-[10px] text-slate-600 leading-snug" style="margin-top: 3px; padding-top: 2px; border-top: 1px solid #f1f5f9; font-size: 6.5pt; line-height: 1.3; color: #334155;">
                                                <div><span style="color: #94a3b8;">Lok:</span> <strong style="color: #1e293b;">{{ $item['location'] }}</strong> &bull; <span style="color: #94a3b8;">PIC:</span> <strong style="color: #1e293b;">{{ $item['pic'] ?? '-' }}</strong></div>
                                                <div><span style="color: #94a3b8;">S/N:</span> <strong style="color: #1e293b;">{{ $item['serial'] }}</strong> &bull; <span style="color: #94a3b8;">Tgl:</span> <strong style="color: #1e293b;">{{ $item['acquisition'] ?? '-' }}</strong></div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Bottom Section: Full Width 1D Barcode --}}
                                    <div class="pt-1.5 border-t border-slate-200 text-center" style="border-top: 1px solid #cbd5e1; padding-top: 2px; margin-top: auto; text-align: center;">
                                        <svg id="barcode-batch-th-{{ $idx }}" style="width: 100%; max-height: 30px; display: block; margin: 0 auto;"></svg>
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
    {{-- JS ASSETS: QRCode.js + JsBarcode                              --}}
    {{-- ============================================================= --}}
    @assets
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.6/JsBarcode.all.min.js"></script>
    @endassets

    @script
    <script>
        window.printAssetLabel = function (targetType, paperMode = 'a4') {
            let printableHtml = '';

            if (targetType === 'single') {
                const cardEl = document.getElementById('print-single-label-card');
                if (!cardEl) return;
                const cardHtml = cardEl.innerHTML;

                if (paperMode === 'a4') {
                    printableHtml = `
                        <div class="a4-sheet-container">
                            <div class="cut-guide-box">
                                <div class="cut-hint">✂ Gunting / Potong Sesuai Garis Putus-putus (Ukuran Stiker: 80 × 50 mm)</div>
                                <div class="sticker-card">
                                    ${cardHtml}
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    printableHtml = `
                        <div class="thermal-sheet-container">
                            <div class="sticker-card">
                                ${cardHtml}
                            </div>
                        </div>
                    `;
                }
            } else if (targetType === 'batch') {
                const sourceEl = paperMode === 'thermal'
                    ? document.getElementById('print-batch-thermal-labels')
                    : document.getElementById('print-batch-a4-labels');

                if (!sourceEl) return;
                printableHtml = sourceEl.outerHTML;
            }

            let iframe = document.getElementById('artaledger-print-frame');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'artaledger-print-frame';
                iframe.style.position = 'fixed';
                iframe.style.top = '-9999px';
                iframe.style.left = '-9999px';
                iframe.style.width = '1px';
                iframe.style.height = '1px';
                iframe.style.border = 'none';
                iframe.style.opacity = '0';
                document.body.appendChild(iframe);
            }

            const frameDoc = iframe.contentWindow.document;
            frameDoc.open();

            let pageRule = '';
            if (paperMode === 'thermal') {
                pageRule = `
                    @page {
                        size: 80mm 50mm;
                        margin: 0;
                    }
                    html, body {
                        width: 80mm;
                        margin: 0;
                        padding: 0;
                        background: #fff;
                    }
                `;
            } else {
                pageRule = `
                    @page {
                        size: A4 portrait;
                        margin: ${targetType === 'single' ? '10mm 12mm' : '10mm 12mm'};
                    }
                    html, body {
                        width: 186mm;
                        margin: 0 auto;
                        padding: 0;
                        background: #fff;
                    }
                `;
            }

            const baseCss = `
                *, *::before, *::after {
                    box-sizing: border-box;
                    margin: 0;
                    padding: 0;
                }
                html, body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                    color: #0f172a;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

                /* Single A4 Guide Container */
                .a4-sheet-container {
                    width: 100%;
                }
                .cut-guide-box {
                    display: inline-block;
                }
                .cut-hint {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    font-size: 7.5pt;
                    color: #64748b;
                    margin-bottom: 2mm;
                    font-weight: 600;
                }

                /* Thermal Sheet Container */
                .thermal-sheet-container {
                    width: 80mm;
                    height: 50mm;
                    margin: 0;
                    padding: 1.5mm;
                    box-sizing: border-box;
                }

                /* Single Sticker Card Dimension (80x50 mm) */
                .sticker-card {
                    width: 80mm !important;
                    min-height: 50mm !important;
                    max-width: 80mm !important;
                    box-sizing: border-box !important;
                    border: 1.5px dashed #475569 !important;
                    border-radius: 4px !important;
                    padding: 2.5mm 3.5mm !important;
                    background: #ffffff !important;
                    display: flex !important;
                    flex-direction: column !important;
                    justify-content: space-between !important;
                    overflow: visible !important;
                }

                /* Batch A4 Grid Layout (2 Kolom x 5 Baris, Stiker 80x50 mm) */
                .batch-label-grid {
                    display: grid !important;
                    grid-template-columns: repeat(2, 80mm) !important;
                    grid-auto-rows: 50mm !important;
                    align-content: start !important;
                    align-items: start !important;
                    justify-content: center !important;
                    column-gap: 8mm !important;
                    row-gap: 4.5mm !important;
                    width: 100% !important;
                    margin: 0 auto !important;
                }
                .batch-label-card {
                    width: 80mm !important;
                    min-height: 50mm !important;
                    max-width: 80mm !important;
                    box-sizing: border-box !important;
                    border: 1.5px dashed #475569 !important;
                    border-radius: 4px !important;
                    padding: 2.5mm 3.5mm !important;
                    background: #ffffff !important;
                    display: flex !important;
                    flex-direction: column !important;
                    justify-content: space-between !important;
                    overflow: visible !important;
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                /* Batch Thermal Roll Continuous Layout */
                .thermal-batch-card {
                    width: 77mm !important;
                    min-height: 47mm !important;
                    max-width: 77mm !important;
                    margin: 1.5mm auto !important;
                    box-sizing: border-box !important;
                    border: 1px dashed #64748b !important;
                    border-radius: 4px !important;
                    padding: 2mm 2.5mm !important;
                    background: #ffffff !important;
                    display: flex !important;
                    flex-direction: column !important;
                    justify-content: space-between !important;
                    overflow: visible !important;
                    page-break-after: always !important;
                    break-after: page !important;
                }
                .thermal-batch-card:last-child {
                    page-break-after: auto !important;
                    break-after: auto !important;
                }

                /* Strict SVG Clamping */
                svg:not([id*="barcode"]) {
                    width: 14px !important;
                    height: 14px !important;
                    max-width: 14px !important;
                    max-height: 14px !important;
                    display: inline-block !important;
                }
                svg[id*="barcode"] {
                    width: 100% !important;
                    max-height: 32px !important;
                    display: block !important;
                }
                /* Utility Classes for Iframe Print */
                .flex { display: flex !important; }
                .flex-col { flex-direction: column !important; }
                .items-center { align-items: center !important; }
                .items-start { align-items: flex-start !important; }
                .justify-between { justify-content: space-between !important; }
                .flex-1 { flex: 1 1 0% !important; }
                .flex-shrink-0 { flex-shrink: 0 !important; }
                .min-w-0 { min-width: 0px !important; }
                .truncate { overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important; }
                .text-center { text-align: center !important; }

                img { max-width: 100% !important; height: auto !important; }
            `;

            frameDoc.write('<!DOCTYPE html><html><head><meta charset="utf-8"><title>ArtaLedger Label</title><style>' + pageRule + baseCss + '</style></head><body>' + printableHtml + '</body></html>');
            frameDoc.close();

            setTimeout(function () {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }, 180);
        };

        function generateSingleLabel(data) {
            const payload = data?.data || data;
            if (!payload || !payload.code) return;

            let attempts = 0;
            const tryRender = () => {
                const qrEl = document.getElementById('qr-single');
                const bcEl = document.getElementById('barcode-single');

                if (window.QRCode && window.JsBarcode && qrEl && bcEl) {
                    qrEl.innerHTML = '';
                    new QRCode(qrEl, {
                        text: payload.scan_url || payload.code,
                        width: 58,
                        height: 58,
                        colorDark: '#0f172a',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.M,
                    });

                    try {
                        JsBarcode(bcEl, payload.code, {
                            format: 'CODE128',
                            width: 1.4,
                            height: 22,
                            displayValue: true,
                            fontSize: 8.5,
                            textMargin: 1,
                            margin: 1,
                            background: '#ffffff',
                            lineColor: '#0f172a',
                        });
                    } catch (e) {
                        console.warn('Barcode error single:', e);
                    }
                } else if (attempts < 20) {
                    attempts++;
                    setTimeout(tryRender, 100);
                }
            };

            setTimeout(tryRender, 50);
        }

        function generateBatchLabels(payload) {
            const list = payload?.items || payload;
            if (!Array.isArray(list)) return;

            let attempts = 0;
            const tryRender = () => {
                if (window.QRCode && window.JsBarcode) {
                    list.forEach(function (item, index) {
                        // 1. A4 Barcodes (now 80x50mm size!)
                        const qrA4 = document.getElementById('qr-batch-a4-' + index);
                        const bcA4 = document.getElementById('barcode-batch-a4-' + index);
                        if (qrA4) {
                            qrA4.innerHTML = '';
                            new QRCode(qrA4, {
                                text: item.scan_url || item.code,
                                width: 58,
                                height: 58,
                                colorDark: '#0f172a',
                                colorLight: '#ffffff',
                                correctLevel: QRCode.CorrectLevel.M,
                            });
                        }
                        if (bcA4) {
                            try {
                                JsBarcode(bcA4, item.code, {
                                    format: 'CODE128',
                                    width: 1.4,
                                    height: 22,
                                    displayValue: true,
                                    fontSize: 8.5,
                                    textMargin: 1,
                                    margin: 1,
                                    background: '#ffffff',
                                    lineColor: '#0f172a',
                                });
                            } catch (e) {}
                        }

                        // 2. Thermal Barcodes (also 80x50mm size!)
                        const qrTh = document.getElementById('qr-batch-th-' + index);
                        const bcTh = document.getElementById('barcode-batch-th-' + index);
                        if (qrTh) {
                            qrTh.innerHTML = '';
                            new QRCode(qrTh, {
                                text: item.scan_url || item.code,
                                width: 58,
                                height: 58,
                                colorDark: '#0f172a',
                                colorLight: '#ffffff',
                                correctLevel: QRCode.CorrectLevel.M,
                            });
                        }
                        if (bcTh) {
                            try {
                                JsBarcode(bcTh, item.code, {
                                    format: 'CODE128',
                                    width: 1.4,
                                    height: 22,
                                    displayValue: true,
                                    fontSize: 8.5,
                                    textMargin: 1,
                                    margin: 1,
                                    background: '#ffffff',
                                    lineColor: '#0f172a',
                                });
                            } catch (e) {}
                        }
                    });
                } else if (attempts < 20) {
                    attempts++;
                    setTimeout(tryRender, 100);
                }
            };

            setTimeout(tryRender, 50);
        }

        $wire.on('labelModalOpened', (event) => {
            generateSingleLabel(event);
        });

        $wire.on('batchLabelModalOpened', (event) => {
            generateBatchLabels(event);
        });
    </script>
    @endscript
</div>
