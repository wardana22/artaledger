<div class="p-6 space-y-6">
    <x-report-nav />

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Neraca Lajur (Worksheet)
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Lembar kerja akuntansi 10-kolom mengintegrasikan Neraca Saldo, Penyesuaian, Saldo Disesuaikan, Laba Rugi, & Neraca.
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

    <!-- WORKSHEET 10-COLUMN TABLE -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">
                Lembar Kerja Akuntansi (Periode {{ $startDate }} s/d {{ $endDate }})
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[11px] text-slate-600 dark:text-slate-300 border-collapse">
                <thead class="bg-slate-100 dark:bg-slate-800/80 uppercase font-bold text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th rowspan="2" class="px-3 py-3 border-r border-slate-200 dark:border-slate-700 w-24">Kode</th>
                        <th rowspan="2" class="px-3 py-3 border-r border-slate-200 dark:border-slate-700 min-w-[200px]">Nama Akun</th>
                        <th colspan="2" class="px-3 py-1.5 text-center border-r border-slate-200 dark:border-slate-700 bg-indigo-50/50 dark:bg-indigo-950/20">Neraca Saldo</th>
                        <th colspan="2" class="px-3 py-1.5 text-center border-r border-slate-200 dark:border-slate-700 bg-amber-50/50 dark:bg-amber-950/20">Penyesuaian</th>
                        <th colspan="2" class="px-3 py-1.5 text-center border-r border-slate-200 dark:border-slate-700 bg-blue-50/50 dark:bg-blue-950/20">NS Disesuaikan</th>
                        <th colspan="2" class="px-3 py-1.5 text-center border-r border-slate-200 dark:border-slate-700 bg-emerald-50/50 dark:bg-emerald-950/20">Laba Rugi</th>
                        <th colspan="2" class="px-3 py-1.5 text-center bg-purple-50/50 dark:bg-purple-950/20">Neraca</th>
                    </tr>
                    <tr class="text-right border-t border-slate-200 dark:border-slate-700 text-[10px]">
                        <th class="px-2 py-1.5 border-r border-slate-200 dark:border-slate-700 w-24">Debit</th>
                        <th class="px-2 py-1.5 border-r border-slate-200 dark:border-slate-700 w-24">Kredit</th>
                        <th class="px-2 py-1.5 border-r border-slate-200 dark:border-slate-700 w-24">Debit</th>
                        <th class="px-2 py-1.5 border-r border-slate-200 dark:border-slate-700 w-24">Kredit</th>
                        <th class="px-2 py-1.5 border-r border-slate-200 dark:border-slate-700 w-24">Debit</th>
                        <th class="px-2 py-1.5 border-r border-slate-200 dark:border-slate-700 w-24">Kredit</th>
                        <th class="px-2 py-1.5 border-r border-slate-200 dark:border-slate-700 w-24">Debit</th>
                        <th class="px-2 py-1.5 border-r border-slate-200 dark:border-slate-700 w-24">Kredit</th>
                        <th class="px-2 py-1.5 border-r border-slate-200 dark:border-slate-700 w-24">Debit</th>
                        <th class="px-2 py-1.5 w-24">Kredit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($rows as $row)
                        @php $acc = $row['account']; @endphp
                        <tr class="{{ $acc->is_group ? 'bg-slate-50/70 dark:bg-slate-800/30 font-bold' : 'hover:bg-slate-50 dark:hover:bg-slate-800/40' }}">
                            <td class="px-3 py-2 font-mono font-bold border-r border-slate-100 dark:border-slate-800 text-slate-800 dark:text-slate-200">{{ $acc->code }}</td>
                            <td class="px-3 py-2 border-r border-slate-100 dark:border-slate-800 text-slate-800 dark:text-slate-100" style="padding-left: {{ min(($acc->level - 1) * 0.75, 2.5) }}rem;">
                                {{ $acc->name }}
                            </td>
                            <!-- NS -->
                            <td class="px-2 py-2 text-right font-mono border-r border-slate-100 dark:border-slate-800 text-indigo-600 dark:text-indigo-400">
                                {{ abs($row['tb_debit']) > 0.001 ? number_format($row['tb_debit'], 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-2 py-2 text-right font-mono border-r border-slate-100 dark:border-slate-800 text-purple-600 dark:text-purple-400">
                                {{ abs($row['tb_credit']) > 0.001 ? number_format($row['tb_credit'], 2, ',', '.') : '-' }}
                            </td>
                            <!-- ADJ -->
                            <td class="px-2 py-2 text-right font-mono border-r border-slate-100 dark:border-slate-800 text-amber-600 dark:text-amber-400">
                                {{ abs($row['adj_debit']) > 0.001 ? number_format($row['adj_debit'], 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-2 py-2 text-right font-mono border-r border-slate-100 dark:border-slate-800 text-purple-600 dark:text-purple-400">
                                {{ abs($row['adj_credit']) > 0.001 ? number_format($row['adj_credit'], 2, ',', '.') : '-' }}
                            </td>
                            <!-- ATB -->
                            <td class="px-2 py-2 text-right font-mono border-r border-slate-100 dark:border-slate-800 font-semibold text-slate-700 dark:text-slate-300">
                                {{ abs($row['atb_debit']) > 0.001 ? number_format($row['atb_debit'], 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-2 py-2 text-right font-mono border-r border-slate-100 dark:border-slate-800 font-semibold text-slate-700 dark:text-slate-300">
                                {{ abs($row['atb_credit']) > 0.001 ? number_format($row['atb_credit'], 2, ',', '.') : '-' }}
                            </td>
                            <!-- IS -->
                            <td class="px-2 py-2 text-right font-mono border-r border-slate-100 dark:border-slate-800 text-rose-600 dark:text-rose-400 font-semibold">
                                {{ abs($row['is_debit']) > 0.001 ? number_format($row['is_debit'], 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-2 py-2 text-right font-mono border-r border-slate-100 dark:border-slate-800 text-emerald-600 dark:text-emerald-400 font-semibold">
                                {{ abs($row['is_credit']) > 0.001 ? number_format($row['is_credit'], 2, ',', '.') : '-' }}
                            </td>
                            <!-- BS -->
                            <td class="px-2 py-2 text-right font-mono border-r border-slate-100 dark:border-slate-800 text-indigo-600 dark:text-indigo-400 font-semibold">
                                {{ abs($row['bs_debit']) > 0.001 ? number_format($row['bs_debit'], 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-2 py-2 text-right font-mono text-purple-600 dark:text-purple-400 font-semibold">
                                {{ abs($row['bs_credit']) > 0.001 ? number_format($row['bs_credit'], 2, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="p-8 text-center text-slate-400">Tidak ada data saldo akun.</td>
                        </tr>
                    @endforelse
                </tbody>
                <!-- SUBTOTAL FOOTER -->
                <tfoot class="bg-slate-100 dark:bg-slate-800/80 font-bold border-t border-slate-200 dark:border-slate-700">
                    <tr>
                        <td colspan="2" class="px-3 py-2.5 text-right uppercase text-slate-600 dark:text-slate-300">Subtotal:</td>
                        <td class="px-2 py-2.5 text-right font-mono border-r border-slate-200 dark:border-slate-700 text-indigo-600">{{ number_format($totTbDebit, 2, ',', '.') }}</td>
                        <td class="px-2 py-2.5 text-right font-mono border-r border-slate-200 dark:border-slate-700 text-purple-600">{{ number_format($totTbCredit, 2, ',', '.') }}</td>
                        <td class="px-2 py-2.5 text-right font-mono border-r border-slate-200 dark:border-slate-700 text-amber-600">{{ number_format($totAdjDebit, 2, ',', '.') }}</td>
                        <td class="px-2 py-2.5 text-right font-mono border-r border-slate-200 dark:border-slate-700 text-purple-600">{{ number_format($totAdjCredit, 2, ',', '.') }}</td>
                        <td class="px-2 py-2.5 text-right font-mono border-r border-slate-200 dark:border-slate-700">{{ number_format($totAtbDebit, 2, ',', '.') }}</td>
                        <td class="px-2 py-2.5 text-right font-mono border-r border-slate-200 dark:border-slate-700">{{ number_format($totAtbCredit, 2, ',', '.') }}</td>
                        <td class="px-2 py-2.5 text-right font-mono border-r border-slate-200 dark:border-slate-700 text-rose-600">{{ number_format($totIsDebit, 2, ',', '.') }}</td>
                        <td class="px-2 py-2.5 text-right font-mono border-r border-slate-200 dark:border-slate-700 text-emerald-600">{{ number_format($totIsCredit, 2, ',', '.') }}</td>
                        <td class="px-2 py-2.5 text-right font-mono border-r border-slate-200 dark:border-slate-700 text-indigo-600">{{ number_format($totBsDebit, 2, ',', '.') }}</td>
                        <td class="px-2 py-2.5 text-right font-mono text-purple-600">{{ number_format($totBsCredit, 2, ',', '.') }}</td>
                    </tr>
                    <!-- NET PROFIT BALANCING ROW -->
                    <tr class="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-extrabold text-xs">
                        <td colspan="2" class="px-3 py-2.5 text-right uppercase">Laba / (Rugi) Bersih:</td>
                        <td colspan="6" class="border-r border-slate-200 dark:border-slate-700"></td>
                        @if ($netProfitFromIs >= 0)
                            <td class="px-2 py-2.5 text-right font-mono border-r border-slate-200 dark:border-slate-700">{{ number_format($netProfitFromIs, 2, ',', '.') }}</td>
                            <td class="px-2 py-2.5 text-right font-mono border-r border-slate-200 dark:border-slate-700">-</td>
                            <td class="px-2 py-2.5 text-right font-mono border-r border-slate-200 dark:border-slate-700">-</td>
                            <td class="px-2 py-2.5 text-right font-mono">{{ number_format($netProfitFromIs, 2, ',', '.') }}</td>
                        @else
                            <td class="px-2 py-2.5 text-right font-mono border-r border-slate-200 dark:border-slate-700">-</td>
                            <td class="px-2 py-2.5 text-right font-mono border-r border-slate-200 dark:border-slate-700">{{ number_format(abs($netProfitFromIs), 2, ',', '.') }}</td>
                            <td class="px-2 py-2.5 text-right font-mono border-r border-slate-200 dark:border-slate-700">{{ number_format(abs($netProfitFromIs), 2, ',', '.') }}</td>
                            <td class="px-2 py-2.5 text-right font-mono">-</td>
                        @endif
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
