<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('accounting.budgets.index') }}" wire:navigate class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Laporan Varian Anggaran (Budget vs Actual)
                </h1>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Komparasi real-time antara plafon pagu anggaran dengan realisasi buku besar (General Ledger).
            </p>
        </div>

        @if ($selectedBudget)
            <div class="flex items-center gap-2">
                <a href="{{ route('accounting.budgets.export.pdf', ['budgetId' => $selectedBudget->id, 'month' => $filterMonth, 'unitId' => $filterUnitId]) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 hover:bg-rose-100 rounded-xl text-xs font-semibold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Cetak PDF
                </a>
                <a href="{{ route('accounting.budgets.export.excel', ['budgetId' => $selectedBudget->id, 'month' => $filterMonth, 'unitId' => $filterUnitId]) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 rounded-xl text-xs font-semibold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Ekspor Excel
                </a>
            </div>
        @endif
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60 flex flex-wrap gap-4 items-center">
        <!-- Budget Selection -->
        <div class="flex-1 min-w-[240px]">
            <label class="block text-xs font-semibold uppercase text-gray-400 mb-1">Pilih Anggaran Master</label>
            <select wire:model.live="selectedBudgetId" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm py-2 px-3 text-gray-900 dark:text-white">
                @foreach ($budgets as $b)
                    <option value="{{ $b->id }}">{{ $b->fiscal_year }} - {{ $b->name }} ({{ ucfirst($b->status) }})</option>
                @endforeach
            </select>
        </div>

        <!-- Month Filter -->
        <div class="w-48">
            <label class="block text-xs font-semibold uppercase text-gray-400 mb-1">Periode Bulan</label>
            <select wire:model.live="filterMonth" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm py-2 px-3 text-gray-900 dark:text-white">
                <option value="">Semua (Setahun Penuh)</option>
                @foreach ($monthNames as $num => $name)
                    <option value="{{ $num }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Unit Filter -->
        <div class="w-48">
            <label class="block text-xs font-semibold uppercase text-gray-400 mb-1">Unit Bisnis</label>
            <select wire:model.live="filterUnitId" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm py-2 px-3 text-gray-900 dark:text-white">
                <option value="">Semua Unit</option>
                @foreach ($units as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($reportData)
        <!-- KPI Glassmorphic Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Budget -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60">
                <div class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Pagu Anggaran</div>
                <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">
                    Rp {{ number_format($reportData['summary']['total_budget'], 0, ',', '.') }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-2 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    Tahun {{ $reportData['year'] }} {{ $reportData['month'] ? '(' . $monthNames[$reportData['month']] . ')' : '' }}
                </div>
            </div>

            <!-- Total Actual -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60">
                <div class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Realisasi Aktual</div>
                <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                    Rp {{ number_format($reportData['summary']['total_actual'], 0, ',', '.') }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-2 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Terposting di Buku Besar
                </div>
            </div>

            <!-- Total Variance -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60">
                <div class="text-xs font-medium text-gray-400 uppercase tracking-wider">Sisa Pagu (Varian)</div>
                <div class="text-xl font-bold {{ $reportData['summary']['total_variance'] >= 0 ? 'text-gray-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }} mt-1">
                    Rp {{ number_format($reportData['summary']['total_variance'], 0, ',', '.') }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    {{ $reportData['summary']['total_variance'] >= 0 ? 'Sisa anggaran tersedia' : 'Melampaui plafon (Defisit)' }}
                </div>
            </div>

            <!-- Aggregate Absorption Rate -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60">
                <div class="text-xs font-medium text-gray-400 uppercase tracking-wider">Tingkat Serapan Total</div>
                <div class="text-xl font-bold text-gray-900 dark:text-white mt-1 flex items-baseline gap-2">
                    <span>{{ number_format($reportData['summary']['aggregate_absorption'], 1, ',', '.') }}%</span>
                </div>
                <!-- Mini Progress Bar -->
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-3 overflow-hidden">
                    <div class="h-2 rounded-full {{ $reportData['summary']['aggregate_absorption'] >= 100 ? 'bg-rose-500' : ($reportData['summary']['aggregate_absorption'] >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                         style="width: {{ min(100, $reportData['summary']['aggregate_absorption']) }}%"></div>
                </div>
            </div>
        </div>

        <!-- Breakdown Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60 overflow-hidden">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="font-bold text-gray-900 dark:text-white text-base">Rincian Perbandingan Akun</h3>
                <span class="text-xs text-gray-400">{{ count($reportData['items']) }} Akun Terdata</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                    <thead class="bg-gray-50/75 dark:bg-gray-700/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3.5">Kode & Nama Akun</th>
                            <th class="px-4 py-3.5">Unit</th>
                            <th class="px-6 py-3.5 text-right">Plafon Anggaran (Rp)</th>
                            <th class="px-6 py-3.5 text-right">Realisasi Aktual (Rp)</th>
                            <th class="px-6 py-3.5 text-right">Sisa / Varian (Rp)</th>
                            <th class="px-6 py-3.5 min-w-[160px]">Serapan (%)</th>
                            <th class="px-4 py-3.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @forelse ($reportData['items'] as $item)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $item['account_name'] }}</div>
                                    <div class="text-xs font-mono text-gray-400">{{ $item['account_code'] }}</div>
                                </td>
                                <td class="px-4 py-4 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $item['unit_name'] }}
                                </td>
                                <td class="px-6 py-4 text-right font-medium">
                                    {{ number_format($item['budget_amount'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($item['actual_amount'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right font-bold {{ $item['variance_amount'] >= 0 ? 'text-gray-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ number_format($item['variance_amount'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="h-2 rounded-full {{ $item['absorption_rate'] >= 100 ? 'bg-rose-500' : ($item['absorption_rate'] >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                                 style="width: {{ min(100, $item['absorption_rate']) }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold min-w-[45px] text-right">{{ $item['absorption_rate'] }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if ($item['status'] === 'safe')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                                            Aman
                                        </span>
                                    @elseif ($item['status'] === 'warning')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300">
                                            Waspada
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300">
                                            Melampaui
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                                    Tidak ada baris anggaran pada konfigurasi anggaran ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="p-12 text-center bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700">
            <p class="text-gray-500 dark:text-gray-400">Tidak ada anggaran yang dipilih atau belum ada anggaran terbuat.</p>
        </div>
    @endif
</div>
