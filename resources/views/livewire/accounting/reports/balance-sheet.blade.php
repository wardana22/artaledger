<div class="p-6 space-y-6">
    <x-report-nav />

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-7 h-7 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                </svg>
                Laporan Neraca (Balance Sheet)
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Laporan Posisi Keuangan (Aset = Kewajiban + Ekuitas) per tanggal acuan.
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if ($isBalanced)
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/30 text-xs font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    NERACA SEIMBANG (BALANCED)
                </span>
            @else
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-rose-500/10 text-rose-500 border border-rose-500/30 text-xs font-bold">
                    UNBALANCED (SELISIH: Rp {{ number_format(abs($totalAssets - $totalLiabilitiesAndEquity), 2, ',', '.') }})
                </span>
            @endif
        </div>

    </div>

    <!-- FILTER BAR -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm grid grid-cols-1 md:grid-cols-2 gap-4 max-w-xl">
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
            <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Per Tanggal Acuan (As Of)</label>
            <input wire:model.live="asOfDate" type="date" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm dark:text-slate-100" />
        </div>
    </div>

    <!-- TWO COLUMN BALANCE SHEET LAYOUT -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- LEFT COLUMN: ASSETS -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="p-6 space-y-4">
                <h3 class="text-base font-extrabold uppercase tracking-wide text-indigo-600 dark:text-indigo-400 border-b border-indigo-500/20 pb-2">
                    ASET / AKTIVA (ASSETS)
                </h3>

                <table class="w-full text-xs text-slate-600 dark:text-slate-300">
                    <tbody>
                        @forelse ($assetRows as $row)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="py-2 font-mono font-bold w-28 text-slate-800 dark:text-slate-200">{{ $row['account']->code }}</td>
                                <td class="py-2 font-medium text-slate-800 dark:text-slate-100">{{ $row['account']->name }}</td>
                                <td class="py-2 text-right font-mono font-semibold text-indigo-600 dark:text-indigo-400 w-36">
                                    Rp {{ number_format($row['amount'], 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-3 text-center text-slate-400 italic">Belum ada akun aset ber-saldo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-5 bg-indigo-50/70 dark:bg-slate-800/80 border-t border-indigo-100 dark:border-slate-800 flex items-center justify-between font-bold text-sm">
                <span class="uppercase text-slate-700 dark:text-slate-200">TOTAL ASET / AKTIVA:</span>
                <span class="font-mono text-indigo-600 dark:text-indigo-400 text-base">
                    Rp {{ number_format($totalAssets, 2, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- RIGHT COLUMN: LIABILITIES & EQUITY -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="p-6 space-y-6">
                <!-- LIABILITIES -->
                <div class="space-y-3">
                    <h3 class="text-base font-extrabold uppercase tracking-wide text-amber-600 dark:text-amber-400 border-b border-amber-500/20 pb-2">
                        1. KEWAJIBAN / HUTANG (LIABILITIES)
                    </h3>

                    <table class="w-full text-xs text-slate-600 dark:text-slate-300">
                        <tbody>
                            @forelse ($liabilityRows as $row)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="py-2 font-mono font-bold w-28 text-slate-800 dark:text-slate-200">{{ $row['account']->code }}</td>
                                    <td class="py-2 font-medium text-slate-800 dark:text-slate-100">{{ $row['account']->name }}</td>
                                    <td class="py-2 text-right font-mono font-semibold text-amber-600 dark:text-amber-400 w-36">
                                        Rp {{ number_format($row['amount'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-2 text-center text-slate-400 italic">Belum ada akun kewajiban ber-saldo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="font-bold border-t border-slate-100 dark:border-slate-800">
                                <td colspan="2" class="py-2 uppercase text-slate-600">Subtotal Kewajiban:</td>
                                <td class="py-2 text-right font-mono text-amber-600">Rp {{ number_format($totalLiabilities, 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- EQUITY -->
                <div class="space-y-3">
                    <h3 class="text-base font-extrabold uppercase tracking-wide text-purple-600 dark:text-purple-400 border-b border-purple-500/20 pb-2">
                        2. EKUITAS / MODAL (EQUITY)
                    </h3>

                    <table class="w-full text-xs text-slate-600 dark:text-slate-300">
                        <tbody>
                            @foreach ($equityRows as $row)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="py-2 font-mono font-bold w-28 text-slate-800 dark:text-slate-200">{{ $row['account']->code }}</td>
                                    <td class="py-2 font-medium text-slate-800 dark:text-slate-100">{{ $row['account']->name }}</td>
                                    <td class="py-2 text-right font-mono font-semibold text-purple-600 dark:text-purple-400 w-36">
                                        Rp {{ number_format($row['amount'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                            <!-- Current Period Net Profit Row -->
                            <tr class="bg-purple-50/50 dark:bg-purple-900/10 font-bold">
                                <td class="py-2 font-mono text-purple-600 dark:text-purple-400">-</td>
                                <td class="py-2 text-purple-700 dark:text-purple-300">Laba / (Rugi) Periode Berjalan</td>
                                <td class="py-2 text-right font-mono text-purple-600 dark:text-purple-400 w-36">
                                    Rp {{ number_format($currentNetProfit, 2, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="font-bold border-t border-slate-100 dark:border-slate-800">
                                <td colspan="2" class="py-2 uppercase text-slate-600">Subtotal Ekuitas:</td>
                                <td class="py-2 text-right font-mono text-purple-600">Rp {{ number_format($totalEquity, 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="p-5 bg-purple-50/70 dark:bg-slate-800/80 border-t border-purple-100 dark:border-slate-800 flex items-center justify-between font-bold text-sm">
                <span class="uppercase text-slate-700 dark:text-slate-200">TOTAL KEWAJIBAN & EKUITAS:</span>
                <span class="font-mono text-purple-600 dark:text-purple-400 text-base">
                    Rp {{ number_format($totalLiabilitiesAndEquity, 2, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
</div>
