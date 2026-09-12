<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <span>Akuntansi</span>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('accounting.fixed-assets.index') }}" class="hover:underline">Aset Tetap</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-indigo-600 dark:text-indigo-400 font-medium">Eksekusi Penyusutan</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Mesin Eksekusi Penyusutan Bulanan
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Kalkulasi otomatis beban depresiasi garis lurus dan posting serentak ke Buku Besar (General Ledger).
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('accounting.fixed-assets.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-semibold text-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Register Aset
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if (session()->has('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <div class="font-medium">{{ session('success') }}</div>
        </div>
    @endif
    @if (session()->has('warning'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div class="font-medium">{{ session('warning') }}</div>
        </div>
    @endif

    <!-- Control Card & Execution Engine -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm p-6 relative overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
            <!-- Parameter Selection -->
            <div class="space-y-4">
                <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Parameter Eksekusi
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Periode Bulan & Tahun</label>
                        <select wire:model.live="period" 
                                class="w-full text-sm font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2.5 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach($periods as $p)
                                @php
                                    $carbonP = \Carbon\Carbon::createFromFormat('Y-m', $p);
                                @endphp
                                <option value="{{ $p }}">{{ $carbonP->translatedFormat('F Y') }} ({{ $p }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Unit Organisasi</label>
                        <select wire:model.live="unit_id" 
                                class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 px-3 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Semua Unit Kerja</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Live Status KPI -->
            <div class="lg:border-l lg:border-r border-slate-100 dark:border-slate-800/80 lg:px-6 space-y-4">
                <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Status Aset Periode Ini
                </h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40">
                        <div class="text-xs text-indigo-700 dark:text-indigo-400 font-medium">Siap Disusutkan</div>
                        <div class="text-2xl font-bold font-mono text-indigo-900 dark:text-indigo-200 mt-1">
                            {{ $pendingAssets->count() }} <span class="text-xs font-normal text-indigo-500">Aset</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40">
                        <div class="text-xs text-emerald-700 dark:text-emerald-400 font-medium">Beban Akan Dibukukan</div>
                        <div class="text-xl font-bold font-mono text-emerald-900 dark:text-emerald-200 mt-1">
                            Rp {{ number_format($totalPendingAmount, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <div class="text-xs text-slate-500 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Jurnal Penyesuaian (JPEN) otomatis dibuat berpasangan & seimbang (Debit Beban, Kredit Akumulasi).</span>
                </div>
            </div>

            <!-- Action Button -->
            <div class="flex flex-col items-center justify-center text-center p-4">
                @if($pendingAssets->count() > 0)
                    @can('assets.depreciate')
                        <button wire:click="openConfirmModal" 
                                wire:loading.attr="disabled"
                                class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-sm shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/35 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Jalankan Penyusutan & Posting Jurnal
                        </button>
                        <p class="text-xs text-slate-400 mt-2">
                            Idempoten: Aman dari dobel depresiasi.
                        </p>
                    @endcan
                @else
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-center w-full">
                        <svg class="w-8 h-8 mx-auto text-emerald-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <div class="text-xs font-semibold text-slate-700 dark:text-slate-300">Periode Ini Sudah Selesai</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Tidak ada aset yang perlu disusutkan untuk {{ $period }}.</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Preview Table of Pending Assets -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Pratinjau Aset yang Siap Disusutkan (Periode {{ $period }})
            </h3>
            <span class="text-xs text-slate-500 font-mono">{{ $pendingAssets->count() }} item ditemukan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/60 text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">Kode & Nama Aset</th>
                        <th class="py-3 px-4">Kategori & Unit</th>
                        <th class="py-3 px-4 text-right">Nilai Buku Saat Ini</th>
                        <th class="py-3 px-4 text-right text-indigo-600 dark:text-indigo-400">Beban Susut Bln Ini</th>
                        <th class="py-3 px-4 text-right">Nilai Buku Sesudah</th>
                        <th class="py-3 px-4">Akun Jurnal (Debet / Kredit)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($pendingAssets as $asset)
                        @php
                            $remaining = max(0, (float) $asset->book_value - (float) $asset->salvage_value);
                            $monthAmount = min((float) $asset->monthly_depreciation_amount, $remaining);
                            $afterBookValue = max((float) $asset->salvage_value, (float) $asset->book_value - $monthAmount);
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $asset->name }}</div>
                                <div class="font-mono text-xs text-indigo-600 dark:text-indigo-400">{{ $asset->asset_code }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $asset->category->name }}</span>
                                <div class="text-[11px] text-slate-400">{{ $asset->unit->name }}</div>
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-white">
                                Rp {{ number_format($asset->book_value, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                Rp {{ number_format($monthAmount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($afterBookValue, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-xs">
                                <div class="text-slate-700 dark:text-slate-300">
                                    <span class="font-semibold text-emerald-600">D:</span> {{ $asset->category->depreciationExpenseAccount?->code }} - {{ $asset->category->depreciationExpenseAccount?->name }}
                                </div>
                                <div class="text-slate-700 dark:text-slate-300 mt-0.5">
                                    <span class="font-semibold text-amber-600">K:</span> {{ $asset->category->accumulatedDepreciationAccount?->code }} - {{ $asset->category->accumulatedDepreciationAccount?->name }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Tidak ada aset yang siap disusutkan untuk parameter periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Depreciation History Section -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Riwayat Eksekusi Penyusutan Bulanan
            </h3>
            <span class="text-xs text-slate-400">Log transaksi jurnal penyesuaian yang telah dibukukan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/60 text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">Periode</th>
                        <th class="py-3 px-4">Voucher Jurnal</th>
                        <th class="py-3 px-4">Tanggal Posting</th>
                        <th class="py-3 px-4 text-center">Jml Aset</th>
                        <th class="py-3 px-4 text-right">Total Beban</th>
                        <th class="py-3 px-4">Eksekutor</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($historyRuns as $run)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $run->period }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-mono text-xs font-semibold text-slate-900 dark:text-white">
                                    {{ $run->journalEntry?->entry_number ?? '-' }}
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    {{ $run->journalEntry?->document_number }}
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-400 text-xs">
                                {{ $run->posted_at ? \Carbon\Carbon::parse($run->posted_at)->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-medium">
                                {{ $run->total_assets }} item
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($run->total_depreciation, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-600 dark:text-slate-400">
                                {{ $run->postedBy?->name ?? 'System' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300">
                                    ✓ Terposting
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Belum ada riwayat eksekusi penyusutan yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Execution Confirmation Modal -->
    @if($showConfirmModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200"
             wire:click.self="$set('showConfirmModal', false)"
             aria-labelledby="modal-confirm" role="dialog" aria-modal="true">
            <div class="relative bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl w-full max-w-lg border border-slate-200 dark:border-slate-800 my-8">
                <div class="p-6">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-xl bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Konfirmasi Eksekusi Penyusutan</h3>
                            <p class="text-xs text-slate-500">Periode: {{ $period }}</p>
                        </div>
                    </div>

                    <div class="mt-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Jumlah Aset:</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $pendingAssets->count() }} Aset</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Total Nilai Beban:</span>
                            <span class="font-bold font-mono text-emerald-600 dark:text-emerald-400">Rp {{ number_format($totalPendingAmount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Jenis Jurnal:</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">JPEN (Jurnal Penyesuaian)</span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-3">
                        Sistem akan secara otomatis mendebit akun Beban Penyusutan dan mengkredit akun Akumulasi Penyusutan pada Buku Besar untuk masing-masing unit kerja.
                    </p>
                </div>

                <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex justify-end gap-3">
                    <button type="button" wire:click="$set('showConfirmModal', false)" 
                            class="px-4 py-2 text-sm font-semibold rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        Batal
                    </button>
                    <button type="button" wire:click="executeDepreciation" 
                            wire:loading.attr="disabled"
                            class="px-5 py-2 text-sm font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-500/25 transition-all flex items-center gap-2">
                        <span wire:loading wire:target="executeDepreciation" class="inline-block animate-spin">⟳</span>
                        Ya, Jalankan & Posting Jurnal
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
