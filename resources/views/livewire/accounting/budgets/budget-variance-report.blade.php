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
                <a href="{{ route('accounting.budgets.export.pdf', ['budgetId' => $selectedBudget->id, 'month' => $filterMonth, 'unitId' => $filterUnitId, 'status' => $filterStatus]) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 hover:bg-rose-100 rounded-xl text-xs font-semibold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Cetak PDF
                </a>
                <a href="{{ route('accounting.budgets.export.excel', ['budgetId' => $selectedBudget->id, 'month' => $filterMonth, 'unitId' => $filterUnitId, 'status' => $filterStatus]) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 rounded-xl text-xs font-semibold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Ekspor Excel
                </a>
            </div>
        @endif
    </div>

    <!-- Unified Filters Bar -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
        <!-- Search -->
        <div class="lg:col-span-1">
            <label class="block text-xs font-semibold uppercase text-gray-400 mb-1">Cari Akun</label>
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Kode / Nama akun..."
                    class="w-full pl-8 pr-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500">
                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Filter Tahun -->
        <div>
            <label class="block text-xs font-semibold uppercase text-gray-400 mb-1">Tahun Anggaran</label>
            <select wire:model.live="filterYear" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs py-2 px-3 text-gray-900 dark:text-white focus:ring-emerald-500">
                @foreach ($years as $yr)
                    <option value="{{ $yr }}">Tahun {{ $yr }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filter Status Serapan -->
        <div>
            <label class="block text-xs font-semibold uppercase text-gray-400 mb-1">Status Serapan</label>
            <select wire:model.live="filterStatus" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs py-2 px-3 text-gray-900 dark:text-white focus:ring-emerald-500">
                <option value="all">Semua Status</option>
                <option value="terkendali">🟢 Terkendali (&lt; 80%)</option>
                <option value="mendekati">🟡 Mendekati (80% - 99.9%)</option>
                <option value="melampaui">🔴 Melampaui (≥ 100%)</option>
                <option value="anomali">⚫ Anomali (Realisasi Tidak Wajar)</option>
            </select>
        </div>

        <!-- Filter Unit Bisnis -->
        <div>
            <label class="block text-xs font-semibold uppercase text-gray-400 mb-1">Unit Bisnis</label>
            <select wire:model.live="filterUnitId" {{ ! $isGlobalUnit && count($units) <= 1 ? 'disabled' : '' }} class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs py-2 px-3 text-gray-900 dark:text-white focus:ring-emerald-500 disabled:opacity-60 disabled:cursor-not-allowed">
                @if ($isGlobalUnit)
                    <option value="">Semua Unit</option>
                @endif
                @foreach ($units as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filter Periode Bulan -->
        <div>
            <label class="block text-xs font-semibold uppercase text-gray-400 mb-1">Periode Bulan</label>
            <select wire:model.live="filterMonth" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs py-2 px-3 text-gray-900 dark:text-white focus:ring-emerald-500">
                <option value="">Setahun Penuh</option>
                @foreach ($monthNames as $num => $name)
                    <option value="{{ $num }}">Bulan {{ $name }}</option>
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

        <!-- Redesigned Main Table: Akun, Anggaran, Realisasi, Sisa Pagu, Serapan, Status -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60 overflow-hidden">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white text-base">Rincian Perbandingan Akun</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Analisa komparasi anggaran per akun beban dan unit bisnis.</p>
                </div>
                <span class="text-xs font-medium px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg">
                    {{ count($reportData['items']) }} Akun Ditampilkan
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                    <thead class="bg-gray-50/75 dark:bg-gray-700/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3.5">Akun Biaya</th>
                            <th class="px-4 py-3.5">Unit</th>
                            <th class="px-6 py-3.5 text-right">Anggaran (Rp)</th>
                            <th class="px-6 py-3.5 text-right">Realisasi (Rp)</th>
                            <th class="px-6 py-3.5 text-right">Sisa Pagu (Rp)</th>
                            <th class="px-6 py-3.5 min-w-[160px]">Serapan (%)</th>
                            <th class="px-4 py-3.5 text-center">Status</th>
                            <th class="px-4 py-3.5 text-center">Rincian Jurnal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @forelse ($reportData['items'] as $item)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                <!-- Akun -->
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $item['account_name'] }}</div>
                                    <div class="text-xs font-mono text-gray-400">{{ $item['account_code'] }}</div>
                                </td>

                                <!-- Unit -->
                                <td class="px-4 py-4 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $item['unit_name'] }}
                                </td>

                                <!-- Anggaran -->
                                <td class="px-6 py-4 text-right font-medium text-gray-800 dark:text-gray-200">
                                    {{ number_format($item['budget_amount'], 0, ',', '.') }}
                                </td>

                                <!-- Realisasi -->
                                <td class="px-6 py-4 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($item['actual_amount'], 0, ',', '.') }}
                                </td>

                                <!-- Sisa Pagu -->
                                <td class="px-6 py-4 text-right font-bold {{ $item['variance_amount'] >= 0 ? 'text-gray-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ number_format($item['variance_amount'], 0, ',', '.') }}
                                </td>

                                <!-- Serapan -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="h-2 rounded-full {{ $item['absorption_rate'] >= 100 ? 'bg-rose-500' : ($item['absorption_rate'] >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                                 style="width: {{ min(100, $item['absorption_rate']) }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold min-w-[45px] text-right">{{ $item['absorption_rate'] }}%</span>
                                    </div>
                                </td>

                                {{-- Status: Terkendali / Mendekati / Melampaui / Anomali --}}
                                <td class="px-4 py-4 text-center">
                                    @if ($item['status'] === 'terkendali')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Terkendali
                                        </span>
                                    @elseif ($item['status'] === 'mendekati')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Mendekati
                                        </span>
                                    @elseif ($item['status'] === 'melampaui')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Melampaui
                                        </span>
                                    @else
                                        {{-- Anomali: realisasi negatif atau tidak wajar, perlu diperiksa --}}
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700/80 text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600" title="Realisasi bernilai negatif atau nol — kemungkinan akun pendapatan atau data perlu diperiksa">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                            Anomali
                                        </span>
                                    @endif
                                </td>

                                <!-- Drill-down Button -->
                                <td class="px-4 py-4 text-center">
                                    <button type="button" wire:click="openDrillDown({{ $item['account_id'] }}, '{{ $item['account_code'] }}', '{{ addslashes($item['account_name']) }}')"
                                        title="Lihat transaksi jurnal pembentuk"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 text-indigo-600 dark:text-indigo-400 rounded-lg text-xs font-medium transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Jurnal
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-400">
                                    Tidak ada data baris anggaran yang cocok dengan kriteria filter saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="p-12 text-center bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700">
            <p class="text-gray-500 dark:text-gray-400">Tidak ada anggaran yang ditemukan untuk tahun {{ $filterYear }}. Silakan pilih tahun lain atau buat anggaran baru.</p>
        </div>
    @endif

    <!-- DRILL-DOWN MODAL: Rincian Transaksi Jurnal Pembentuk Realisasi -->
    @if ($showDrillDownModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-4xl w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Rincian Transaksi Jurnal Pembentuk Realisasi
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Akun: <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $drillDownAccountCode }}</span> - <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $drillDownAccountName }}</span>
                            (Tahun {{ $filterYear }} {{ $filterMonth ? '- Bulan ' . $monthNames[$filterMonth] : '' }})
                        </p>
                    </div>

                    <button wire:click="closeDrillDown" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="overflow-x-auto max-h-[420px]">
                    <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 uppercase font-semibold text-gray-500 dark:text-gray-400 sticky top-0">
                            <tr>
                                <th class="px-3 py-2.5">Tanggal</th>
                                <th class="px-3 py-2.5">Nomor Jurnal</th>
                                <th class="px-3 py-2.5">No. Dokumen</th>
                                <th class="px-3 py-2.5">Unit</th>
                                <th class="px-3 py-2.5">Keterangan</th>
                                <th class="px-3 py-2.5 text-right">Debit (Rp)</th>
                                <th class="px-3 py-2.5 text-right">Kredit (Rp)</th>
                                <th class="px-3 py-2.5 text-right">Netto (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            @forelse ($drillDownData['lines'] ?? [] as $line)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20">
                                    <td class="px-3 py-2 font-medium">{{ $line['entry_date'] }}</td>
                                    <td class="px-3 py-2 font-mono font-semibold text-indigo-600 dark:text-indigo-400">{{ $line['entry_number'] }}</td>
                                    <td class="px-3 py-2 font-mono text-gray-500">{{ $line['document_number'] }}</td>
                                    <td class="px-3 py-2">{{ $line['unit_name'] }}</td>
                                    <td class="px-3 py-2 max-w-xs truncate" title="{{ $line['description'] }}">{{ $line['description'] }}</td>
                                    <td class="px-3 py-2 text-right font-medium text-gray-900 dark:text-white">{{ number_format($line['debit'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right text-gray-500">{{ number_format($line['credit'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($line['net_amount'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-400">
                                        Belum ada riwayat transaksi jurnal terposting untuk akun ini pada periode terpilih.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700/40 p-3 rounded-xl flex items-center justify-between border border-gray-200/60 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">Total Akumulasi Realisasi Bersih:</span>
                    <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                        Rp {{ number_format($drillDownData['net_actual'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>

                <div class="flex justify-end pt-2">
                    <button wire:click="closeDrillDown" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-medium rounded-xl hover:bg-gray-200">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
