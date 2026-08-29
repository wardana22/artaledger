<div class="p-4 sm:p-5 space-y-3.5">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M13 12h.01M13 16h.01M17 12h.01M17 16h.01"></path>
                </svg>
                Laporan Perubahan Ekuitas / Modal
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Laporan pergerakan perubahan modal disetor & laba ditahan selama periode berjalan.
            </p>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-3.5 rounded-xl shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end max-w-3xl">
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Dari Tanggal</label>
                <input wire:model.live="startDate" type="date" aria-label="Dari Tanggal" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm font-medium dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 transition-all" />
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Sampai Tanggal</label>
                <input wire:model.live="endDate" type="date" aria-label="Sampai Tanggal" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm font-medium dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 transition-all" />
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Unit Perusahaan</label>
                <select wire:model.live="unitFilter" aria-label="Unit Perusahaan" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm font-semibold focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 transition-all">
                    <option value="all">🌐 Konsolidasi (Seluruh 11 Unit)</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->code }} - {{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- EQUITY SUMMARY CARD -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">
        <div class="p-6 bg-slate-50/80 dark:bg-slate-800/40 flex items-center justify-between font-bold text-sm">
            <span class="uppercase text-slate-700 dark:text-slate-200">1. SALDO EKUITAS / MODAL AWAL PERIODE:</span>
            <span class="font-mono text-indigo-600 dark:text-indigo-400 text-base">
                Rp {{ number_format($initialEquity, 2, ',', '.') }}
            </span>
        </div>

        <div class="p-6 space-y-3">
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-600 dark:text-slate-300 font-medium">Penambahan Laba / (Rugi) Bersih Periode Berjalan</span>
                <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($netProfit, 2, ',', '.') }}</span>
            </div>
        </div>

        <div class="p-6 bg-indigo-500/10 flex items-center justify-between font-bold text-base">
            <span class="uppercase text-slate-900 dark:text-white">SALDO EKUITAS / MODAL AKHIR PERIODE:</span>
            <span class="font-mono text-indigo-600 dark:text-indigo-400 text-xl font-black">
                Rp {{ number_format($endingEquity, 2, ',', '.') }}
            </span>
        </div>
    </div>
</div>
