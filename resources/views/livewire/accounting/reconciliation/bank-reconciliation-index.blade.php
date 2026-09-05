<div class="p-4 sm:p-6 space-y-6">
    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100 flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/50 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                </div>
                <span>Rekonsiliasi Bank Otomatis</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Pencocokan mutasi kas/bank otomatis berbasis rekening koran resmi Cash Management System (CMS) BRI.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            @can('reconciliation.upload')
                <button 
                    wire:click="openUploadModal" 
                    type="button" 
                    class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-indigo-500/20 flex items-center gap-2 transition-all transform active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    <span>Unggah Rekening Koran (PDF)</span>
                </button>
            @endcan
        </div>
    </div>

    <!-- FLASH MESSAGE -->
    @if (session()->has('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- KPI STATS CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- TOTAL REKENING KORAN -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4.5 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Total Rekening Koran</span>
                <span class="text-2xl font-black font-mono text-slate-800 dark:text-slate-100 mt-1 block">{{ $totalStatements }}</span>
                <span class="text-[11px] text-slate-500 mt-0.5 block">File rekening terunggah</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>

        <!-- SEDANG DIREKONSILIASI -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4.5 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Dalam Proses</span>
                <span class="text-2xl font-black font-mono text-amber-600 dark:text-amber-400 mt-1 block">{{ $totalInProgress }}</span>
                <span class="text-[11px] text-slate-500 mt-0.5 block">Perlu tindakan pencocokan</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-100 dark:border-amber-900/40 flex items-center justify-center text-amber-600 dark:text-amber-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <!-- REKONSILIASI SELESAI -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4.5 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Selesai (Reconciled)</span>
                <span class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 mt-1 block">{{ $totalReconciled }}</span>
                <span class="text-[11px] text-slate-500 mt-0.5 block">100% mutasi klop</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/40 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- STATEMENTS LIST TABLE -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Daftar Rekening Koran Bank</h3>
                <p class="text-xs text-slate-500 mt-0.5">Pilih salah satu periode untuk membuka lembar kerja pencocokan interaktif.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">BANK & REKENING</th>
                        <th class="py-3 px-4">PERIODE</th>
                        <th class="py-3 px-4 text-right">TOTAL MUTASI</th>
                        <th class="py-3 px-4 text-right">SALDO AKHIR</th>
                        <th class="py-3 px-4 text-center">PROGRESS</th>
                        <th class="py-3 px-4 text-center">STATUS</th>
                        <th class="py-3 px-4 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @forelse($statements as $st)
                        @php
                            $pct = $st->getReconciledPercentage();
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 dark:text-slate-100">{{ $st->bank_name }}</div>
                                <div class="font-mono text-slate-500 text-[11px] mt-0.5">{{ $st->account_number }} ({{ $st->account_holder }})</div>
                                <div class="text-[10px] text-indigo-600 dark:text-indigo-400 font-semibold mt-0.5">Akun: {{ $st->account?->code }} - {{ $st->account?->name }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-700 dark:text-slate-300">
                                {{ \Carbon\Carbon::parse($st->period_start)->isoFormat('D MMM Y') }} s/d {{ \Carbon\Carbon::parse($st->period_end)->isoFormat('D MMM Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="font-mono font-bold text-slate-800 dark:text-slate-100">{{ $st->lines_count }} Baris</div>
                                <div class="text-[10px] text-slate-400">D: {{ number_format((float)$st->total_debit, 0, ',', '.') }} | K: {{ number_format((float)$st->total_credit, 0, ',', '.') }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                Rp {{ number_format((float)$st->closing_balance, 2, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex flex-col items-center w-28">
                                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                        <div class="h-2 rounded-full {{ $pct >= 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-[10px] font-mono font-bold text-slate-500 mt-1">{{ $pct }}% Cocok</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($st->status === 'reconciled')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                                        ✓ Reconciled
                                    </span>
                                @elseif($st->status === 'in_progress')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                        In Progress
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a 
                                        href="{{ route('accounting.reconciliation.detail', $st->id) }}" 
                                        wire:navigate 
                                        class="px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 font-bold text-xs flex items-center gap-1 transition-all">
                                        <span>Buka Rekonsiliasi</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                    @can('reconciliation.manage')
                                        <button 
                                            wire:click="deleteStatement({{ $st->id }})" 
                                            wire:confirm="Yakin ingin menghapus data rekening koran ini beserta seluruh histori pencocokannya?" 
                                            type="button" 
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-all cursor-pointer" 
                                            title="Hapus Rekening Koran">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span class="font-medium text-sm">Belum ada rekening koran yang diunggah.</span>
                                    <span class="text-xs">Klik tombol "Unggah Rekening Koran (PDF)" di atas untuk memulai pencocokan.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- UPLOAD MODAL -->
    @if ($showUploadModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity">
            <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-slate-800 dark:text-slate-100">Unggah Rekening Koran CMS BRI</h3>
                            <p class="text-[11px] text-slate-500">Ekstraksi mutasi otomatis dari berkas PDF e-Statement resmi.</p>
                        </div>
                    </div>
                    <button wire:click="closeUploadModal" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit="processUpload" class="p-6 space-y-4">
                    @if ($uploadError)
                        <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-rose-700 dark:text-rose-300 text-xs flex items-center gap-2.5">
                            <svg class="w-4 h-4 shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>{{ $uploadError }}</span>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                            Pilih Akun Bank di Pembukuan *
                        </label>
                        <select wire:model="selectedAccountId" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                            @foreach ($bankAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedAccountId') <span class="text-[11px] text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                            Berkas PDF Rekening Koran CMS *
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 dark:border-slate-700 border-dashed rounded-2xl hover:border-indigo-500 transition-colors">
                            <div class="space-y-2 text-center">
                                <svg class="mx-auto h-10 w-10 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-xs text-slate-600 dark:text-slate-400 justify-center">
                                    <label class="relative cursor-pointer rounded-md font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                        <span>Pilih berkas PDF</span>
                                        <input wire:model="pdfFile" type="file" accept="application/pdf" class="sr-only">
                                    </label>
                                    <p class="pl-1">atau seret ke sini</p>
                                </div>
                                <p class="text-[10px] text-slate-400">PDF e-Statement CMS BRI (Maks. 20MB)</p>
                            </div>
                        </div>
                        @if ($pdfFile)
                            <div class="mt-2.5 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 flex items-center justify-between text-xs">
                                <span class="font-mono text-indigo-600 dark:text-indigo-400 truncate max-w-xs">{{ $pdfFile->getClientOriginalName() }}</span>
                                <span class="text-slate-400 text-[10px]">{{ round($pdfFile->getSize() / 1024, 1) }} KB</span>
                            </div>
                        @endif
                        @error('pdfFile') <span class="text-[11px] text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                        <button wire:click="closeUploadModal" type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 flex items-center gap-2 transition-all disabled:opacity-50">
                            <span wire:loading.remove>Proses & Ekstraksi</span>
                            <span wire:loading class="flex items-center gap-2">
                                <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                Mengekstrak Data...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
