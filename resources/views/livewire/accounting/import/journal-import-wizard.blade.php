<div class="p-6 space-y-6">
    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                Import Jurnal Transaksi Excel
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Unggah file Excel format pembukuan (.xlsx) untuk dibaca, divalidasi, dan diposting ke General Ledger.
            </p>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    @if (session()->has('message'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 rounded-2xl text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-400 rounded-2xl text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    @if (! $activeBatch)
        <!-- STEP 1: UPLOAD AREA -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="max-w-xl mx-auto space-y-4 text-center">
                <!-- IDLE UPLOAD DROPZONE -->
                <div wire:loading.remove wire:target="file" class="p-8 border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-400 rounded-2xl bg-slate-50/50 dark:bg-slate-800/40 transition-all group cursor-pointer">
                    <svg class="w-12 h-12 mx-auto text-emerald-600 dark:text-emerald-400 mb-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>

                    <label class="cursor-pointer block">
                        <span class="text-base font-bold text-emerald-600 dark:text-emerald-400 hover:underline block">
                            Pilih File Excel (.xlsx) - Konversi Otomatis
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 block mt-1.5 leading-relaxed">
                            Sistem mengonversi, mengurai rumus VLOOKUP, memetakan unit, dan memvalidasi saldo sheet <strong>'Jurnal Umum'</strong> secara otomatis.
                        </span>
                        <input type="file" wire:model.live="file" accept=".xlsx,.xls" class="hidden" aria-label="Unggah File Excel Transaksi Jurnal" />
                    </label>

                    <!-- FORMAT GUIDELINE BADGES -->
                    <div class="flex flex-wrap items-center justify-center gap-1.5 mt-4 pt-4 border-t border-slate-200 dark:border-slate-700/60 text-[11px] font-mono text-slate-500 dark:text-slate-400">
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold">M: Tanggal</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold">N: No.Bukti</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold">O: Keterangan</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold">S/Q: Akun</span>
                        <span class="px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 font-semibold">T: Debit</span>
                        <span class="px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 font-semibold">U: Kredit</span>
                    </div>
                </div>

                <!-- LOADING INDICATOR -->
                <div wire:loading wire:target="file" class="p-6 border border-emerald-500/30 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 text-xs font-bold flex items-center justify-center gap-3 animate-pulse">
                    <svg class="w-5 h-5 animate-spin text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Membaca, mengonversi, dan memvalidasi file Excel... Mohon tunggu sebentar...</span>
                </div>

                @error('file') <span class="text-xs text-rose-500 font-semibold block">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- RIWAYAT BATCH IMPOR TABLE -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 bg-slate-50/80 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Riwayat Batch Impor Excel
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar riwayat file yang telah diunggah dan diproses ke sistem.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-5 py-3.5">WAKTU IMPOR</th>
                            <th class="px-5 py-3.5 font-mono">KODE BATCH</th>
                            <th class="px-5 py-3.5">NAMA FILE</th>
                            <th class="px-5 py-3.5 text-center">TOTAL BARIS</th>
                            <th class="px-5 py-3.5 text-center">BARIS VALID</th>
                            <th class="px-5 py-3.5 text-center">STATUS</th>
                            <th class="px-5 py-3.5 text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($recentBatches as $batchItem)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    {{ $batchItem->created_at ? $batchItem->created_at->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="px-5 py-3.5 font-mono font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                                    {{ $batchItem->batch_code }}
                                </td>
                                <td class="px-5 py-3.5 font-medium text-slate-800 dark:text-slate-200">
                                    📄 {{ $batchItem->file_name }}
                                </td>
                                <td class="px-5 py-3.5 text-center font-bold font-mono text-slate-800 dark:text-slate-200">
                                    {{ number_format($batchItem->total_rows) }}
                                </td>
                                <td class="px-5 py-3.5 text-center font-mono">
                                    @if ($batchItem->error_rows > 0)
                                        <span class="text-rose-600 dark:text-rose-400 font-bold">{{ $batchItem->valid_rows }} / {{ $batchItem->total_rows }}</span>
                                    @else
                                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ $batchItem->valid_rows }} (100%)</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    @if ($batchItem->status === 'posted')
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                            ✓ POSTED
                                        </span>
                                    @elseif ($batchItem->error_rows > 0)
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                            ⚠️ HAS ERROR
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                            STAGED
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    <button 
                                        wire:click="deleteBatch({{ $batchItem->id }})"
                                        wire:confirm="Apakah Anda yakin ingin menghapus seluruh data transaksi jurnal yang diposting dari batch file ini?"
                                        aria-label="Hapus Batch Impor {{ $batchItem->batch_code }}"
                                        class="px-3 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 rounded-xl text-xs font-semibold transition-all inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Hapus Batch Impor
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400">
                                    Belum ada riwayat batch impor file Excel.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- STEP 2: TOP ACTION BAR & CONTROL BADGES -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-xl text-center">
                    <span class="text-xl font-bold font-mono text-indigo-600 dark:text-indigo-400 block leading-none">{{ number_format($activeBatch->total_rows) }}</span>
                    <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 mt-0.5 block">TRANSAKSI</span>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Pratinjau File: {{ $activeBatch->file_name }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Kode Batch: <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $activeBatch->batch_code }}</span></p>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex items-center gap-3">
                @if ($activeBatch->error_rows === 0 && $batchDifference <= 1.00)
                    <button 
                        wire:click="commitPosting"
                        wire:confirm="Apakah Anda yakin ingin memposting seluruh {{ $activeBatch->total_rows }} jurnal transaksi ini ke General Ledger?"
                        aria-label="Proses Import Sekarang"
                        class="inline-flex items-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-emerald-500/25 transition-all duration-200 gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        ⚡ Proses Import Sekarang
                    </button>
                @else
                    <button 
                        disabled
                        aria-label="Proses Import Ditahan"
                        class="inline-flex items-center px-5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 font-bold text-xs uppercase tracking-wider rounded-xl border border-slate-200 dark:border-slate-700 cursor-not-allowed gap-2">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        Proses Import Ditahan (Ada Error)
                    </button>
                @endif

                <button 
                    wire:click="resetWizard"
                    aria-label="Batal Import"
                    class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition-all">
                    ✕ Batal
                </button>
            </div>
        </div>

        <!-- STEP 3: DITEMUKAN KESALAHAN PADA DATA EXCEL (RED ALERT CARD - ONLY IF ERRORS EXIST) -->
        @if ($activeBatch->error_rows > 0)
            <div class="p-5 bg-rose-500/10 border border-rose-500/30 rounded-2xl space-y-3">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-rose-500/20 text-rose-600 dark:text-rose-400 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>

                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-rose-700 dark:text-rose-400">
                            Ditemukan Kesalahan pada Data Excel
                        </h3>
                        <p class="text-xs text-rose-600 dark:text-rose-300 mt-0.5">
                            Terdapat <strong class="font-bold">{{ number_format($activeBatch->error_rows) }} baris transaksi</strong> yang tidak valid. 
                            @if ($headerAccountErrorCount > 0)
                                Ditemukan <strong class="font-bold">{{ number_format($headerAccountErrorCount) }} transaksi</strong> menggunakan <strong class="underline">Akun Header/Grup</strong>. 
                            @endif
                            Mohon perbaiki file Excel Anda sebelum melanjutkan import.
                        </p>
                    </div>
                </div>

                <!-- EMBEDDED ERROR TABLE (SCROLLABLE) -->
                <div class="border border-rose-500/20 rounded-xl overflow-hidden bg-white dark:bg-slate-900 max-h-52 overflow-y-auto">
                    <table class="w-full text-left text-xs font-mono text-slate-600 dark:text-slate-300">
                        <thead class="bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 uppercase font-semibold border-b border-rose-500/20 sticky top-0">
                            <tr>
                                <th class="px-4 py-2.5 w-16">Baris</th>
                                <th class="px-4 py-2.5 w-36">No. Bukti</th>
                                <th class="px-4 py-2.5 w-28">Kode Akun</th>
                                <th class="px-4 py-2.5">Keterangan Baris</th>
                                <th class="px-4 py-2.5 text-right text-rose-600 dark:text-rose-400">Keterangan Error</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rose-100 dark:divide-rose-950/50">
                            @foreach ($errorRowsSummary as $errRow)
                                <tr class="hover:bg-rose-50/50 dark:hover:bg-rose-900/20 transition-colors">
                                    <td class="px-4 py-2 text-slate-400 font-bold">{{ $errRow->row_index }}</td>
                                    <td class="px-4 py-2 text-slate-700 dark:text-slate-200">{{ $errRow->document_number ?: '-' }}</td>
                                    <td class="px-4 py-2 text-slate-900 dark:text-slate-100 font-bold">{{ $errRow->raw_account_code ?: 'KOSONG' }}</td>
                                    <td class="px-4 py-2 text-slate-500 dark:text-slate-400 truncate max-w-xs">{{ $errRow->description ?: '-' }}</td>
                                    <td class="px-4 py-2 text-right text-rose-600 dark:text-rose-400 font-bold uppercase">
                                        {{ implode(' | ', $errRow->validation_messages ?? ['ERROR']) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- STEP 4: GLOBAL SUMMARY CARDS (3 CARDS - TOTAL DEBIT, TOTAL KREDIT, SELISIH) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- TOTAL DEBIT GLOBAL -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 block">TOTAL DEBIT GLOBAL</span>
                <span class="text-xl font-bold font-mono text-slate-800 dark:text-slate-100 mt-1 block">
                    Rp {{ number_format($totalBatchDebit, 2, ',', '.') }}
                </span>
            </div>

            <!-- TOTAL KREDIT GLOBAL -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 block">TOTAL KREDIT GLOBAL</span>
                <span class="text-xl font-bold font-mono text-slate-800 dark:text-slate-100 mt-1 block">
                    Rp {{ number_format($totalBatchCredit, 2, ',', '.') }}
                </span>
            </div>

            <!-- STATUS KESEIMBANGAN / SELISIH -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
                <span class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 block">STATUS KESEIMBANGAN / SELISIH</span>
                <div class="mt-1 flex items-center gap-2">
                    @if ($batchDifference <= 1.00)
                        <span class="text-base font-bold font-mono text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5" title="Selisih Rp {{ number_format($batchDifference, 2, ',', '.') }} berada dalam batas toleransi pembulatan <= Rp 1,00">
                            ✓ BALANCE (Selisih Rp {{ number_format($batchDifference, 2, ',', '.') }})
                        </span>
                    @else
                        <span class="text-base font-bold font-mono text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                            ⚠️ UNBALANCED (Selisih Rp {{ number_format($batchDifference, 2, ',', '.') }})
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- STEP 5: PREVIEW DATA TRANSAKSI TABLE WITH SEARCH & STATUS FILTERING -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 bg-slate-50/80 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                        Preview Data Transaksi
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar baris transaksi yang berhasil diekstrak dan divalidasi.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- SEARCH INPUT -->
                    <div class="relative w-full sm:w-64">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            placeholder="Cari no.bukti, akun, ket..."
                            aria-label="Cari Baris Transaksi Preview"
                            class="w-full pl-9 pr-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-200" 
                        />
                    </div>

                    <!-- STATUS FILTER SELECT -->
                    <select 
                        wire:model.live="statusFilter" 
                        aria-label="Filter Status Transaksi"
                        class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="all">Semua Status</option>
                        <option value="error">Hanya Status Error</option>
                        <option value="valid">Hanya Status Valid</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-5 py-3.5">TANGGAL</th>
                            <th class="px-5 py-3.5">NO. BUKTI</th>
                            <th class="px-5 py-3.5 font-sans">KETERANGAN</th>
                            <th class="px-5 py-3.5 font-sans">UNIT (AUTO)</th>
                            <th class="px-5 py-3.5">AKUN</th>
                            <th class="px-5 py-3.5 text-right">DEBIT</th>
                            <th class="px-5 py-3.5 text-right">KREDIT</th>
                            <th class="px-5 py-3.5 text-center">STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($rows as $row)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors @if($row->validation_status === 'error') bg-rose-50/50 dark:bg-rose-950/20 @endif">
                                <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    {{ $row->entry_date ? $row->entry_date->format('Y-m-d') : '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-slate-800 dark:text-slate-100 font-bold whitespace-nowrap">
                                    {{ $row->document_number ?: '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300 font-sans font-medium">
                                    {{ $row->description ?: '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300 font-sans font-medium whitespace-nowrap">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ $row->unit ? $row->unit->name : 'Kantor Pusat' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($row->account && ! $row->account->is_group)
                                        <span class="font-bold text-indigo-600 dark:text-indigo-400 font-mono">{{ $row->raw_account_code }}</span>
                                    @elseif ($row->account && $row->account->is_group)
                                        <div class="space-y-0.5">
                                            <span class="font-bold text-rose-600 dark:text-rose-400 font-mono">{{ $row->raw_account_code }}</span>
                                            <span class="block text-[10px] font-sans font-bold text-rose-600 dark:text-rose-500 uppercase">
                                                ✕ AKUN HEADER (TIDAK BOLEH)
                                            </span>
                                        </div>
                                    @else
                                        <div class="space-y-0.5">
                                            <span class="font-bold text-rose-600 dark:text-rose-400 font-mono">{{ $row->raw_account_code ?: 'KOSONG' }}</span>
                                            <span class="block text-[10px] font-sans font-bold text-rose-600 dark:text-rose-500 uppercase">
                                                ✕ TIDAK TERDAFTAR
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ $row->debit > 0 ? number_format($row->debit, 2, ',', '.') : '0' }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ $row->credit > 0 ? number_format($row->credit, 2, ',', '.') : '0' }}
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    @if ($row->validation_status === 'valid')
                                        <span class="text-emerald-600 dark:text-emerald-400 font-black text-sm">✓</span>
                                    @else
                                        <span class="text-rose-600 dark:text-rose-400 font-black text-sm">✕</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-400 font-sans">
                                    Tidak ada data baris transaksi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <x-custom-pagination :paginator="$rows" />
    @endif
</div>
