<div class="p-6 space-y-6">
    <x-journal-nav />

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Jurnal Umum (General Journal)
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Daftar transaksi Jurnal Umum berstatus Posted dan Reversal pada ArtaLedger.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a 
                href="{{ route('accounting.journals.create') }}"
                wire:navigate
                class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-medium text-sm rounded-xl shadow-lg shadow-indigo-500/25 transition-all duration-200 gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

    <!-- Search & Filter -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                <input 
                    wire:model.live.debounce.300ms="search" 
                    type="text" 
                    placeholder="Cari nomor jurnal, no bukti, deskripsi..." 
                    class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-200"
                />
            </div>

            <select wire:model.live="statusFilter" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-200">
                <option value="all">Semua Status</option>
                <option value="posted">Posted</option>
                <option value="draft">Draft</option>
                <option value="reversed">Reversed</option>
            </select>
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
                                @if ($journal->status === 'posted')
                                    @if ($journal->source_type === 'reversal' || str_starts_with($journal->document_number ?? '', 'REV-') || str_starts_with($journal->description ?? '', 'REVERSAL:'))
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-slate-200 dark:bg-slate-800 text-slate-500 border border-slate-300 dark:border-slate-700" title="Jurnal Reversal Dikunci (Tidak dapat di-reverse kembali)">
                                            REVERSAL
                                        </span>
                                    @else
                                        <button 
                                            wire:click="reverseJournal({{ $journal->id }})"
                                            wire:confirm="Apakah Anda yakin ingin membalikkan (reverse) jurnal ini?"
                                            title="Reverse Jurnal"
                                            class="px-2.5 py-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 rounded-lg text-xs font-bold transition-all">
                                            Reverse
                                        </button>
                                    @endif
                                @elseif ($journal->status === 'reversed')
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-rose-500/10 text-rose-500 border border-rose-500/25">
                                        REVERSED
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-500/10 text-amber-500 border border-amber-500/25">
                                        DRAFT
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                Belum ada transaksi Jurnal Umum. Klik <strong>"Buat Jurnal Manual"</strong> untuk membuat transaksi jurnal pertama.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-custom-pagination :paginator="$journals" />
</div>
