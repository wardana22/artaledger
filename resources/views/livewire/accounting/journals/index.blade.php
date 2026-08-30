<div class="p-4 sm:p-5 space-y-3.5">
    <x-journal-nav :active="$statusFilter === 'draft' ? 'draft' : 'jurnal-umum'" />

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                @if ($statusFilter === 'draft')
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                    </svg>
                    Jurnal Draft
                @else
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Jurnal Umum (General Journal)
                @endif
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                @if ($statusFilter === 'draft')
                    Daftar transaksi Jurnal Umum berstatus Draft yang menunggu peninjauan dan persetujuan (approval).
                @else
                    Daftar transaksi Jurnal Umum berstatus Posted dan Reversal pada ArtaLedger.
                @endif
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a 
                href="{{ route('accounting.journals.create') }}"
                wire:navigate
                class="inline-flex items-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-xs rounded-lg shadow-md shadow-indigo-500/20 transition-all duration-150 gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Buat Jurnal Manual
            </a>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 rounded-xl text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 rounded-xl text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Search & Filter Bar (Symmetrical Proportional 12-Column Grid) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-3.5 rounded-xl shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <!-- 1. Search Input (Col-Span 3) -->
            <div class="lg:col-span-3">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                    Pencarian
                </label>
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input 
                        wire:model.live.debounce.300ms="search" 
                        type="text" 
                        placeholder="Cari no. jurnal, bukti, desc..." 
                        class="w-full pl-9 pr-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-200 transition-all"
                    />
                </div>
            </div>

            <!-- 2. Status Filter (Col-Span 2) -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                    Status Jurnal
                </label>
                <select wire:model.live="statusFilter" aria-label="Status Jurnal" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-200 transition-all">
                    @if ($statusFilter === 'draft')
                        <option value="draft">Draft (Menunggu Approval)</option>
                    @else
                        <option value="all">Semua Status (Posted & Reversed)</option>
                        <option value="posted">Posted</option>
                        <option value="reversed">Reversed</option>
                    @endif
                </select>
            </div>

            <!-- 3. Unit Filter (Col-Span 3) -->
            <div class="lg:col-span-3">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                    Unit Perusahaan
                </label>
                <select wire:model.live="unitFilter" aria-label="Unit Perusahaan" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-200 transition-all">
                    @if (auth()->user()?->hasGlobalUnitAccess())
                        <option value="all">🌐 Konsolidasi (Semua Unit)</option>
                    @endif
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->code }} - {{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 4. Date Range Filter (Col-Span 4 - Roomy & Overflow-Free) -->
            <div class="lg:col-span-4">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                    Periode Tanggal
                </label>
                <div class="grid grid-cols-2 gap-1.5 items-center">
                    <input 
                        wire:model.live="startDate" 
                        type="date" 
                        aria-label="Dari Tanggal"
                        class="w-full px-2 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm font-medium dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" 
                    />
                    <input 
                        wire:model.live="endDate" 
                        type="date" 
                        aria-label="Sampai Tanggal"
                        class="w-full px-2 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm font-medium dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" 
                    />
                </div>
            </div>
        </div>
    </div>

    <!-- Clean Flat Journals Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5 w-32">Tanggal</th>
                        <th class="px-5 py-3.5 w-52">No. Bukti</th>
                        <th class="px-5 py-3.5">Keterangan Utama</th>
                        <th class="px-5 py-3.5 text-right w-36">Debit</th>
                        <th class="px-5 py-3.5 text-right w-36">Kredit</th>
                        <th class="px-5 py-3.5 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($journals as $journal)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-5 py-3.5 font-mono whitespace-nowrap text-slate-700 dark:text-slate-300">
                                {{ $journal->entry_date->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-3.5 font-mono">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ $journal->document_number ?: $journal->entry_number }}
                                    </span>
                                    @if ($journal->journalType)
                                        <span class="px-1.5 py-0.5 text-[10px] font-extrabold rounded bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20" title="{{ $journal->journalType->name }}">
                                            {{ $journal->journalType->code }}
                                        </span>
                                    @endif

                                    @if ($journal->status === 'draft')
                                        <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30">
                                            DRAFT
                                        </span>
                                    @elseif ($journal->status === 'posted')
                                        <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                                            POSTED
                                        </span>
                                    @elseif ($journal->status === 'reversed')
                                        <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/30">
                                            REVERSED
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-800 dark:text-slate-100">
                                {{ $journal->description ?: '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-mono font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                                Rp {{ number_format($journal->total_debit, 2, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-mono font-bold text-purple-600 dark:text-purple-400 whitespace-nowrap">
                                Rp {{ number_format($journal->total_credit, 2, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- 1. Tombol Lihat Detail Audit (Selalu ada untuk semua status) -->
                                    <button 
                                        wire:click="viewJournalDetail({{ $journal->id }})"
                                        title="Lihat Detail & Audit Trail Jurnal {{ $journal->entry_number }}"
                                        class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-sky-500/10 hover:text-sky-600 dark:hover:text-sky-400 hover:border-sky-500/30 shadow-2xs transition-all duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>

                                    @if ($journal->status === 'draft')
                                        <!-- AKSI JURNAL DRAFT: Approve, Edit, Delete -->
                                        @can('journals.post')
                                            <button 
                                                wire:click="postJournal({{ $journal->id }})"
                                                wire:confirm="Apakah Anda yakin ingin menyetujui dan memposting jurnal {{ $journal->entry_number }} ke Buku Besar?"
                                                title="Setujui & Posting Jurnal ke Buku Besar"
                                                class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-600 dark:hover:text-emerald-400 hover:border-emerald-500/30 shadow-2xs transition-all duration-200">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                        @endcan

                                        <a 
                                            href="{{ route('accounting.journals.edit', $journal->id) }}"
                                            wire:navigate
                                            title="Edit Jurnal {{ $journal->entry_number }}"
                                            class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-indigo-500/10 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-500/30 shadow-2xs transition-all duration-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>

                                        <button 
                                            wire:click="deleteJournal({{ $journal->id }})"
                                            wire:confirm="Apakah Anda yakin ingin menghapus draft jurnal {{ $journal->entry_number }} secara permanen?"
                                            title="Hapus Draft Jurnal {{ $journal->entry_number }}"
                                            class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-rose-500/10 hover:text-rose-600 dark:hover:text-rose-400 hover:border-rose-500/30 shadow-2xs transition-all duration-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    @elseif ($journal->status === 'posted')
                                         <!-- AKSI JURNAL POSTED: Reversal & Delete untuk Admin -->
                                         @if (! ($journal->source_type === 'reversal' || str_starts_with($journal->document_number ?? '', 'REV-') || str_starts_with($journal->description ?? '', 'REVERSAL:')))
                                             @can('journals.post')
                                                 <button 
                                                     wire:click="reverseJournal({{ $journal->id }})"
                                                     wire:confirm="Apakah Anda yakin ingin membalikkan (reverse) jurnal ini?"
                                                     title="Reverse Jurnal {{ $journal->entry_number }}"
                                                     class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 hover:border-amber-500/30 shadow-2xs transition-all duration-200">
                                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                     </svg>
                                                 </button>
                                             @endcan
                                         @endif

                                         @can('journals.delete')
                                             <button 
                                                 wire:click="deleteJournal({{ $journal->id }})"
                                                 wire:confirm="Apakah Anda yakin ingin menghapus jurnal terposting {{ $journal->entry_number }} secara permanen?"
                                                 title="Hapus Jurnal Terposting {{ $journal->entry_number }}"
                                                 class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-rose-500/10 hover:text-rose-600 dark:hover:text-rose-400 hover:border-rose-500/30 shadow-2xs transition-all duration-200">
                                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                 </svg>
                                             </button>
                                         @endcan
                                    @else
                                        <!-- AKSI JURNAL REVERSED: Locked -->
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-slate-500/10 text-slate-500 border border-slate-500/25" title="Jurnal Reversal Dikunci">
                                            REVERSED
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                Belum ada transaksi Jurnal.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-custom-pagination :paginator="$journals" />

    <!-- MODAL DETAIL TRANSAKSI & AUDIT TRAIL -->
    @if ($showDetailModal && $selectedJournalDetail)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh]">
                <!-- Modal Header -->
                <div class="p-4 bg-slate-50 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                Detail Jurnal {{ $selectedJournalDetail->entry_number }}
                                @if ($selectedJournalDetail->status === 'draft')
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-500/10 text-amber-600 border border-amber-500/30">DRAFT</span>
                                @elseif ($selectedJournalDetail->status === 'posted')
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-500/10 text-emerald-600 border border-emerald-500/30">POSTED</span>
                                @elseif ($selectedJournalDetail->status === 'reversed')
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-rose-500/10 text-rose-600 border border-rose-500/30">REVERSED</span>
                                @endif
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                No. Bukti: {{ $selectedJournalDetail->document_number ?: '-' }} | Tanggal: {{ $selectedJournalDetail->entry_date->format('d F Y') }}
                            </p>
                        </div>
                    </div>

                    <button wire:click="closeDetailModal" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-5 space-y-4 overflow-y-auto flex-1">
                    <!-- General Description -->
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200/80 dark:border-slate-700/80">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Keterangan Utama</span>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $selectedJournalDetail->description ?: '-' }}</p>
                    </div>

                    <!-- Journal Lines Table -->
                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/80 uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-3.5 py-2.5 w-10 text-center">#</th>
                                    <th class="px-3.5 py-2.5">Kode & Nama Akun</th>
                                    <th class="px-3.5 py-2.5">Unit</th>
                                    <th class="px-3.5 py-2.5 text-right w-32">Debit (Rp)</th>
                                    <th class="px-3.5 py-2.5 text-right w-32">Kredit (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                                @foreach ($selectedJournalDetail->lines as $lIndex => $line)
                                    <tr>
                                        <td class="px-3.5 py-2.5 text-center text-slate-400 font-mono">{{ $lIndex + 1 }}</td>
                                        <td class="px-3.5 py-2.5 text-slate-800 dark:text-slate-200">
                                            <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $line->account?->code }}</span> - {{ $line->account?->name }}
                                            @if ($line->description)
                                                <p class="text-[11px] text-slate-400 italic mt-0.5">{{ $line->description }}</p>
                                            @endif
                                        </td>
                                        <td class="px-3.5 py-2.5 text-slate-600 dark:text-slate-400">
                                            {{ $line->unit ? $line->unit->code . ' - ' . $line->unit->name : 'Global' }}
                                        </td>
                                        <td class="px-3.5 py-2.5 text-right font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                            {{ number_format($line->debit, 2, ',', '.') }}
                                        </td>
                                        <td class="px-3.5 py-2.5 text-right font-mono font-bold text-purple-600 dark:text-purple-400">
                                            {{ number_format($line->credit, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-50 dark:bg-slate-800/80 font-bold border-t border-slate-200 dark:border-slate-800 text-xs">
                                <tr>
                                    <td colspan="3" class="px-3.5 py-2.5 text-right uppercase text-slate-500">Total</td>
                                    <td class="px-3.5 py-2.5 text-right font-mono text-indigo-600 dark:text-indigo-400">
                                        Rp {{ number_format($selectedJournalDetail->total_debit, 2, ',', '.') }}
                                    </td>
                                    <td class="px-3.5 py-2.5 text-right font-mono text-purple-600 dark:text-purple-400">
                                        Rp {{ number_format($selectedJournalDetail->total_credit, 2, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Audit Trail Section -->
                    <div class="p-3.5 bg-indigo-50/50 dark:bg-slate-800/80 rounded-xl border border-indigo-100 dark:border-slate-700/80 space-y-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-300 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Informasi Jejak Audit (Audit Trail Information)
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-slate-400 block text-[11px]">Diposting Oleh:</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">
                                    {{ $selectedJournalDetail->postedBy?->name ?? 'Sistem / Otomatis' }}
                                    @if ($selectedJournalDetail->postedBy?->email)
                                        ({{ $selectedJournalDetail->postedBy->email }})
                                    @endif
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Waktu Diposting:</span>
                                <span class="font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ $selectedJournalDetail->posted_at ? $selectedJournalDetail->posted_at->format('d/m/Y H:i:s') : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-3 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                    <button 
                        type="button" 
                        onclick="window.print()" 
                        class="px-3.5 py-1.5 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Cetak Bukti Jurnal
                    </button>
                    <button 
                        type="button" 
                        wire:click="closeDetailModal" 
                        class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition-all">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
