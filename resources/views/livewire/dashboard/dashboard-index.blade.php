<div class="space-y-6">
    <!-- Header Controls -->
    <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="p-2.5 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-black tracking-tight text-slate-900 dark:text-white">Dashboard Finansial Eksekutif</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Monitoring kinerja keuangan, rasio kesehatan finansial, dan tren operasional secara real-time.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar: 3 Kolom (Unit, Dari Tanggal, Sampai Tanggal) -->
    <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- 1. Unit -->
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Unit Perusahaan</label>
                <select wire:model.live="selectedUnitId" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                    @if (auth()->user()?->hasGlobalUnitAccess())
                        <option value="">🌐 Konsolidasi (Semua Unit)</option>
                    @endif
                    @foreach ($units as $u)
                        <option value="{{ $u->id }}">{{ $u->code }} - {{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 2. Dari Tanggal -->
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Dari Tanggal</label>
                <input wire:model.live="startDate" type="date" aria-label="Dari Tanggal" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:ring-2 focus:ring-indigo-500 transition-all" />
            </div>

            <!-- 3. Sampai Tanggal -->
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Sampai Tanggal</label>
                <input wire:model.live="endDate" type="date" aria-label="Sampai Tanggal" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:ring-2 focus:ring-indigo-500 transition-all" />
            </div>
        </div>
    </div>

    <!-- Section 1: 12 Kartu Metrik KPI (Urutan Pendapatan sampai DSI) -->
    @if ($setting->show_kpi_cards && !empty($kpiCards))
        <div>
            <div class="flex items-center justify-between mb-3 px-1">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    <span class="size-2 rounded-full bg-indigo-500"></span>
                    12 Indikator Finansial & Operasional Utama (Periode {{ $startDate }} s/d {{ $endDate }})
                </h3>
                <span class="text-[11px] font-semibold text-slate-400">Terurut baku dari Pendapatan s/d DSI</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($kpiCards as $card)
                    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 transition-all">
                        <!-- Top Accent Line -->
                        <div class="absolute top-0 left-0 right-0 h-1
                            {{ $card['color_theme'] === 'emerald' ? 'bg-emerald-500' : '' }}
                            {{ $card['color_theme'] === 'cyan' ? 'bg-cyan-500' : '' }}
                            {{ $card['color_theme'] === 'amber' ? 'bg-amber-500' : '' }}
                            {{ $card['color_theme'] === 'indigo' ? 'bg-indigo-500' : '' }}
                            {{ $card['color_theme'] === 'purple' ? 'bg-purple-500' : '' }}
                            {{ $card['color_theme'] === 'orange' ? 'bg-orange-500' : '' }}
                            {{ $card['color_theme'] === 'teal' ? 'bg-teal-500' : '' }}
                            {{ $card['color_theme'] === 'rose' ? 'bg-rose-500' : '' }}
                            {{ $card['color_theme'] === 'blue' ? 'bg-blue-500' : '' }}
                            {{ $card['color_theme'] === 'sky' ? 'bg-sky-500' : '' }}
                            {{ $card['color_theme'] === 'yellow' ? 'bg-yellow-500' : '' }}
                        "></div>

                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide truncate" title="{{ $card['title'] }}">
                                {{ $card['title'] }}
                            </span>
                            <div class="p-1.5 rounded-lg shrink-0
                                {{ $card['color_theme'] === 'emerald' ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400' : '' }}
                                {{ $card['color_theme'] === 'cyan' ? 'bg-cyan-50 dark:bg-cyan-950/50 text-cyan-600 dark:text-cyan-400' : '' }}
                                {{ $card['color_theme'] === 'amber' ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400' : '' }}
                                {{ $card['color_theme'] === 'indigo' ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400' : '' }}
                                {{ $card['color_theme'] === 'purple' ? 'bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400' : '' }}
                                {{ $card['color_theme'] === 'orange' ? 'bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400' : '' }}
                                {{ $card['color_theme'] === 'teal' ? 'bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400' : '' }}
                                {{ $card['color_theme'] === 'rose' ? 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400' : '' }}
                                {{ $card['color_theme'] === 'blue' ? 'bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400' : '' }}
                                {{ $card['color_theme'] === 'sky' ? 'bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400' : '' }}
                                {{ $card['color_theme'] === 'yellow' ? 'bg-yellow-50 dark:bg-yellow-950/50 text-yellow-600 dark:text-yellow-400' : '' }}
                            ">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="text-xl font-black text-slate-900 dark:text-white tracking-tight truncate" title="{{ $card['formatted_value'] }}">
                                {{ $card['formatted_value'] }}
                            </div>
                            <div class="flex items-center justify-between mt-1 text-[10px] text-slate-400 dark:text-slate-500 font-semibold">
                                <span class="capitalize">{{ $card['display_format'] }}</span>
                                <span>Periode Aktif</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Section 2: 3 Kartu Rasio Finansial Eksekutif (CR, NPM, DER) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- 1. Current Ratio -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border-l-4 border-l-sky-500 border-y border-r border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Current Ratio (CR)</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20">Likuiditas</span>
            </div>
            <div class="mt-2 text-2xl font-black text-sky-600 dark:text-sky-400">
                {{ number_format($financialRatios['current_ratio'], 2, ',', '.') }}x
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                Kemampuan Aset Lancar (Rp {{ number_format($financialRatios['current_assets'], 0, ',', '.') }}) menutup Kewajiban Lancar (Rp {{ number_format($financialRatios['current_liabilities'], 0, ',', '.') }}).
            </p>
        </div>

        <!-- 2. Net Profit Margin -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border-l-4 border-l-emerald-500 border-y border-r border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Net Profit Margin (NPM)</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">Profitabilitas</span>
            </div>
            <div class="mt-2 text-2xl font-black text-emerald-600 dark:text-emerald-400">
                {{ number_format($financialRatios['net_profit_margin'], 2, ',', '.') }}%
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                Persentase laba bersih yang dihasilkan dari setiap rupiah pendapatan operasional pada periode ini.
            </p>
        </div>

        <!-- 3. Debt to Equity Ratio -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border-l-4 border-l-amber-500 border-y border-r border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Debt to Equity (DER)</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">Solvabilitas</span>
            </div>
            <div class="mt-2 text-2xl font-black text-amber-600 dark:text-amber-400">
                {{ number_format($financialRatios['debt_to_equity'], 2, ',', '.') }}%
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                Rasio total kewajiban (Rp {{ number_format($financialRatios['total_liabilities'], 0, ',', '.') }}) terhadap total modal ekuitas pemilik.
            </p>
        </div>
    </div>

    <!-- Section 3: Grafik Multi-Series ApexCharts -->
    @if ($charts->isNotEmpty())
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            @foreach ($charts as $chart)
                <div wire:key="chart-card-{{ $chart->id }}" class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 {{ $chart->width === 'full' ? 'lg:col-span-2' : '' }}" wire:ignore>
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ $chart->name }}</h4>
                                <p class="text-[11px] text-slate-400">Tren multi-series bulanan tahun {{ date('Y', strtotime($startDate)) }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                            Tipe: {{ $chart->type }}
                        </span>
                    </div>

                    <div id="apex-chart-{{ $chart->id }}" class="min-h-[320px] w-full"></div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Section 4: Top 10 High-Value Transactions & Status Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Top 10 High-Value Transactions -->
        <div class="lg:col-span-2 p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>💎</span> 10 Transaksi Bernilai Terbesar (High-Value Transactions)
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Transaksi buku besar dengan nilai mutasi nominal debit tertinggi pada rentang periode ini.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/50">
                    Audit Materialitas
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-2.5 px-3">Tanggal</th>
                            <th class="py-2.5 px-3">No. Jurnal</th>
                            <th class="py-2.5 px-3">Unit</th>
                            <th class="py-2.5 px-3">Keterangan</th>
                            <th class="py-2.5 px-3 text-right">Total Debit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        @forelse ($topTransactions as $tx)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-all">
                                <td class="py-2.5 px-3 text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ $tx->entry_date->format('d/m/Y') }}</td>
                                <td class="py-2.5 px-3 font-bold font-mono text-indigo-600 dark:text-indigo-400 whitespace-nowrap">{{ $tx->entry_number }}</td>
                                <td class="py-2.5 px-3 text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $tx->lines->first()?->unit?->code ?? 'Pusat' }}</td>
                                <td class="py-2.5 px-3 text-slate-900 dark:text-white max-w-xs truncate" title="{{ $tx->description }}">{{ $tx->description }}</td>
                                <td class="py-2.5 px-3 text-right font-black text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                    Rp {{ number_format($tx->total_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400 text-xs">Tidak ada transaksi tercatat pada rentang periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sidebar Kanan: Periode & Pintasan -->
        <div class="space-y-6">
            <!-- Widget: Status Periode Akuntansi -->
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">📅 Periode Akuntansi Aktif</h4>
                    <a href="{{ route('accounting.periods.index') }}" wire:navigate class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Kelola &rarr;</a>
                </div>

                @if ($activePeriod)
                    <div class="p-3.5 rounded-xl bg-indigo-50/50 dark:bg-slate-800/80 border border-indigo-200/60 dark:border-slate-700/80 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-black text-indigo-950 dark:text-slate-100">{{ date('F Y', mktime(0,0,0, $activePeriod->month, 1, $activePeriod->year)) }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">TERBUKA</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Rentang filter: {{ $startDate }} s/d {{ $endDate }}</p>
                    </div>
                @else
                    <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold">Tidak ada periode terbuka pada bulan yang dipilih.</p>
                @endif
            </div>

            <!-- Widget: Pintasan Cepat -->
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">⚡ Pintasan Akses Cepat</h4>
                <div class="grid grid-cols-2 gap-2.5">
                    @can('journals.create')
                        <a href="{{ route('accounting.journals.create') }}" wire:navigate class="flex flex-col items-center justify-center p-3 rounded-xl bg-indigo-50 dark:bg-slate-800/80 hover:bg-indigo-100 dark:hover:bg-slate-700/80 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-slate-700/80 transition-all text-center">
                            <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span class="text-[11px] font-bold">Buat Jurnal</span>
                        </a>
                    @endcan

                    @can('accounts.view')
                        <a href="{{ route('accounting.accounts.index') }}" wire:navigate class="flex flex-col items-center justify-center p-3 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-slate-200 dark:hover:bg-slate-700/80 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700/80 transition-all text-center">
                            <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M3 7l9 6 9-6M3 7l9-6 9 6"></path>
                            </svg>
                            <span class="text-[11px] font-bold">Master COA</span>
                        </a>
                    @endcan

                    @can('reports.profit_loss')
                        <a href="{{ route('accounting.reports.profit-loss') }}" wire:navigate class="flex flex-col items-center justify-center p-3 rounded-xl bg-emerald-50 dark:bg-slate-800/80 hover:bg-emerald-100 dark:hover:bg-slate-700/80 text-emerald-600 dark:text-emerald-400 border border-emerald-200/50 dark:border-slate-700/80 transition-all text-center">
                            <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <span class="text-[11px] font-bold">Laba Rugi</span>
                        </a>
                    @endcan

                    @can('reports.cash_flow')
                        <a href="{{ route('accounting.reports.cash-flow') }}" wire:navigate class="flex flex-col items-center justify-center p-3 rounded-xl bg-cyan-50 dark:bg-slate-800/80 hover:bg-cyan-100 dark:hover:bg-slate-700/80 text-cyan-600 dark:text-cyan-400 border border-cyan-200/50 dark:border-slate-700/80 transition-all text-center">
                            <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-[11px] font-bold">Arus Kas</span>
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Script ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('livewire:navigated', initDashboardCharts);
        document.addEventListener('DOMContentLoaded', initDashboardCharts);

        let renderedCharts = {};

        function initDashboardCharts() {
            const chartDataRaw = @json($chartData);
            const chartsConfig = @json($charts);
            const isDark = document.documentElement.classList.contains('dark');

            chartsConfig.forEach(cfg => {
                const containerId = 'apex-chart-' + cfg.id;
                const containerEl = document.getElementById(containerId);
                if (!containerEl) return;

                if (renderedCharts[cfg.id]) {
                    renderedCharts[cfg.id].destroy();
                }

                const cData = chartDataRaw[cfg.id] || { categories: [], series: [] };

                const options = {
                    series: cData.series || [],
                    chart: {
                        type: cfg.type || 'area',
                        height: 320,
                        toolbar: { show: false },
                        background: 'transparent',
                        fontFamily: 'Inter, system-ui, sans-serif',
                        foreColor: isDark ? '#a1a1aa' : '#64748b'
                    },
                    colors: (cData.series || []).map(s => s.color || '#6366f1'),
                    dataLabels: { enabled: false },
                    stroke: {
                        curve: 'smooth',
                        width: 2.5
                    },
                    grid: {
                        borderColor: isDark ? '#27272a' : '#f1f5f9',
                        strokeDashArray: 3
                    },
                    xaxis: {
                        categories: cData.categories || [],
                        labels: {
                            style: { colors: isDark ? '#a1a1aa' : '#64748b', fontSize: '11px', fontWeight: 600 }
                        },
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: {
                        labels: {
                            style: { colors: isDark ? '#a1a1aa' : '#64748b', fontSize: '11px' },
                            formatter: function (val) {
                                if (Math.abs(val) >= 1e9) return (val / 1e9).toFixed(1) + 'M';
                                if (Math.abs(val) >= 1e6) return (val / 1e6).toFixed(1) + 'Jt';
                                if (Math.abs(val) >= 1e3) return (val / 1e3).toFixed(0) + 'Rb';
                                return val;
                            }
                        }
                    },
                    tooltip: {
                        theme: isDark ? 'dark' : 'light',
                        y: {
                            formatter: function (val) {
                                return new Intl.NumberFormat('id-ID').format(val);
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        labels: { colors: isDark ? '#d4d4d8' : '#334155' }
                    }
                };

                const chartInstance = new ApexCharts(containerEl, options);
                chartInstance.render();
                renderedCharts[cfg.id] = chartInstance;
            });
        }
    </script>
</div>
