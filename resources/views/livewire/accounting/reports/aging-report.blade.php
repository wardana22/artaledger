<div class="p-4 sm:p-5 space-y-4">
    <!-- HEADER TITLE & EXPORT ACTION -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Laporan Umur {{ $activeTab === 'receivable' ? 'Piutang Usaha (AR Aging)' : 'Hutang Usaha (AP Aging)' }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Analisis jatuh tempo tagihan dan pelunasan berbasis nomor faktur spesifik per tanggal cut-off.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <x-report-export-dropdown 
                :pdfUrl="route('accounting.reports.export.pdf', ['type' => $activeTab === 'receivable' ? 'aging-receivable' : 'aging-payable', 'as_of_date' => $asOfDate, 'unit' => $unitFilter])"
                :excelUrl="route('accounting.reports.export.excel', ['type' => $activeTab === 'receivable' ? 'aging-receivable' : 'aging-payable', 'as_of_date' => $asOfDate, 'unit' => $unitFilter])"
            />
        </div>
    </div>

    <!-- NOTIFIKASI FLASH -->
    @if (session()->has('message'))
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-xl text-xs font-semibold flex items-center justify-between">
            <span>{{ session('message') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-3 bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 rounded-xl text-xs font-semibold flex items-center justify-between">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- CONTROLLER TABS & FILTER BAR -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-3.5 rounded-2xl shadow-xs space-y-3.5">
        <!-- Dual Tab Switcher -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800/80 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                <button 
                    type="button" 
                    wire:click="setTab('receivable')"
                    class="px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-2 {{ $activeTab === 'receivable' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-indigo-600' }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Piutang Usaha (AR)
                </button>
                <button 
                    type="button" 
                    wire:click="setTab('payable')"
                    class="px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-2 {{ $activeTab === 'payable' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-amber-600' }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Hutang Usaha (AP)
                </button>
            </div>

            <!-- Toggle Filter Saldo Nol & Tombol Gabung Multi-Jurnal -->
            <div class="flex items-center gap-3">
                @php
                    $countSelected = count(array_filter($selectedLineIds));
                @endphp
                @if ($countSelected >= 2)
                    <button 
                        type="button" 
                        wire:click="openMergeModal"
                        class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5 animate-pulse"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                        <span>Gabung {{ $countSelected }} Jurnal Menjadi 1 Invoice</span>
                    </button>
                @endif

                <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-medium text-slate-600 dark:text-slate-400">
                    <input type="checkbox" wire:model.live="hideZeroBalances" class="rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    Sembunyikan Akun Saldo Nol
                </label>
            </div>
        </div>

        <!-- Filter Inputs -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end pt-2 border-t border-slate-100 dark:border-slate-800">
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Tanggal Acuan (Cut-Off)</label>
                <input wire:model.live="asOfDate" type="date" aria-label="Tanggal Acuan" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm font-medium dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 transition-all" />
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Unit Perusahaan</label>
                <select wire:model.live="unitFilter" aria-label="Unit Perusahaan" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm font-semibold focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 transition-all">
                    @if (auth()->user()?->hasGlobalUnitAccess())
                        <option value="all">🌐 Konsolidasi (Semua Unit)</option>
                    @endif
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->code }} - {{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- EXECUTIVE KPI CARDS -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Card 1: Total Saldo Terbuka -->
        <div class="p-3.5 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-xl shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Total Saldo Terbuka</span>
            <div class="text-sm md:text-base font-extrabold text-slate-900 dark:text-white mt-1">
                Rp {{ number_format($report['kpi']['total_outstanding'], 2, ',', '.') }}
            </div>
        </div>

        <!-- Card 2: Current (Belum Jatuh Tempo) -->
        <div class="p-3.5 bg-emerald-500/5 dark:bg-emerald-500/10 border border-emerald-500/20 rounded-xl shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block">Lancar (Current)</span>
            <div class="text-sm md:text-base font-extrabold text-emerald-700 dark:text-emerald-300 mt-1">
                Rp {{ number_format($report['kpi']['current'], 2, ',', '.') }}
            </div>
        </div>

        <!-- Card 3: 1 - 30 Hari -->
        <div class="p-3.5 bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/20 rounded-xl shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 block">1 - 30 Hari</span>
            <div class="text-sm md:text-base font-extrabold text-amber-700 dark:text-amber-300 mt-1">
                Rp {{ number_format($report['kpi']['overdue_1_30'], 2, ',', '.') }}
            </div>
        </div>

        <!-- Card 4: 31 - 60 Hari -->
        <div class="p-3.5 bg-orange-500/5 dark:bg-orange-500/10 border border-orange-500/20 rounded-xl shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-orange-600 dark:text-orange-400 block">31 - 60 Hari</span>
            <div class="text-sm md:text-base font-extrabold text-orange-700 dark:text-orange-300 mt-1">
                Rp {{ number_format($report['kpi']['overdue_31_60'], 2, ',', '.') }}
            </div>
        </div>

        <!-- Card 5: 61 - 90 Hari -->
        <div class="p-3.5 bg-rose-500/5 dark:bg-rose-500/10 border border-rose-500/20 rounded-xl shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 block">61 - 90 Hari</span>
            <div class="text-sm md:text-base font-extrabold text-rose-700 dark:text-rose-300 mt-1">
                Rp {{ number_format($report['kpi']['overdue_61_90'], 2, ',', '.') }}
            </div>
        </div>

        <!-- Card 6: > 90 Hari -->
        <div class="p-3.5 bg-red-600/10 border border-red-600/30 rounded-xl shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-red-600 dark:text-red-400 block">&gt; 90 Hari (Kritis)</span>
            <div class="text-sm md:text-base font-extrabold text-red-700 dark:text-red-300 mt-1">
                Rp {{ number_format($report['kpi']['overdue_over_90'], 2, ',', '.') }}
            </div>
        </div>
    </div>

    <!-- AGING TABLE -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 uppercase tracking-wider font-bold">
                        <th class="py-3 px-4">No. Invoice / Tagihan</th>
                        <th class="py-3 px-3">Rekanan / Deskripsi</th>
                        <th class="py-3 px-3">Tgl Dokumen</th>
                        <th class="py-3 px-3">Jatuh Tempo</th>
                        <th class="py-3 px-3 text-right">Saldo Terbuka</th>
                        <th class="py-3 px-3 text-right text-emerald-600 dark:text-emerald-400">Lancar</th>
                        <th class="py-3 px-3 text-right text-amber-600 dark:text-amber-400">1-30 Hr</th>
                        <th class="py-3 px-3 text-right text-orange-600 dark:text-orange-400">31-60 Hr</th>
                        <th class="py-3 px-3 text-right text-rose-600 dark:text-rose-400">61-90 Hr</th>
                        <th class="py-3 px-3 text-right text-red-600 dark:text-red-400">&gt;90 Hr</th>
                        <th class="py-3 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($report['accounts'] as $accItem)
                        @php
                            $accId = $accItem['account']['id'];
                            $isExpanded = isset($expandedAccounts[$accId]);
                        @endphp
                        <!-- Header Baris Akun (Level 1) -->
                        <tr class="bg-slate-100/70 dark:bg-slate-800/40 hover:bg-slate-200/50 dark:hover:bg-slate-800/70 cursor-pointer transition-all font-bold" wire:click="toggleAccount({{ $accId }})">
                            <td class="py-3 px-4 flex items-center gap-2" colspan="4">
                                <svg class="w-4 h-4 text-slate-500 transition-transform {{ $isExpanded ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                <span>{{ $accItem['account']['code'] }} - {{ $accItem['account']['name'] }}</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 font-semibold">
                                    {{ count($accItem['invoices']) }} tagihan
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right font-extrabold text-slate-900 dark:text-white">
                                Rp {{ number_format($accItem['subtotal']['total_outstanding'], 2, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right text-emerald-600 dark:text-emerald-400">
                                {{ $accItem['subtotal']['current'] > 0 ? 'Rp '.number_format($accItem['subtotal']['current'], 2, ',', '.') : '-' }}
                            </td>
                            <td class="py-3 px-3 text-right text-amber-600 dark:text-amber-400">
                                {{ $accItem['subtotal']['overdue_1_30'] > 0 ? 'Rp '.number_format($accItem['subtotal']['overdue_1_30'], 2, ',', '.') : '-' }}
                            </td>
                            <td class="py-3 px-3 text-right text-orange-600 dark:text-orange-400">
                                {{ $accItem['subtotal']['overdue_31_60'] > 0 ? 'Rp '.number_format($accItem['subtotal']['overdue_31_60'], 2, ',', '.') : '-' }}
                            </td>
                            <td class="py-3 px-3 text-right text-rose-600 dark:text-rose-400">
                                {{ $accItem['subtotal']['overdue_61_90'] > 0 ? 'Rp '.number_format($accItem['subtotal']['overdue_61_90'], 2, ',', '.') : '-' }}
                            </td>
                            <td class="py-3 px-3 text-right text-red-600 dark:text-red-400">
                                {{ $accItem['subtotal']['overdue_over_90'] > 0 ? 'Rp '.number_format($accItem['subtotal']['overdue_over_90'], 2, ',', '.') : '-' }}
                            </td>
                            <td class="py-3 px-3 text-center text-slate-400 text-[11px]">
                                {{ $isExpanded ? 'Tutup' : 'Buka' }}
                            </td>
                        </tr>

                        <!-- Baris Rincian Invoice (Level 2) -->
                        @if ($isExpanded)
                            @forelse ($accItem['invoices'] as $inv)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/20 transition-colors">
                                    <td class="py-2.5 px-4 pl-10 font-semibold text-slate-700 dark:text-slate-200">
                                        @if ($inv['is_registered_invoice'])
                                            <span class="inline-flex items-center gap-1.5 font-mono text-indigo-600 dark:text-indigo-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                {{ $inv['invoice_number'] }}
                                            </span>
                                        @else
                                            <div class="flex items-center gap-2">
                                                <input 
                                                    type="checkbox" 
                                                    wire:model.live="selectedLineIds.{{ $inv['id'] }}"
                                                    class="rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500"
                                                    title="Centang untuk menggabungkan beberapa jurnal menjadi 1 invoice"
                                                >
                                                <span class="inline-flex items-center gap-1 text-slate-400 italic">
                                                    <span>{{ $inv['entry_number'] }} (Belum Bernomor Invoice)</span>
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-600 dark:text-slate-400">
                                        {{ $inv['partner_name'] }}
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-500 font-mono">{{ $inv['invoice_date'] }}</td>
                                    <td class="py-2.5 px-3 text-slate-500 font-mono">{{ $inv['due_date'] }}</td>
                                    <td class="py-2.5 px-3 text-right font-bold text-slate-800 dark:text-slate-100">
                                        Rp {{ number_format($inv['remaining_amount'], 2, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right text-emerald-600 dark:text-emerald-400 font-mono">
                                        {{ $inv['buckets']['current'] > 0 ? number_format($inv['buckets']['current'], 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right text-amber-600 dark:text-amber-400 font-mono">
                                        {{ $inv['buckets']['overdue_1_30'] > 0 ? number_format($inv['buckets']['overdue_1_30'], 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right text-orange-600 dark:text-orange-400 font-mono">
                                        {{ $inv['buckets']['overdue_31_60'] > 0 ? number_format($inv['buckets']['overdue_31_60'], 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right text-rose-600 dark:text-rose-400 font-mono">
                                        {{ $inv['buckets']['overdue_61_90'] > 0 ? number_format($inv['buckets']['overdue_61_90'], 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right text-red-600 dark:text-red-400 font-mono font-bold">
                                        {{ $inv['buckets']['overdue_over_90'] > 0 ? number_format($inv['buckets']['overdue_over_90'], 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        @if (! $inv['is_registered_invoice'])
                                            <button 
                                                type="button" 
                                                wire:click="openAssignModal({{ $inv['id'] }})"
                                                class="px-2 py-1 rounded bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white transition-all text-[11px] font-semibold"
                                                title="Tugaskan nomor invoice sekarang"
                                            >
                                                Tugaskan No.
                                            </button>
                                        @else
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button 
                                                    type="button" 
                                                    wire:click="openEditModal({{ $inv['id'] }})"
                                                    class="p-1 px-1.5 rounded bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white transition-all text-[11px] font-semibold flex items-center gap-1 shadow-sm"
                                                    title="Edit / Koreksi Nomor Invoice, Rekanan, Jatuh Tempo, atau Nominal"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                    <span>Edit</span>
                                                </button>
                                                <button 
                                                    type="button" 
                                                    wire:click="unlinkInvoice({{ $inv['id'] }})"
                                                    wire:confirm="Yakin ingin membatalkan penugasan invoice ini? Baris jurnal akan dikembalikan ke status belum terdaftar."
                                                    class="p-1 px-1.5 rounded bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-rose-600 hover:border-rose-300 dark:hover:border-rose-800 transition-all text-[11px]"
                                                    title="Batalkan penugasan invoice (Unlink)"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="py-3 px-4 pl-10 text-slate-400 italic">Tidak ada rincian tagihan terbuka pada akun ini.</td>
                                </tr>
                            @endforelse
                        @endif
                    @empty
                        <tr>
                            <td colspan="11" class="py-8 text-center text-slate-400">
                                Tidak ditemukan data tagihan {{ $activeTab === 'receivable' ? 'piutang' : 'hutang' }} pada tanggal acuan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL PENUGASAN / PEMECAHAN INVOICE -->
    @if ($showAssignModal && $selectedJournalLine)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-2xl w-full border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden animate-in fade-in zoom-in duration-200">
                <!-- Modal Header -->
                <div class="px-5 py-4 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">
                            {{ $modalMode === 'merge' ? 'Penugasan Invoice Gabungan Multi-Jurnal' : 'Penugasan Nomor Invoice Resmi' }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            @if ($modalMode === 'merge')
                                Akumulasi Nominal ({{ count(array_filter($selectedLineIds)) }} Jurnal): <strong class="text-slate-700 dark:text-slate-200 font-semibold">Rp {{ number_format($mergeTotalAmount, 2, ',', '.') }}</strong>
                            @else
                                Jurnal: {{ $selectedJournalLine->journalEntry?->entry_number }} | 
                                Nominal: Rp {{ number_format($activeTab === 'receivable' ? $selectedJournalLine->debit : $selectedJournalLine->credit, 2, ',', '.') }}
                            @endif
                        </p>
                    </div>
                    <button type="button" wire:click="closeAssignModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Mode Switcher (Single vs Split) -->
                <div class="p-5 space-y-4">
                    @if ($modalMode !== 'merge')
                        <div class="flex items-center gap-2 p-1 bg-slate-100 dark:bg-slate-800 rounded-lg w-fit text-xs font-semibold">
                            <button 
                                type="button" 
                                wire:click="$set('modalMode', 'single')"
                                class="px-3 py-1.5 rounded-md transition-all {{ $modalMode === 'single' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-white shadow-2xs' : 'text-slate-500' }}"
                            >
                                1 Jurnal = 1 Invoice Tunggal
                            </button>
                            <button 
                                type="button" 
                                wire:click="$set('modalMode', 'split')"
                                class="px-3 py-1.5 rounded-md transition-all {{ $modalMode === 'split' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-white shadow-2xs' : 'text-slate-500' }}"
                            >
                                Pecah Menjadi Banyak Invoice (Split)
                            </button>
                        </div>
                    @endif

                    <!-- SINGLE MODE FORM -->
                    @if ($modalMode === 'single')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Nomor Invoice Fisik *</label>
                                <input type="text" wire:model="singleInvoiceNumber" placeholder="contoh: INV-2026-001" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                                @error('singleInvoiceNumber') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Pelanggan / Rekanan</label>
                                <input type="text" wire:model="singlePartnerName" placeholder="contoh: PT Mitra Abadi" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Invoice *</label>
                                <input type="date" wire:model="singleInvoiceDate" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Jatuh Tempo *</label>
                                <input type="date" wire:model="singleDueDate" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Catatan / Keterangan</label>
                                <textarea wire:model="singleNotes" rows="2" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium focus:ring-2 focus:ring-indigo-500"></textarea>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800 pt-3">
                            <button type="button" wire:click="closeAssignModal" class="px-4 py-2 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200">Batal</button>
                            <button type="button" wire:click="saveSingleInvoice" class="px-4 py-2 rounded-lg text-xs font-bold bg-indigo-600 text-white hover:bg-indigo-500">Simpan Invoice</button>
                        </div>
                    @elseif ($modalMode === 'split')
                        <!-- SPLIT MODE FORM -->
                        <div class="space-y-3">
                            @php
                                $totalSplit = (float) array_sum(array_map(fn($r) => (float) ($r['original_amount'] ?? 0), $splitRows));
                                $targetAmount = $activeTab === 'receivable' ? (float) $selectedJournalLine->debit : (float) $selectedJournalLine->credit;
                                $diff = $targetAmount - $totalSplit;
                            @endphp

                            <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                                <span>Nilai Jurnal: <strong>Rp {{ number_format($targetAmount, 2, ',', '.') }}</strong></span>
                                <span>Total Alokasi: <strong>Rp {{ number_format($totalSplit, 2, ',', '.') }}</strong></span>
                                <span class="{{ abs($diff) < 0.01 ? 'text-emerald-500 font-bold' : 'text-rose-500 font-bold' }}">
                                    Selisih: Rp {{ number_format($diff, 2, ',', '.') }}
                                </span>
                            </div>

                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                @foreach ($splitRows as $idx => $row)
                                    <div class="p-3 bg-slate-50/60 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 rounded-xl space-y-2">
                                        <div class="flex items-center justify-between text-xs font-bold text-slate-600">
                                            <span>Invoice #{{ $idx + 1 }}</span>
                                            @if (count($splitRows) > 1)
                                                <button type="button" wire:click="removeSplitRow({{ $idx }})" class="text-rose-500 hover:underline">Hapus</button>
                                            @endif
                                        </div>
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                                            <div>
                                                <label class="text-[10px] text-slate-400">No. Invoice</label>
                                                <input type="text" wire:model="splitRows.{{ $idx }}.invoice_number" placeholder="INV-..." class="w-full px-2 py-1 bg-white dark:bg-slate-900 border rounded text-xs">
                                            </div>
                                            <div>
                                                <label class="text-[10px] text-slate-400">Nominal (Rp)</label>
                                                <input type="number" step="0.01" wire:model.live="splitRows.{{ $idx }}.original_amount" class="w-full px-2 py-1 bg-white dark:bg-slate-900 border rounded text-xs font-semibold">
                                            </div>
                                            <div>
                                                <label class="text-[10px] text-slate-400">Jatuh Tempo</label>
                                                <input type="date" wire:model="splitRows.{{ $idx }}.due_date" class="w-full px-2 py-1 bg-white dark:bg-slate-900 border rounded text-xs">
                                            </div>
                                            <div>
                                                <label class="text-[10px] text-slate-400">Rekanan</label>
                                                <input type="text" wire:model="splitRows.{{ $idx }}.partner_name" placeholder="Nama..." class="w-full px-2 py-1 bg-white dark:bg-slate-900 border rounded text-xs">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" wire:click="addSplitRow" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
                                + Tambah Baris Pecahan Invoice
                            </button>

                            <div class="mt-4 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800 pt-3">
                                <button type="button" wire:click="closeAssignModal" class="px-4 py-2 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200">Batal</button>
                                <button 
                                    type="button" 
                                    wire:click="saveSplitInvoices" 
                                    @disabled(abs($diff) >= 0.01)
                                    class="px-4 py-2 rounded-lg text-xs font-bold bg-indigo-600 text-white hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Simpan Hasil Pemecahan
                                </button>
                            </div>
                        </div>
                    @elseif ($modalMode === 'merge')
                        @php
                            $currentMergeSum = (float) array_sum(array_map(fn($r) => (float) ($r['original_amount'] ?? 0), $mergeInvoiceRows));
                            $mergeDiff = $mergeTotalAmount - $currentMergeSum;
                        @endphp
                        <!-- MERGE MODE FORM -->
                        <div class="space-y-4">
                            <!-- SUB-MODE TABS -->
                            <div class="flex items-center gap-2 p-1 bg-slate-100 dark:bg-slate-800/80 rounded-xl border border-slate-200 dark:border-slate-700">
                                <button 
                                    type="button" 
                                    wire:click="setMergeSubMode('single')"
                                    class="flex-1 py-1.5 px-3 rounded-lg text-xs font-bold transition-all {{ $mergeSubMode === 'single' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200' }}"
                                >
                                    📄 1 Invoice Gabungan
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="setMergeSubMode('multiple')"
                                    class="flex-1 py-1.5 px-3 rounded-lg text-xs font-bold transition-all {{ $mergeSubMode === 'multiple' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200' }}"
                                >
                                    📑 Beberapa Invoice (Multi-Invoice)
                                </button>
                            </div>

                            @if ($mergeSubMode === 'single')
                                <div class="p-3 bg-indigo-500/10 border border-indigo-500/20 rounded-xl text-xs text-indigo-700 dark:text-indigo-300">
                                    <strong>Penggabungan Multi-Jurnal:</strong> Anda sedang menggabungkan <strong>{{ count(array_filter($selectedLineIds)) }}</strong> transaksi jurnal pengakuan menjadi <strong>1 nomor invoice fisik gabungan</strong>.
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Nomor Invoice Gabungan *</label>
                                        <input type="text" wire:model="mergeInvoiceNumber" placeholder="contoh: INV-2026-GABUNGAN" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                                        @error('mergeInvoiceNumber') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Rekanan</label>
                                        <input type="text" wire:model="mergePartnerName" placeholder="contoh: PT Multi Transaksi" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Invoice *</label>
                                        <input type="date" wire:model="mergeInvoiceDate" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Jatuh Tempo *</label>
                                        <input type="date" wire:model="mergeDueDate" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Catatan / Keterangan</label>
                                        <textarea wire:model="mergeNotes" rows="2" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium focus:ring-2 focus:ring-indigo-500"></textarea>
                                    </div>
                                </div>
                            @else
                                <!-- MULTIPLE INVOICES SUB-MODE -->
                                <div class="p-3 bg-indigo-500/10 border border-indigo-500/20 rounded-xl text-xs text-indigo-700 dark:text-indigo-300">
                                    <strong>Multi-Jurnal ke Banyak Invoice:</strong> Anda sedang memecah akumulasi <strong>{{ count(array_filter($selectedLineIds)) }}</strong> jurnal terpilih menjadi <strong>{{ count($mergeInvoiceRows) }} nomor invoice fisik</strong>.
                                </div>

                                <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                                    <span>Akumulasi Jurnal: <strong>Rp {{ number_format($mergeTotalAmount, 2, ',', '.') }}</strong></span>
                                    <span>Total Invoice: <strong>Rp {{ number_format($currentMergeSum, 2, ',', '.') }}</strong></span>
                                    <span class="{{ abs($mergeDiff) < 0.01 ? 'text-emerald-500 font-bold' : 'text-rose-500 font-bold' }}">
                                        Selisih: Rp {{ number_format($mergeDiff, 2, ',', '.') }}
                                    </span>
                                </div>

                                <div class="space-y-2 max-h-64 overflow-y-auto">
                                    @foreach ($mergeInvoiceRows as $idx => $row)
                                        <div class="p-3 bg-slate-50/60 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 rounded-xl space-y-2">
                                            <div class="flex items-center justify-between text-xs font-bold text-slate-600 dark:text-slate-300">
                                                <span>Invoice #{{ $idx + 1 }}</span>
                                                @if (count($mergeInvoiceRows) > 1)
                                                    <button type="button" wire:click="removeMergeInvoiceRow({{ $idx }})" class="text-rose-500 hover:underline">Hapus</button>
                                                @endif
                                            </div>
                                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                                                <div>
                                                    <label class="text-[10px] text-slate-400">No. Invoice *</label>
                                                    <input type="text" wire:model="mergeInvoiceRows.{{ $idx }}.invoice_number" placeholder="INV-..." class="w-full px-2 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs">
                                                </div>
                                                <div>
                                                    <label class="text-[10px] text-slate-400">Nominal (Rp) *</label>
                                                    <input type="number" step="0.01" wire:model.live="mergeInvoiceRows.{{ $idx }}.original_amount" class="w-full px-2 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs font-semibold">
                                                </div>
                                                <div>
                                                    <label class="text-[10px] text-slate-400">Jatuh Tempo *</label>
                                                    <input type="date" wire:model="mergeInvoiceRows.{{ $idx }}.due_date" class="w-full px-2 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs">
                                                </div>
                                                <div>
                                                    <label class="text-[10px] text-slate-400">Rekanan</label>
                                                    <input type="text" wire:model="mergeInvoiceRows.{{ $idx }}.partner_name" placeholder="Nama Rekanan..." class="w-full px-2 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <button type="button" wire:click="addMergeInvoiceRow" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                    + Tambah Lembar Invoice Baru
                                </button>
                            @endif

                            <div class="mt-4 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800 pt-3">
                                <button type="button" wire:click="closeAssignModal" class="px-4 py-2 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200">Batal</button>
                                <button 
                                    type="button" 
                                    wire:click="saveMergedInvoice" 
                                    @if($mergeSubMode === 'multiple') @disabled(abs($mergeDiff) >= 0.01) @endif
                                    class="px-4 py-2 rounded-lg text-xs font-bold bg-indigo-600 text-white hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {{ $mergeSubMode === 'single' ? 'Simpan Invoice Gabungan' : 'Simpan Semua Invoice' }}
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL EDIT DATA INVOICE --}}
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/30">
                    <div class="flex items-center gap-2">
                        <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Koreksi / Edit Data Faktur</h3>
                            <p class="text-xs text-slate-500">Perbarui nomor invoice, rekanan, jatuh tempo, atau nominal faktur</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeEditModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    @if ($editHasSettlement)
                        <div class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-xl text-xs text-amber-700 dark:text-amber-300 space-y-1">
                            <p class="font-semibold flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                Invoice ini sudah memiliki pelunasan sebesar Rp {{ number_format($editSettledAmount, 2, ',', '.') }}
                            </p>
                            <p class="text-[11px] text-amber-600 dark:text-amber-400">Nominal invoice tidak boleh diubah menjadi lebih kecil dari total yang sudah dilunasi.</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Nomor Invoice <span class="text-rose-500">*</span></label>
                            <input 
                                type="text" 
                                wire:model="editInvoiceNumber" 
                                class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500 outline-none"
                                placeholder="Contoh: INV/2025/001"
                            >
                            @error('editInvoiceNumber') <span class="text-[10px] text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Nama Rekanan / Partner</label>
                            <input 
                                type="text" 
                                wire:model="editPartnerName" 
                                class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500 outline-none"
                                placeholder="Nama Vendor atau Pelanggan"
                            >
                            @error('editPartnerName') <span class="text-[10px] text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Tanggal Faktur <span class="text-rose-500">*</span></label>
                            <input 
                                type="date" 
                                wire:model="editInvoiceDate" 
                                class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500 outline-none"
                            >
                            @error('editInvoiceDate') <span class="text-[10px] text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Tanggal Jatuh Tempo <span class="text-rose-500">*</span></label>
                            <input 
                                type="date" 
                                wire:model="editDueDate" 
                                class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500 outline-none"
                            >
                            <span class="text-[10px] text-slate-400">Menentukan kelompok bucket umur tagihan</span>
                            @error('editDueDate') <span class="text-[10px] text-rose-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Nominal Faktur (Rp) <span class="text-rose-500">*</span></label>
                        <input 
                            type="number" 
                            step="0.01" 
                            wire:model="editOriginalAmount" 
                            class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-bold font-mono focus:ring-2 focus:ring-emerald-500 outline-none"
                        >
                        @error('editOriginalAmount') <span class="text-[10px] text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Catatan / Memo</label>
                        <textarea 
                            wire:model="editNotes" 
                            rows="2" 
                            class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500 outline-none"
                            placeholder="Catatan tambahan faktur..."
                        ></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
                    <button 
                        type="button" 
                        wire:click="closeEditModal" 
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 hover:bg-slate-100 transition-all"
                    >
                        Batal
                    </button>
                    <button 
                        type="button" 
                        wire:click="saveUpdatedInvoice" 
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-500 transition-all shadow-sm flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
