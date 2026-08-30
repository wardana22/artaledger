<div class="space-y-6">
    <!-- Header Controls -->
    <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Dashboard Finansial Eksekutif</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Ringkasan performa keuangan, saldo kas/bank, dan transaksi jurnal terbaru.</p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Filter Unit -->
            <select wire:model.live="selectedUnitId" class="px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                @if (auth()->user()?->hasGlobalUnitAccess())
                    <option value="">🌐 Konsolidasi (Semua Unit)</option>
                @endif
                @foreach ($units as $u)
                    <option value="{{ $u->id }}">{{ $u->code }} - {{ $u->name }}</option>
                @endforeach
            </select>

            <!-- Filter Bulan -->
            <select wire:model.live="selectedMonth" class="px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                <option value="0">🗓️ Semua Bulan</option>
                <option value="1">Januari</option>
                <option value="2">Februari</option>
                <option value="3">Maret</option>
                <option value="4">April</option>
                <option value="5">Mei</option>
                <option value="6">Juni</option>
                <option value="7">Juli</option>
                <option value="8">Agustus</option>
                <option value="9">September</option>
                <option value="10">Oktober</option>
                <option value="11">November</option>
                <option value="12">Desember</option>
            </select>

            <!-- Filter Tahun -->
            <select wire:model.live="selectedYear" class="px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                @for ($y = date('Y'); $y >= date('Y') - 4; $y--)
                    <option value="{{ $y }}">Tahun {{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    <!-- Section 1: Dynamic Kartu KPI Finansial (CRUDable) -->
    @if ($setting->show_kpi_cards && !empty($kpiCards))
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($kpiCards as $card)
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                    <!-- Color Accent Line -->
                    <div class="absolute top-0 left-0 right-0 h-1.5
                        {{ $card['color_theme'] === 'emerald' ? 'bg-gradient-to-r from-emerald-500 to-teal-400' : '' }}
                        {{ $card['color_theme'] === 'indigo' ? 'bg-gradient-to-r from-indigo-500 to-sky-400' : '' }}
                        {{ $card['color_theme'] === 'rose' ? 'bg-gradient-to-r from-rose-500 to-pink-400' : '' }}
                        {{ $card['color_theme'] === 'amber' ? 'bg-gradient-to-r from-amber-500 to-orange-400' : '' }}
                        {{ $card['color_theme'] === 'sky' ? 'bg-gradient-to-r from-sky-500 to-blue-400' : '' }}
                        {{ $card['color_theme'] === 'violet' ? 'bg-gradient-to-r from-violet-500 to-purple-400' : '' }}
                    "></div>

                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ $card['title'] }}</span>
                        <div class="p-2 rounded-xl
                            {{ $card['color_theme'] === 'emerald' ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400' : '' }}
                            {{ $card['color_theme'] === 'indigo' ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400' : '' }}
                            {{ $card['color_theme'] === 'rose' ? 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400' : '' }}
                            {{ $card['color_theme'] === 'amber' ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400' : '' }}
                            {{ $card['color_theme'] === 'sky' ? 'bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400' : '' }}
                            {{ $card['color_theme'] === 'violet' ? 'bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400' : '' }}
                        ">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                            Rp {{ number_format($card['value'], 0, ',', '.') }}
                        </h3>
                        <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 mt-1">
                            Kalkulasi: {{ str_replace('_', ' ', ucfirst($card['calculation_type'])) }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Section 2: Grafik Performa & Widget Kanan -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Main Chart / Table -->
        @if ($setting->show_revenue_expense_chart)
            <div class="lg:col-span-2 p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">📊 Performa Pendapatan vs Beban (Tahun {{ $selectedYear }})</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Perbandingan mutasi pendapatan usaha dan beban operasional per bulan.</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-bold">
                        <span class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                            <span class="size-2.5 rounded-full bg-emerald-500"></span> Pendapatan
                        </span>
                        <span class="flex items-center gap-1.5 text-rose-600 dark:text-rose-400">
                            <span class="size-2.5 rounded-full bg-rose-500"></span> Beban
                        </span>
                    </div>
                </div>

                <!-- Monthly Bars Visualization -->
                <div class="space-y-3 pt-2">
                    @foreach ($chartMonths as $cm)
                        @php
                            $maxVal = max(1, max(array_column($chartMonths, 'revenue')), max(array_column($chartMonths, 'expense')));
                            $revPct = min(100, round(($cm['revenue'] / $maxVal) * 100));
                            $expPct = min(100, round(($cm['expense'] / $maxVal) * 100));
                        @endphp
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs font-semibold">
                                <span class="w-12 text-slate-600 dark:text-slate-400">{{ $cm['month'] }}</span>
                                <div class="flex items-center gap-3 text-[11px]">
                                    <span class="text-emerald-600 dark:text-emerald-400">Rp {{ number_format($cm['revenue'], 0, ',', '.') }}</span>
                                    <span class="text-slate-400">|</span>
                                    <span class="text-rose-600 dark:text-rose-400">Rp {{ number_format($cm['expense'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 h-3 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden p-0.5">
                                <div class="bg-emerald-500 rounded-full transition-all duration-500" style="width: {{ $revPct }}%"></div>
                                <div class="bg-rose-500 rounded-full transition-all duration-500" style="width: {{ $expPct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Right Side Widgets -->
        <div class="space-y-6">
            <!-- Widget: Status Periode Akuntansi -->
            @if ($setting->show_period_status)
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">📅 Periode Akuntansi Aktif</h4>
                        <a href="{{ route('accounting.periods.index') }}" wire:navigate class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Kelola &rarr;</a>
                    </div>

                    @if ($activePeriod)
                        <div class="p-3.5 rounded-xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-200/60 dark:border-indigo-800/60 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-black text-indigo-950 dark:text-indigo-200">{{ date('F Y', mktime(0,0,0, $activePeriod->month, 1, $activePeriod->year)) }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">TERBUKA</span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Status Lock Key: {{ $activePeriod->lock_key ? '🔒 Dilindungi Lock Key' : '🔓 Tanpa Lock Key' }}</p>
                        </div>
                    @else
                        <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold">Tidak ada periode yang terbuka.</p>
                    @endif
                </div>
            @endif

            <!-- Widget: Saldo Kas & Bank -->
            @if ($setting->show_cash_bank_summary && !empty($cashBankAccounts))
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">🏦 Saldo Kas & Kesetaraan Kas</h4>
                        <a href="{{ route('accounting.accounts.index') }}" wire:navigate class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">COA &rarr;</a>
                    </div>

                    <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                        @foreach ($cashBankAccounts as $cb)
                            <div class="flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white block">{{ $cb['name'] }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $cb['code'] }}</span>
                                </div>
                                <span class="font-bold {{ $cb['balance'] >= 0 ? 'text-slate-900 dark:text-white' : 'text-rose-500' }}">
                                    Rp {{ number_format($cb['balance'], 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Widget: Quick Action Shortcuts -->
            @if ($setting->show_quick_actions)
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">⚡ Pintasan Akses Cepat</h4>
                    <div class="grid grid-cols-2 gap-2.5">
                        @can('journals.create')
                            <a href="{{ route('accounting.journals.create') }}" wire:navigate class="flex flex-col items-center justify-center p-3 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/50 transition-all text-center">
                                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span class="text-[11px] font-bold">Buat Jurnal</span>
                            </a>
                        @endcan

                        @can('accounts.view')
                            <a href="{{ route('accounting.accounts.index') }}" wire:navigate class="flex flex-col items-center justify-center p-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 transition-all text-center">
                                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M3 7l9 6 9-6M3 7l9-6 9 6"></path>
                                </svg>
                                <span class="text-[11px] font-bold">Master COA</span>
                            </a>
                        @endcan

                        @can('reports.profit_loss')
                            <a href="{{ route('accounting.reports.profit-loss') }}" wire:navigate class="flex flex-col items-center justify-center p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/50 transition-all text-center">
                                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <span class="text-[11px] font-bold">Laba Rugi</span>
                            </a>
                        @endcan

                        @can('reports.balance_sheet')
                            <a href="{{ route('accounting.reports.balance-sheet') }}" wire:navigate class="flex flex-col items-center justify-center p-3 rounded-xl bg-violet-50 dark:bg-violet-950/40 hover:bg-violet-100 dark:hover:bg-violet-900/50 text-violet-600 dark:text-violet-400 border border-violet-200/50 dark:border-violet-800/50 transition-all text-center">
                                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                                </svg>
                                <span class="text-[11px] font-bold">Laporan Neraca</span>
                            </a>
                        @endcan
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Section 3: Tabel Jurnal Terbaru -->
    @if ($setting->show_recent_journals && !empty($recentJournals))
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">📑 Jurnal Transaksi Terbaru</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar {{ count($recentJournals) }} transaksi jurnal terbaru yang dicatat ke dalam sistem.</p>
                </div>
                <a href="{{ route('accounting.journals.index') }}" wire:navigate class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Lihat Semua Jurnal &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-2.5 px-3">Tanggal</th>
                            <th class="py-2.5 px-3">No. Jurnal</th>
                            <th class="py-2.5 px-3">Jenis Jurnal</th>
                            <th class="py-2.5 px-3">Unit</th>
                            <th class="py-2.5 px-3">Keterangan</th>
                            <th class="py-2.5 px-3 text-right">Total Debit</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        @foreach ($recentJournals as $rj)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-all">
                                <td class="py-2.5 px-3 text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ $rj->entry_date->format('d/m/Y') }}</td>
                                <td class="py-2.5 px-3 font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">{{ $rj->entry_number }}</td>
                                <td class="py-2.5 px-3 text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $rj->journalType?->name ?? '-' }}</td>
                                <td class="py-2.5 px-3 text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $rj->lines->first()?->unit?->code ?? 'Konsolidasi' }}</td>
                                <td class="py-2.5 px-3 text-slate-900 dark:text-white max-w-xs truncate">{{ $rj->description }}</td>
                                <td class="py-2.5 px-3 text-right font-bold text-slate-900 dark:text-white whitespace-nowrap">Rp {{ number_format($rj->total_debit, 0, ',', '.') }}</td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                        {{ $rj->status === 'posted' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : '' }}
                                        {{ $rj->status === 'draft' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' : '' }}
                                        {{ $rj->status === 'reversed' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' : '' }}
                                    ">
                                        {{ strtoupper($rj->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
