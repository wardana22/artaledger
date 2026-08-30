<div class="space-y-6">
    <!-- Sub Nav Tabs (Inline) -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-2 mb-3.5 overflow-x-auto">
        <button 
            type="button" 
            wire:click="$set('activeTab', 'general')" 
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $activeTab === 'general' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 ring-2 ring-indigo-400/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
            </svg>
            <span>Pengaturan Tampilan & Kartu KPI</span>
        </button>

        <button 
            type="button" 
            wire:click="$set('activeTab', 'account_groups')" 
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $activeTab === 'account_groups' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 ring-2 ring-indigo-400/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <span>Grup Akun COA Kustom</span>
        </button>
    </div>

    @if (session()->has('message'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-sm font-semibold animate-fade-in">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if ($activeTab === 'general')
    <!-- Header Banner -->
    <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Pengaturan Tampilan & KPI Dashboard</h2>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola Kartu KPI Finansial berbasis COA (CRUD) serta atur komponen widget yang muncul di dashboard eksekutif.</p>
        </div>

        <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700 w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            <span>Pratinjau Dashboard Utama</span>
        </a>
    </div>

    <!-- Section 1: CRUD Kartu KPI Finansial -->
    <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
            <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>💳 Kartu KPI Finansial Berbasis COA (CRUD)</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/50">{{ $kpis->count() }} Kartu</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Buat kartu KPI kustom yang menghitung saldo akun COA secara real-time untuk ditampilkan di bagian paling atas dashboard.</p>
            </div>

            <button type="button" wire:click="openCreateKpiModal" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-md shadow-indigo-500/20 w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Tambah Kartu KPI Baru</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($kpis as $kpi)
                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 space-y-3 relative group transition-all hover:border-indigo-500/30">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider
                            {{ $kpi->color_theme === 'emerald' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : '' }}
                            {{ $kpi->color_theme === 'rose' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' : '' }}
                            {{ $kpi->color_theme === 'indigo' ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20' : '' }}
                            {{ $kpi->color_theme === 'amber' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' : '' }}
                            {{ $kpi->color_theme === 'sky' ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20' : '' }}
                            {{ $kpi->color_theme === 'violet' ? 'bg-violet-500/10 text-violet-600 dark:text-violet-400 border border-violet-500/20' : '' }}
                        ">
                            Tema: {{ ucfirst($kpi->color_theme) }}
                        </span>

                        <div class="flex items-center gap-1.5">
                            <button type="button" wire:click="toggleKpiActive({{ $kpi->id }})" title="Aktif/Nonaktifkan Kartu" class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 hover:bg-slate-200/80 dark:hover:bg-slate-700/80 transition-all duration-200">
                                @if ($kpi->is_active)
                                    <span class="inline-block size-3 rounded-full bg-emerald-500 ring-2 ring-emerald-400/30" title="Aktif"></span>
                                @else
                                    <span class="inline-block size-3 rounded-full bg-slate-400 ring-2 ring-slate-400/30" title="Nonaktif"></span>
                                @endif
                            </button>
                            <button type="button" wire:click="openEditKpiModal({{ $kpi->id }})" class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-indigo-500/10 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-500/30 shadow-2xs transition-all duration-200" title="Edit Kartu KPI">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button type="button" wire:click="deleteKpi({{ $kpi->id }})" wire:confirm="Apakah Anda yakin ingin menghapus kartu KPI ini?" class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-rose-500/10 hover:text-rose-600 dark:hover:text-rose-400 hover:border-rose-500/30 shadow-2xs transition-all duration-200" title="Hapus Kartu KPI">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ $kpi->title }}</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            @if ($kpi->source_type === 'account' && $kpi->account)
                                Akun Spesifik: <strong class="text-slate-700 dark:text-slate-300">{{ $kpi->account->code }} - {{ $kpi->account->name }}</strong>
                            @else
                                Kategori Tipe: <strong class="text-slate-700 dark:text-slate-300 uppercase">{{ $kpi->account_type }}</strong>
                            @endif
                        </p>
                    </div>

                    <div class="pt-2 border-t border-slate-200/60 dark:border-slate-800/60 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                        <span>Kalkulasi: {{ str_replace('_', ' ', ucfirst($kpi->calculation_type)) }}</span>
                        <span>Urutan: #{{ $kpi->order_index }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 text-center bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-dashed border-slate-300 dark:border-slate-700">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold">Belum ada Kartu KPI Kustom. Klik tombol "Tambah Kartu KPI Baru" untuk membuatnya.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Section 2: Sakelar Toggle Komponen Dashboard -->
    <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <div class="pb-4 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">⚙️ Sakelar Komponen & Widget Dashboard</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Aktifkan atau matikan bagian komponen dashboard yang ingin ditampilkan kepada pengguna.</p>
        </div>

        <form wire:submit.prevent="saveSettings" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Toggle 1: Kartu KPI -->
                <div class="flex items-start justify-between p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                    <div>
                        <label for="toggle_kpi" class="text-sm font-bold text-slate-900 dark:text-white cursor-pointer">Kartu KPI Finansial</label>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tampilkan deretan kartu ringkasan KPI di bagian paling atas.</p>
                    </div>
                    <input type="checkbox" id="toggle_kpi" wire:model="show_kpi_cards" class="size-5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-700 dark:bg-slate-800 cursor-pointer" />
                </div>

                <!-- Toggle 2: Grafik Performa -->
                <div class="flex items-start justify-between p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                    <div>
                        <label for="toggle_chart" class="text-sm font-bold text-slate-900 dark:text-white cursor-pointer">Grafik Performa Pendapatan vs Beban</label>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tampilkan grafik visual perbandingan pendapatan & beban bulanan.</p>
                    </div>
                    <input type="checkbox" id="toggle_chart" wire:model="show_revenue_expense_chart" class="size-5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-700 dark:bg-slate-800 cursor-pointer" />
                </div>

                <!-- Toggle 3: Jurnal Terbaru -->
                <div class="flex items-start justify-between p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                    <div>
                        <label for="toggle_journals" class="text-sm font-bold text-slate-900 dark:text-white cursor-pointer">Tabel Jurnal Transaksi Terbaru</label>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tampilkan tabel transaksi jurnal terposting/draft terbaru.</p>
                    </div>
                    <input type="checkbox" id="toggle_journals" wire:model="show_recent_journals" class="size-5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-700 dark:bg-slate-800 cursor-pointer" />
                </div>

                <!-- Toggle 4: Akses Cepat -->
                <div class="flex items-start justify-between p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                    <div>
                        <label for="toggle_quick_actions" class="text-sm font-bold text-slate-900 dark:text-white cursor-pointer">Tombol Pintasan Akses Cepat</label>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tampilkan tombol pintasan cepat ke fungsi operasional utama.</p>
                    </div>
                    <input type="checkbox" id="toggle_quick_actions" wire:model="show_quick_actions" class="size-5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-700 dark:bg-slate-800 cursor-pointer" />
                </div>

                <!-- Toggle 5: Status Periode -->
                <div class="flex items-start justify-between p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                    <div>
                        <label for="toggle_period" class="text-sm font-bold text-slate-900 dark:text-white cursor-pointer">Kartu Status Periode Akuntansi</label>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tampilkan widget status periode aktif dan informasi lock key.</p>
                    </div>
                    <input type="checkbox" id="toggle_period" wire:model="show_period_status" class="size-5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-700 dark:bg-slate-800 cursor-pointer" />
                </div>

                <!-- Toggle 6: Saldo Kas & Bank -->
                <div class="flex items-start justify-between p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                    <div>
                        <label for="toggle_cash_bank" class="text-sm font-bold text-slate-900 dark:text-white cursor-pointer">Rincian Saldo Kas & Bank</label>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tampilkan rincian daftar akun kas & kesetaraan kas.</p>
                    </div>
                    <input type="checkbox" id="toggle_cash_bank" wire:model="show_cash_bank_summary" class="size-5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-700 dark:bg-slate-800 cursor-pointer" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-800">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold transition-all shadow-lg shadow-indigo-500/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Simpan Pengaturan Dashboard</span>
                </button>
            </div>
        </form>
    </div>
    @else
        <!-- TAB 2: MANAJEMEN GRUP AKUN COA KUSTOM -->
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Grup Akun COA Kustom</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Kelola pengelompokan akun COA dinamis sebagai variabel rumus matematika KPI pada dashboard.</p>
                    </div>
                </div>
            </div>

            <button type="button" wire:click="openCreateGroupModal" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-md shadow-indigo-500/20 w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Tambah Grup Akun Baru</span>
            </button>
        </div>

        <!-- Group List Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($groups as $group)
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 relative group hover:border-indigo-500/30 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-extrabold tracking-wider font-mono
                            {{ $group->color_theme === 'emerald' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : '' }}
                            {{ $group->color_theme === 'rose' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' : '' }}
                            {{ $group->color_theme === 'indigo' ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20' : '' }}
                            {{ $group->color_theme === 'amber' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' : '' }}
                            {{ $group->color_theme === 'sky' ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20' : '' }}
                            {{ $group->color_theme === 'violet' ? 'bg-violet-500/10 text-violet-600 dark:text-violet-400 border border-violet-500/20' : '' }}
                        ">
                            [{{ $group->code }}]
                        </span>

                        <div class="flex items-center gap-1.5">
                            <button type="button" wire:click="openEditGroupModal({{ $group->id }})" class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-indigo-500/10 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-500/30 shadow-2xs transition-all duration-200" title="Edit Grup Akun">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button type="button" wire:click="deleteGroup({{ $group->id }})" wire:confirm="Apakah Anda yakin ingin menghapus grup akun '{{ $group->name }}' ini?" class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-rose-500/10 hover:text-rose-600 dark:hover:text-rose-400 hover:border-rose-500/30 shadow-2xs transition-all duration-200" title="Hapus Grup Akun Ini">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-base font-bold text-slate-900 dark:text-white">{{ $group->name }}</h4>
                        @if ($group->description)
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">{{ $group->description }}</p>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2 text-xs">
                        <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Anggota Grup:</span>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($group->members as $m)
                                @if ($m->account_prefix)
                                    <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium text-[11px]">
                                        Awalan Kode: <strong class="text-indigo-600 dark:text-indigo-400">{{ $m->account_prefix }}*</strong>
                                    </span>
                                @elseif ($m->account_type)
                                    <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium text-[11px]">
                                        Tipe: <strong class="text-indigo-600 dark:text-indigo-400 uppercase">{{ $m->account_type }}</strong>
                                    </span>
                                @elseif ($m->account)
                                    <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium text-[11px]">
                                        {{ $m->account->code }} - {{ $m->account->name }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 text-center bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold">Belum ada Grup Akun COA Kustom. Klik tombol "Tambah Grup Akun Baru" untuk membuatnya.</p>
                </div>
            @endforelse
        </div>
    @endif

    <!-- Modal CRUD Kartu KPI -->
    @if ($showKpiModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in">
            <div class="w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                        {{ $editingKpiId ? 'Edit Kartu KPI Finansial' : 'Tambah Kartu KPI Finansial Baru' }}
                    </h3>
                    <button type="button" wire:click="$set('showKpiModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveKpi" class="p-6 space-y-5">
                    <div>
                        <label for="kpi_title" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Judul Kartu KPI *</label>
                        <input type="text" id="kpi_title" wire:model="kpi_title" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-semibold focus:ring-2 focus:ring-indigo-500 transition-all" placeholder="Contoh: Saldo Kas Utama" />
                        @error('kpi_title') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="kpi_source_type" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Sumber Akun / Formula *</label>
                            <select id="kpi_source_type" wire:model.live="kpi_source_type" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                                <option value="account">Spesifik Akun COA</option>
                                <option value="account_group">Grup Akun COA Kustom</option>
                                <option value="account_type">Kategori Tipe Akun</option>
                                <option value="formula">Formula Matematika (+ - * /)</option>
                            </select>
                        </div>

                        @if ($kpi_source_type === 'account')
                            <div>
                                <label for="kpi_account_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Pilih Akun COA *</label>
                                <select id="kpi_account_id" wire:model="kpi_account_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-indigo-500 transition-all">
                                    <option value="">-- Pilih Akun --</option>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                                @error('kpi_account_id') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @elseif ($kpi_source_type === 'account_group')
                            <div>
                                <label for="kpi_account_group_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Pilih Grup Akun COA *</label>
                                <select id="kpi_account_group_id" wire:model="kpi_account_group_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-indigo-500 transition-all">
                                    <option value="">-- Pilih Grup Akun --</option>
                                    @foreach ($accountGroups as $grp)
                                        <option value="{{ $grp->id }}">[{{ $grp->code }}] {{ $grp->name }}</option>
                                    @endforeach
                                </select>
                                @error('kpi_account_group_id') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @elseif ($kpi_source_type === 'account_type')
                            <div>
                                <label for="kpi_account_type" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Tipe Akun *</label>
                                <select id="kpi_account_type" wire:model="kpi_account_type" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-indigo-500 transition-all">
                                    <option value="asset">Aset / Aktiva (Asset)</option>
                                    <option value="revenue">Pendapatan (Revenue)</option>
                                    <option value="expense">Beban (Expense)</option>
                                    <option value="liability">Kewajiban (Liability)</option>
                                    <option value="equity">Ekuitas (Equity)</option>
                                </select>
                            </div>
                        @else
                            <div class="col-span-2 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label for="kpi_formula_expression" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Ekspresi Rumus Formula *</label>
                                    <button type="button" wire:click="$set('kpi_formula_expression', '')" class="text-[11px] font-bold text-rose-500 hover:underline">Clear / Reset</button>
                                </div>
                                <input type="text" id="kpi_formula_expression" wire:model="kpi_formula_expression" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold font-mono focus:ring-2 focus:ring-indigo-500 transition-all" placeholder="Contoh: ([COGS] / [REVENUE]) * 100" />
                                @error('kpi_formula_expression') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror

                                <!-- Interactive Chips Helper -->
                                <div class="p-3.5 rounded-xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-200/60 dark:border-indigo-800/60 space-y-2">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-700 dark:text-indigo-300 block">💡 Klik Tombol Variabel atau Operator untuk Menyisipkan ke Rumus:</span>
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @foreach ($accountGroups as $grp)
                                            <button type="button" wire:click="appendFormulaToken('[{{ $grp->code }}]')" class="px-2.5 py-1 rounded-lg text-[11px] font-bold font-mono bg-white dark:bg-slate-800 hover:bg-indigo-600 hover:text-white text-indigo-600 dark:text-indigo-400 border border-indigo-300 dark:border-indigo-700 transition-all shadow-sm">
                                                + [{{ $grp->code }}]
                                            </button>
                                        @endforeach

                                        <span class="text-slate-300 dark:text-slate-700 mx-1">|</span>

                                        @foreach (['+', '-', '*', '/', '(', ')', '100', '365'] as $op)
                                            <button type="button" wire:click="appendFormulaToken('{{ $op }}')" class="px-2.5 py-1 rounded-lg text-xs font-bold font-mono bg-white dark:bg-slate-800 hover:bg-slate-700 hover:text-white text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700 transition-all shadow-sm">
                                                {{ $op }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Display Format Settings -->
                    <div class="grid grid-cols-2 gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/60">
                        <div>
                            <label for="kpi_display_format" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Format Tampilan Result *</label>
                            <select id="kpi_display_format" wire:model="kpi_display_format" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                                <option value="currency">Mata Uang (Rp)</option>
                                <option value="percentage">Persentase (%)</option>
                                <option value="days">Jumlah Hari (Hari)</option>
                                <option value="number">Angka Biasa / Desimal</option>
                                <option value="times">Frekuensi / Perputaran (x)</option>
                            </select>
                        </div>

                        <div>
                            <label for="kpi_decimal_places" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Jumlah Desimal *</label>
                            <select id="kpi_decimal_places" wire:model="kpi_decimal_places" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                                <option value="0">0 Desimal (Tanpa Komma)</option>
                                <option value="1">1 Desimal (0,0)</option>
                                <option value="2">2 Desimal (0,00)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="kpi_calculation_type" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Metode Kalkulasi *</label>
                            <select id="kpi_calculation_type" wire:model="kpi_calculation_type" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-indigo-500 transition-all">
                                <option value="ending_balance">Saldo Akhir Berjalan</option>
                                <option value="period_mutation">Mutasi Periode</option>
                                <option value="debit_sum">Total Debit</option>
                                <option value="credit_sum">Total Kredit</option>
                            </select>
                        </div>

                        <div>
                            <label for="kpi_color_theme" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Tema Warna *</label>
                            <select id="kpi_color_theme" wire:model="kpi_color_theme" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-indigo-500 transition-all">
                                <option value="emerald">Emerald Green (Hijau)</option>
                                <option value="indigo">Indigo Blue (Biru Indigo)</option>
                                <option value="rose">Rose Red (Merah Rose)</option>
                                <option value="amber">Amber Gold (Emas/Kuning)</option>
                                <option value="sky">Sky Cyan (Biru Langit)</option>
                                <option value="violet">Violet Purple (Ungu)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="kpi_order_index" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Urutan Tampilan *</label>
                            <input type="number" id="kpi_order_index" wire:model="kpi_order_index" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-semibold focus:ring-2 focus:ring-indigo-500 transition-all" />
                        </div>

                        <div class="flex items-center pt-6">
                            <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-900 dark:text-white cursor-pointer">
                                <input type="checkbox" wire:model="kpi_is_active" class="size-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-700 dark:bg-slate-800" />
                                <span>Aktifkan Kartu Ini</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" wire:click="$set('showKpiModal', false)" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-all">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-md shadow-indigo-500/20">
                            Simpan Kartu KPI
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal CRUD Grup Akun COA -->
    @if ($showGroupModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in">
            <div class="w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                        {{ $editingGroupId ? 'Edit Grup Akun COA' : 'Tambah Grup Akun COA Baru' }}
                    </h3>
                    <button type="button" wire:click="$set('showGroupModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveGroup" class="p-6 space-y-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="group_code" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Kode Grup (Variabel) *</label>
                            <input type="text" id="group_code" wire:model="group_code" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold font-mono uppercase focus:ring-2 focus:ring-indigo-500 transition-all" placeholder="Contoh: COGS" />
                            <p class="text-[10px] text-slate-400 mt-1">Digunakan sebagai variabel formula: [COGS]</p>
                            @error('group_code') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="group_color_theme" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Tema Warna *</label>
                            <select id="group_color_theme" wire:model="group_color_theme" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-indigo-500 transition-all">
                                <option value="indigo">Indigo Blue (Biru)</option>
                                <option value="emerald">Emerald Green (Hijau)</option>
                                <option value="rose">Rose Red (Merah)</option>
                                <option value="amber">Amber Gold (Kuning)</option>
                                <option value="sky">Sky Cyan (Biru Langit)</option>
                                <option value="violet">Violet Purple (Ungu)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="group_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Nama Grup Akun *</label>
                        <input type="text" id="group_name" wire:model="group_name" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-semibold focus:ring-2 focus:ring-indigo-500 transition-all" placeholder="Contoh: Beban Pokok Pendapatan (COGS)" />
                        @error('group_name') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="group_description" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Keterangan / Catatan</label>
                        <textarea id="group_description" wire:model="group_description" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-indigo-500 transition-all" placeholder="Catatan opsional mengenai isi grup akun ini..."></textarea>
                    </div>

                    <hr class="border-slate-200 dark:border-slate-800" />

                    <!-- Penentuan Anggota Grup -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Metode Penentuan Anggota Grup *</label>
                        <select wire:model.live="group_member_mode" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                            <option value="prefix">Berdasarkan Awalan Kode COA (Contoh: Kepala 4 atau 5)</option>
                            <option value="type">Berdasarkan Tipe Kategori Akun (Contoh: PENDAPATAN, BEBAN)</option>
                            <option value="specific">Pilih Akun Spesifik Manual</option>
                        </select>
                    </div>

                    @if ($group_member_mode === 'prefix')
                        <div>
                            <label for="group_account_prefix" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Awalan Kode Akun (Prefix) *</label>
                            <select id="group_account_prefix" wire:model="group_account_prefix" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                                <option value="4">Kepala 4 (Seluruh Akun Pendapatan)</option>
                                <option value="5">Kepala 5 (Seluruh Akun Beban Pokok / HPP)</option>
                                <option value="6">Kepala 6 (Seluruh Akun Beban Operasional)</option>
                                <option value="7">Kepala 7 (Seluruh Akun Beban Umum)</option>
                                <option value="8">Kepala 8 (Seluruh Akun Pendapatan Lain-lain)</option>
                                <option value="9">Kepala 9 (Seluruh Akun Beban Lain-lain)</option>
                                <option value="1">Kepala 1 (Seluruh Akun Aktiva / Aset)</option>
                                <option value="11">Kepala 11 (Akun Kas & Kesetaraan Kas)</option>
                                <option value="2">Kepala 2 (Seluruh Akun Kewajiban / Hutang)</option>
                                <option value="3">Kepala 3 (Seluruh Akun Ekuitas / Modal)</option>
                            </select>
                        </div>
                    @elseif ($group_member_mode === 'type')
                        <div>
                            <label for="group_account_type" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Pilih Tipe Kategori Akun *</label>
                            <select id="group_account_type" wire:model="group_account_type" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-indigo-500 transition-all">
                                <option value="PENDAPATAN">PENDAPATAN</option>
                                <option value="BEBAN">BEBAN</option>
                                <option value="BEBAN LAIN-LAIN">BEBAN LAIN-LAIN</option>
                                <option value="KAS">KAS</option>
                                <option value="BANK">BANK</option>
                                <option value="PIUTANG">PIUTANG</option>
                                <option value="AKTIVA TETAP">AKTIVA TETAP</option>
                                <option value="HUTANG LANCAR">HUTANG LANCAR</option>
                                <option value="MODAL">MODAL</option>
                            </select>
                        </div>
                    @else
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Pilih Akun Spesifik (Multi-Select)</label>
                            <div class="max-h-48 overflow-y-auto p-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 space-y-2">
                                @foreach ($accounts as $acc)
                                    <label class="flex items-center gap-2 text-xs font-medium text-slate-800 dark:text-slate-200 cursor-pointer">
                                        <input type="checkbox" value="{{ $acc->id }}" wire:model="group_selected_account_ids" class="size-4 rounded text-indigo-600 border-slate-300 dark:border-slate-700" />
                                        <span>{{ $acc->code }} - {{ $acc->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center justify-between gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <div>
                            @if ($editingGroupId)
                                <button type="button" wire:click="deleteGroup({{ $editingGroupId }})" wire:confirm="Apakah Anda yakin ingin menghapus grup akun ini?" class="px-3.5 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500 text-rose-600 hover:text-white dark:text-rose-400 text-xs font-bold transition-all border border-rose-500/20 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    <span>Hapus Grup Akun</span>
                                </button>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="$set('showGroupModal', false)" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-all">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-md shadow-indigo-500/20">
                                Simpan Grup Akun
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
