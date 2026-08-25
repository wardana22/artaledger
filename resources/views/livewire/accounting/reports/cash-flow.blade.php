<div class="p-6 space-y-6">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-7 h-7 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Laporan Arus Kas (Cash Flow Statement)
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Laporan pergerakan penerimaan dan pengeluaran Kas & Bank.
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

    <!-- CASH FLOW SUMMARY CARD -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">
        <div class="p-6 bg-slate-50/80 dark:bg-slate-800/40 flex items-center justify-between font-bold text-sm">
            <span class="uppercase text-slate-700 dark:text-slate-200">1. SALDO KAS & BANK AWAL PERIODE:</span>
            <span class="font-mono text-cyan-600 dark:text-cyan-400 text-base">
                Rp {{ number_format($openingCash, 2, ',', '.') }}
            </span>
        </div>

        <div class="p-6 space-y-4">
            <h3 class="text-base font-extrabold uppercase tracking-wide text-slate-800 dark:text-slate-100 border-b border-slate-200 dark:border-slate-700 pb-2">
                2. ARUS KAS DARI AKTIVITAS OPERASIONAL
            </h3>

            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-600 dark:text-slate-300">Penerimaan Kas / Bank (Mutasi Masuk / Debit Kas)</span>
                    <span class="font-mono font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($operatingIn, 2, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600 dark:text-slate-300">Pengeluaran Kas / Bank (Mutasi Keluar / Kredit Kas)</span>
                    <span class="font-mono font-semibold text-rose-600 dark:text-rose-400">(Rp {{ number_format($operatingOut, 2, ',', '.') }})</span>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between font-bold text-xs uppercase text-slate-700 dark:text-slate-200">
                <span>Kenaikan / (Penurunan) Kas Bersih Operasional:</span>
                <span class="font-mono text-sm {{ $netOperatingCash >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                    Rp {{ number_format($netOperatingCash, 2, ',', '.') }}
                </span>
            </div>
        </div>

        <div class="p-6 bg-cyan-500/10 flex items-center justify-between font-bold text-base">
            <span class="uppercase text-slate-900 dark:text-white">SALDO KAS & BANK AKHIR PERIODE:</span>
            <span class="font-mono text-cyan-600 dark:text-cyan-400 text-xl font-black">
                Rp {{ number_format($endingCash, 2, ',', '.') }}
            </span>
        </div>
    </div>
</div>
