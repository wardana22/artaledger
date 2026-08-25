<div class="p-6 space-y-6">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M13 12h.01M13 16h.01M17 12h.01M17 16h.01"></path>
                </svg>
                Laporan Perubahan Ekuitas / Modal
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Laporan pergerakan perubahan modal disetor & laba ditahan selama periode berjalan.
            </p>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl">
        <div>
            <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Unit Perusahaan</label>
            <select wire:model.live="unitFilter" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                <option value="all">🌐 Konsolidasi (Seluruh 11 Unit)</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->code }} - {{ $unit->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Dari Tanggal</label>
            <input wire:model.live="startDate" type="date" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm dark:text-slate-100" />
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Sampai Tanggal</label>
            <input wire:model.live="endDate" type="date" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm dark:text-slate-100" />
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
