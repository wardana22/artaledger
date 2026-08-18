<div class="p-6 space-y-6">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                Laporan Laba Rugi (Profit & Loss Statement)
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Laporan akumulasi Pendapatan dikurangi Beban Operasional untuk menentukan Laba/Rugi Bersih.
            </p>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm grid grid-cols-1 md:grid-cols-2 gap-4 max-w-xl">
        <div>
            <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Dari Tanggal</label>
            <input wire:model.live="startDate" type="date" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm dark:text-slate-100" />
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Sampai Tanggal</label>
            <input wire:model.live="endDate" type="date" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm dark:text-slate-100" />
        </div>
    </div>

    <!-- REPORT CARD -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">
        <!-- REVENUE SECTION -->
        <div class="p-6 space-y-3">
            <h3 class="text-base font-extrabold uppercase tracking-wide text-emerald-600 dark:text-emerald-400 border-b border-emerald-500/20 pb-2">
                1. PENDAPATAN (REVENUE)
            </h3>

            <table class="w-full text-xs text-slate-600 dark:text-slate-300">
                <tbody>
                    @forelse ($revenueRows as $row)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="py-2 font-mono font-bold w-32 text-slate-800 dark:text-slate-200">{{ $row['account']->code }}</td>
                            <td class="py-2 font-medium text-slate-800 dark:text-slate-100">{{ $row['account']->name }}</td>
                            <td class="py-2 text-right font-mono font-semibold text-emerald-600 dark:text-emerald-400 w-44">
                                Rp {{ number_format($row['amount'], 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-3 text-center text-slate-400 italic">Belum ada pendapatan tercatat pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t border-slate-200 dark:border-slate-700 font-bold text-sm">
                        <td colspan="2" class="py-3 uppercase text-slate-700 dark:text-slate-200">TOTAL PENDAPATAN:</td>
                        <td class="py-3 text-right font-mono text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($totalRevenue, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- EXPENSE SECTION -->
        <div class="p-6 space-y-3">
            <h3 class="text-base font-extrabold uppercase tracking-wide text-rose-600 dark:text-rose-400 border-b border-rose-500/20 pb-2">
                2. BEBAN OPERASIONAL & USAHA (EXPENSES)
            </h3>

            <table class="w-full text-xs text-slate-600 dark:text-slate-300">
                <tbody>
                    @forelse ($expenseRows as $row)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="py-2 font-mono font-bold w-32 text-slate-800 dark:text-slate-200">{{ $row['account']->code }}</td>
                            <td class="py-2 font-medium text-slate-800 dark:text-slate-100">{{ $row['account']->name }}</td>
                            <td class="py-2 text-right font-mono font-semibold text-rose-600 dark:text-rose-400 w-44">
                                Rp {{ number_format($row['amount'], 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-3 text-center text-slate-400 italic">Belum ada beban tercatat pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t border-slate-200 dark:border-slate-700 font-bold text-sm">
                        <td colspan="2" class="py-3 uppercase text-slate-700 dark:text-slate-200">TOTAL BEBAN OPERASIONAL:</td>
                        <td class="py-3 text-right font-mono text-rose-600 dark:text-rose-400">
                            Rp {{ number_format($totalExpense, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- NET PROFIT SUMMARY -->
        <div class="p-6 bg-slate-50 dark:bg-slate-800/60 flex items-center justify-between">
            <div>
                <h4 class="text-lg font-extrabold text-slate-900 dark:text-white uppercase">
                    LABA / (RUGI) BERSIH PERIODE:
                </h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">Total Pendapatan dikurangi Total Beban</p>
            </div>

            <div class="text-right font-mono text-2xl font-black {{ $netProfit >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                Rp {{ number_format($netProfit, 2, ',', '.') }}
            </div>
        </div>
    </div>
</div>
