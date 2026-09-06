<div class="p-4 sm:p-6 space-y-5">
    <!-- TOP BREADCRUMB & HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('accounting.reconciliation.index') }}" wire:navigate class="hover:text-indigo-600 dark:hover:text-indigo-400 font-semibold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span>Daftar Rekonsiliasi</span>
                </a>
                <span>/</span>
                <span class="text-slate-700 dark:text-slate-300 font-bold">{{ $statement->bank_name }} - {{ $statement->account_number }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <span>Lembar Kerja Rekonsiliasi Bank</span>
                @if($statement->status === 'reconciled')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/80">
                        ✓ Reconciled (Klop)
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800/80">
                        In Progress
                    </span>
                @endif
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Akun Pembukuan: <strong>{{ $statement->account?->code }} - {{ $statement->account?->name }}</strong> | Periode: <strong>{{ \Carbon\Carbon::parse($statement->period_start)->isoFormat('D MMMM Y') }} s/d {{ \Carbon\Carbon::parse($statement->period_end)->isoFormat('D MMMM Y') }}</strong>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @can('reconciliation.manage')
                <button 
                    wire:click="runAutoMatch" 
                    type="button" 
                    class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 flex items-center gap-2 transition-all transform active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <span>Jalankan Auto-Match</span>
                </button>
            @endcan

            <a 
                href="{{ route('accounting.reconciliation.pdf', $statement->id) }}" 
                target="_blank"
                class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50 text-xs font-bold shadow-xs flex items-center gap-2 transition-all">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Cetak Laporan PDF</span>
            </a>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    @if (session()->has('success'))
        <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm flex items-center gap-2.5">
            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-rose-800 dark:text-rose-300 text-xs sm:text-sm flex items-center gap-2.5">
            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- SUMMARY METRICS BAR -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Saldo Menurut Bank</span>
            <span class="text-base font-black font-mono text-slate-800 dark:text-slate-100 mt-1 block">
                Rp {{ number_format($summary['bank_balance'], 2, ',', '.') }}
            </span>
            <span class="text-[10px] text-slate-400 mt-0.5 block">Closing e-Statement CMS</span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Saldo Menurut Buku</span>
            <span class="text-base font-black font-mono text-slate-800 dark:text-slate-100 mt-1 block">
                Rp {{ number_format($summary['book_balance'], 2, ',', '.') }}
            </span>
            <span class="text-[10px] text-slate-400 mt-0.5 block">General Ledger Kas/Bank</span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Selisih Rekonsiliasi</span>
            <span class="text-base font-black font-mono mt-1 block {{ $summary['difference'] < 0.01 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                Rp {{ number_format($summary['difference'], 2, ',', '.') }}
            </span>
            <span class="text-[10px] text-slate-400 mt-0.5 block">
                {{ $summary['difference'] < 0.01 ? '✓ Seimbang Sempurna' : 'Perlu penyesuaian' }}
            </span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Progress Pencocokan</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-base font-black font-mono text-indigo-600 dark:text-indigo-400">{{ $summary['reconciled_percentage'] }}%</span>
                <span class="text-[10px] text-slate-500 font-mono">({{ $summary['total_matched_count'] }} cocok / {{ $summary['total_unmatched_count'] }} belum)</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 mt-2 overflow-hidden">
                <div class="h-1.5 rounded-full {{ $summary['reconciled_percentage'] >= 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}" style="width: {{ $summary['reconciled_percentage'] }}%"></div>
            </div>
        </div>
    </div>

    <!-- DUAL PANEL INTERACTIVE COMPARISON WORKSPACE -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
        <!-- LEFT PANEL: BANK STATEMENT LINES -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden flex flex-col h-[700px]">
            <!-- PANEL HEADER & FILTER -->
            <div class="p-3.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 space-y-2.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                        <h3 class="font-bold text-xs uppercase tracking-wider text-slate-800 dark:text-slate-100">
                            1. Rekening Koran Bank BRI ({{ $bankLines->count() }} Baris)
                        </h3>
                    </div>
                    <div class="inline-flex rounded-lg bg-slate-200/60 dark:bg-slate-800 p-0.5 text-[10px] font-bold">
                        <button wire:click="$set('bankFilter', 'all')" type="button" class="px-2 py-0.5 rounded-md {{ $bankFilter === 'all' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-500' }}">Semua</button>
                        <button wire:click="$set('bankFilter', 'unmatched')" type="button" class="px-2 py-0.5 rounded-md {{ $bankFilter === 'unmatched' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-500' }}">Belum Cocok</button>
                        <button wire:click="$set('bankFilter', 'matched')" type="button" class="px-2 py-0.5 rounded-md {{ $bankFilter === 'matched' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-500' }}">Cocok</button>
                    </div>
                </div>

                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input 
                        wire:model.live.debounce.300ms="search" 
                        type="text" 
                        placeholder="Cari transaksi, nominal, atau keterangan..." 
                        class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>
            </div>

            <!-- LINES LIST (SCROLLABLE) -->
            <div class="overflow-y-auto flex-1 divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                @forelse($bankLines as $line)
                    @php
                        $isSelected = in_array($line->id, $selectedBankLineIds);
                        $isMatched = in_array($line->match_status, ['matched', 'manual_matched', 'adjusted', 'opening_reconciled']);
                    @endphp
                    <div 
                        wire:click="selectBankLine({{ $line->id }})" 
                        class="p-3 transition-all cursor-pointer select-none {{ $isSelected ? 'bg-indigo-50/90 dark:bg-indigo-950/60 border-l-4 border-indigo-600' : ($isMatched ? 'bg-slate-50/40 dark:bg-slate-900/40 hover:bg-slate-100/60' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60') }}">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2">
                                @if(! $isMatched)
                                    <input 
                                        type="checkbox" 
                                        wire:click.stop="toggleBankLine({{ $line->id }})" 
                                        {{ $isSelected ? 'checked' : '' }} 
                                        class="rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5 cursor-pointer">
                                @endif
                                <span class="font-mono text-[11px] font-bold text-slate-500">{{ \Carbon\Carbon::parse($line->transaction_date)->format('d/m/Y') }}</span>
                                @if($line->transaction_time)
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $line->transaction_time }}</span>
                                @endif
                                @if($line->match_status === 'matched')
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">✓ Auto</span>
                                @elseif($line->match_status === 'manual_matched')
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-200 dark:border-blue-800/60">✓ Manual</span>
                                @elseif($line->match_status === 'adjusted')
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-50 text-purple-600 dark:bg-purple-950/40 dark:text-purple-400 border border-purple-200 dark:border-purple-800/60">✓ Penyesuaian</span>
                                @elseif($line->match_status === 'opening_reconciled')
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-sky-50 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400 border border-sky-200 dark:border-sky-800/60" title="{{ $line->notes }}">✓ Saldo Awal</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">Belum Cocok</span>
                                @endif
                            </div>

                            <div class="text-right">
                                @if((float)$line->debit > 0)
                                    <span class="font-mono font-bold text-rose-600 dark:text-rose-400 text-xs">
                                        - Rp {{ number_format((float)$line->debit, 2, ',', '.') }}
                                    </span>
                                    <span class="text-[9px] text-slate-400 block">Debet (Keluar)</span>
                                @else
                                    <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400 text-xs">
                                        + Rp {{ number_format((float)$line->credit, 2, ',', '.') }}
                                    </span>
                                    <span class="text-[9px] text-slate-400 block">Kredit (Masuk)</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-1.5 text-slate-700 dark:text-slate-300 font-medium line-clamp-2">
                            {{ $line->description }}
                        </div>

                        <div class="mt-1.5 flex items-center justify-between text-[10px] text-slate-400">
                            <span>Saldo: <strong class="font-mono text-slate-600 dark:text-slate-300">Rp {{ number_format((float)$line->balance, 2, ',', '.') }}</strong></span>
                            @if($line->teller_id)
                                <span>Teller: <strong class="font-mono">{{ $line->teller_id }}</strong></span>
                            @endif
                        </div>

                        <!-- ACTIONS FOR THIS LINE -->
                        <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2" wire:click.stop>
                            @if($isMatched)
                                <button 
                                    wire:click="unmatchLine({{ $line->id }})" 
                                    type="button" 
                                    class="text-[10px] text-rose-600 dark:text-rose-400 font-bold hover:underline cursor-pointer">
                                    Batalkan Cocok (Unmatch)
                                </button>
                            @else
                                <div class="flex items-center gap-1.5">
                                    <button 
                                        wire:click="markAsOpeningOutstanding({{ $line->id }})" 
                                        type="button" 
                                        title="Tandai sebagai pencairan cek beredar / pos saldo awal (Desember 2024 sebelum Go-Live). Tidak membuat jurnal baru di tahun berjalan."
                                        class="px-2 py-1 rounded bg-sky-50 hover:bg-sky-100 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800/60 text-[10px] font-bold transition-all cursor-pointer">
                                        + Saldo Awal Lalu
                                    </button>
                                    <button 
                                        wire:click="openAdjustmentModal({{ $line->id }})" 
                                        type="button" 
                                        class="px-2 py-1 rounded bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold transition-all cursor-pointer">
                                        + Jurnal Penyesuaian
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        Tidak ada transaksi rekening koran yang sesuai filter.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- RIGHT PANEL: GENERAL LEDGER LINES (BUKU BESAR KAS/BANK) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden flex flex-col h-[700px]">
            <!-- PANEL HEADER -->
            <div class="p-3.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 space-y-2.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="font-bold text-xs uppercase tracking-wider text-slate-800 dark:text-slate-100">
                            2. Buku Besar Kas/Bank ({{ $bookLines->count() }} Baris Jurnal)
                        </h3>
                    </div>
                    <div class="inline-flex rounded-lg bg-slate-200/60 dark:bg-slate-800 p-0.5 text-[10px] font-bold">
                        <button wire:click="$set('bookFilter', 'all')" type="button" class="px-2 py-0.5 rounded-md {{ $bookFilter === 'all' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-500' }}">Semua</button>
                        <button wire:click="$set('bookFilter', 'unmatched')" type="button" class="px-2 py-0.5 rounded-md {{ $bookFilter === 'unmatched' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-500' }}">Belum Cocok</button>
                        <button wire:click="$set('bookFilter', 'matched')" type="button" class="px-2 py-0.5 rounded-md {{ $bookFilter === 'matched' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-500' }}">Cocok</button>
                    </div>
                </div>

                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input 
                        wire:model.live.debounce.300ms="bookSearch" 
                        type="text" 
                        placeholder="Cari transaksi jurnal, nominal, atau no. bukti..." 
                        class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>

                <!-- Filter Periode Tab Bar -->
                <div class="inline-flex rounded-lg bg-slate-200/60 dark:bg-slate-800 p-0.5 text-[10px] font-bold w-full">
                    <button wire:click="$set('bookPeriodFilter', 'all')" type="button" 
                            class="flex-1 text-center px-1.5 py-1 rounded-md transition-all {{ $bookPeriodFilter === 'all' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-xs font-black' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                        Semua ({{ $periodCounts['all'] ?? 0 }})
                    </button>
                    <button wire:click="$set('bookPeriodFilter', 'current')" type="button" 
                            class="flex-1 text-center px-1.5 py-1 rounded-md transition-all {{ $bookPeriodFilter === 'current' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-xs font-black' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                        Bulan Ini ({{ $periodCounts['current'] ?? 0 }})
                    </button>
                    <button wire:click="$set('bookPeriodFilter', 'prior')" type="button" 
                            class="flex-1 text-center px-1.5 py-1 rounded-md transition-all flex items-center justify-center gap-1 {{ $bookPeriodFilter === 'prior' ? 'bg-white dark:bg-slate-700 text-amber-600 dark:text-amber-400 shadow-xs font-black' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}"
                            title="Transaksi buku besar sebelum awal rekening koran">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Sebelum ({{ $periodCounts['prior'] ?? 0 }})
                    </button>
                    <button wire:click="$set('bookPeriodFilter', 'next')" type="button" 
                            class="flex-1 text-center px-1.5 py-1 rounded-md transition-all flex items-center justify-center gap-1 {{ $bookPeriodFilter === 'next' ? 'bg-white dark:bg-slate-700 text-purple-600 dark:text-purple-400 shadow-xs font-black' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}"
                            title="Transaksi buku besar setelah akhir rekening koran">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                        Sesudah ({{ $periodCounts['next'] ?? 0 }})
                    </button>
                </div>
            </div>

            <!-- LINES LIST (SCROLLABLE) -->
            <div class="overflow-y-auto flex-1 divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                @forelse($bookLines as $jLine)
                    @php
                        $isSelected = in_array($jLine->id, $selectedBookLineIds);
                        $jEntry = $jLine->journalEntry;
                        $isDebit = (float)$jLine->debit > 0;
                        $isReconciled = in_array($jLine->id, $matchedIds);
                    @endphp
                    <div 
                        wire:click="selectBookLine({{ $jLine->id }})" 
                        class="p-3 transition-all cursor-pointer select-none {{ $isSelected ? 'bg-emerald-50/90 dark:bg-emerald-950/60 border-l-4 border-emerald-600' : ($isReconciled ? 'bg-slate-50/40 dark:bg-slate-900/40 hover:bg-slate-100/60' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60') }}">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2">
                                @if(! $isReconciled)
                                    <input 
                                        type="checkbox" 
                                        wire:click.stop="toggleBookLine({{ $jLine->id }})" 
                                        {{ $isSelected ? 'checked' : '' }} 
                                        class="rounded border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500 w-3.5 h-3.5 cursor-pointer">
                                @endif
                                <span class="font-mono text-[11px] font-bold text-slate-500">{{ \Carbon\Carbon::parse($jEntry?->entry_date)->format('d/m/Y') }}</span>
                                <span class="font-mono text-[10px] text-slate-600 dark:text-slate-300 font-bold">{{ $jEntry?->entry_number }}</span>
                                @if($jEntry && $jEntry->entry_date < $statement->period_start)
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60" title="Transaksi dari periode sebelum rekening koran">Periode Lalu</span>
                                @elseif($jEntry && $jEntry->entry_date > $statement->period_end)
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-50 text-purple-600 dark:bg-purple-950/40 dark:text-purple-400 border border-purple-200 dark:border-purple-800/60" title="Transaksi dicatat pada periode setelah rekening koran">Periode Sesudah</span>
                                @endif
                                @if($isReconciled)
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">✓ Klop</span>
                                @endif
                            </div>

                            <div class="text-right">
                                @if($isDebit)
                                    <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400 text-xs">
                                        + Rp {{ number_format((float)$jLine->debit, 2, ',', '.') }}
                                    </span>
                                    <span class="text-[9px] text-slate-400 block">Debit (Penerimaan Buku)</span>
                                @else
                                    <span class="font-mono font-bold text-rose-600 dark:text-rose-400 text-xs">
                                        - Rp {{ number_format((float)$jLine->credit, 2, ',', '.') }}
                                    </span>
                                    <span class="text-[9px] text-slate-400 block">Kredit (Pengeluaran Buku)</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-1.5 text-slate-700 dark:text-slate-300 font-medium line-clamp-2">
                            {{ $jLine->description ?: $jEntry?->description }}
                        </div>

                        <div class="mt-1.5 flex items-center justify-between text-[10px] text-slate-400">
                            <span>Unit: <strong class="font-semibold text-slate-600 dark:text-slate-300">{{ $jLine->unit?->code ?? '-' }}</strong></span>
                            <span>Bukti: <strong class="font-mono">{{ $jEntry?->document_number ?? '-' }}</strong></span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        Tidak ada transaksi jurnal pada akun bank ini di rentang tanggal terkait.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- FLOATING MANUAL MATCH TOOLBAR & DIFFERENCE CALCULATOR -->
    @if (count($selectedBankLineIds) > 0 || count($selectedBookLineIds) > 0)
        <div class="fixed bottom-6 inset-x-0 z-40 flex justify-center px-4">
            <div class="bg-slate-900/95 backdrop-blur-md text-white rounded-2xl shadow-2xl border border-slate-700/80 p-4 max-w-4xl w-full flex flex-col md:flex-row md:items-center justify-between gap-4 animate-in fade-in slide-in-from-bottom-4 duration-200">
                <!-- COMPARISON SUMMARY & REAL-TIME DIFFERENCE -->
                <div class="flex flex-wrap items-center gap-3 text-xs">
                    <!-- Bank Selected Total -->
                    <div class="bg-slate-800/90 px-3 py-1.5 rounded-xl border border-slate-700/60">
                        <span class="text-[10px] text-slate-400 block font-medium">Bank Terpilih ({{ count($selectedBankLineIds) }} baris)</span>
                        <span class="font-mono font-bold text-blue-400 text-sm">
                            Rp {{ number_format($this->selectedBankTotal, 2, ',', '.') }}
                        </span>
                    </div>

                    <span class="text-slate-500 font-bold text-sm hidden sm:inline">vs</span>

                    <!-- Book Selected Total -->
                    <div class="bg-slate-800/90 px-3 py-1.5 rounded-xl border border-slate-700/60">
                        <span class="text-[10px] text-slate-400 block font-medium">Buku Besar Terpilih ({{ count($selectedBookLineIds) }} baris)</span>
                        <span class="font-mono font-bold text-emerald-400 text-sm">
                            Rp {{ number_format($this->selectedBookTotal, 2, ',', '.') }}
                        </span>
                    </div>

                    <!-- DIFFERENCE BADGE (DENGAN TOLERANSI RP 2) -->
                    <div class="px-3.5 py-1.5 rounded-xl border {{ $this->isMatchValid ? 'bg-emerald-950/70 border-emerald-500/60 text-emerald-300' : 'bg-rose-950/70 border-rose-500/60 text-rose-300' }}">
                        <span class="text-[10px] uppercase tracking-wider block font-bold {{ $this->isMatchValid ? 'text-emerald-400' : 'text-rose-400' }}">
                            @if ($this->difference <= 0.001)
                                ✓ Seimbang Sempurna (Rp 0)
                            @elseif ($this->isMatchValid)
                                ✓ Klop (Toleransi ≤ Rp 2)
                            @else
                                ⚠ Selisih Belum Klop (> Rp 2)
                            @endif
                        </span>
                        <span class="font-mono font-bold text-sm">
                            Rp {{ number_format($this->difference, 2, ',', '.') }}
                        </span>
                    </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="flex items-center gap-2.5 self-end md:self-center">
                    <button 
                        wire:click="clearSelection" 
                        type="button" 
                        class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300 transition-colors">
                        Batal Pilihan
                    </button>

                    <!-- USER REQUIREMENT: TOLERANSI RP 2 -->
                    @if ($this->isMatchValid)
                        <button 
                            wire:click="executeManualMatch" 
                            type="button" 
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-xs font-bold text-white shadow-lg shadow-emerald-500/30 flex items-center gap-2 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Tautkan Transaksi (Klop)</span>
                        </button>
                    @else
                        <button 
                            type="button" 
                            disabled 
                            title="Pencocokan hanya dapat dilakukan jika selisih maksimal Rp 2,00 dan kedua sisi telah dipilih."
                            class="px-4 py-2 rounded-xl bg-slate-800 text-slate-500 text-xs font-bold border border-slate-700/60 flex items-center gap-2 cursor-not-allowed opacity-70">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <span>Terkunci (Selisih > Rp 2)</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- QUICK ADJUSTMENT MODAL -->
    @if ($showAdjustmentModal && $adjustmentLine)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity">
            <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-slate-800 dark:text-slate-100">Buat Jurnal Penyesuaian Cepat</h3>
                            <p class="text-[11px] text-slate-500">Mencatat beban bank atau bunga giro langsung ke jurnal pembukuan.</p>
                        </div>
                    </div>
                    <button wire:click="closeAdjustmentModal" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit="submitAdjustment" class="p-6 space-y-4">
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 space-y-1 text-xs">
                        <div class="text-[10px] uppercase font-bold text-slate-400">Informasi Mutasi Bank:</div>
                        <div class="font-bold text-slate-800 dark:text-slate-100">{{ $adjustmentLine->description }}</div>
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-slate-500">Tanggal: {{ \Carbon\Carbon::parse($adjustmentLine->transaction_date)->format('d/m/Y') }}</span>
                            @if((float)$adjustmentLine->debit > 0)
                                <span class="font-mono font-bold text-rose-600 dark:text-rose-400">Debet Bank: Rp {{ number_format((float)$adjustmentLine->debit, 2, ',', '.') }}</span>
                            @else
                                <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">Kredit Bank: Rp {{ number_format((float)$adjustmentLine->credit, 2, ',', '.') }}</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                            Pilih Akun Lawan (Contra Account) *
                        </label>
                        <select wire:model="contraAccountId" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                            <option value="0">-- Pilih Akun --</option>
                            @foreach ($allAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ $acc->type }})</option>
                            @endforeach
                        </select>
                        @error('contraAccountId') <span class="text-[11px] text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                            Keterangan Jurnal Penyesuaian *
                        </label>
                        <input 
                            wire:model="adjustmentDescription" 
                            type="text" 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                        @error('adjustmentDescription') <span class="text-[11px] text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                        <button wire:click="closeAdjustmentModal" type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 flex items-center gap-2 transition-all disabled:opacity-50">
                            <span wire:loading.remove>Posting Penyesuaian</span>
                            <span wire:loading>Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
