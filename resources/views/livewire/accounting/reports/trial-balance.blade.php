<div class="p-4 sm:p-5 space-y-3.5">
    <x-report-nav active="trial-balance" />

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                </svg>
                Neraca Saldo (Trial Balance)
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Laporan ringkasan mutasi dan saldo akhir seluruh akun COA. Total Debit & Kredit wajib seimbang.
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
                    @if (auth()->user()?->hasGlobalUnitAccess())
                        <option value="all">🌐 Konsolidasi (Seluruh 11 Unit)</option>
                    @endif
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->code }} - {{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- TRIAL BALANCE TABLE -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">
                Ringkasan Saldo Akun (Periode {{ $startDate }} s/d {{ $endDate }})
            </h3>

            @if ($isBalanced)
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/30 text-xs font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    BALANCED (SEIMBANG)
                </span>
            @else
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/10 text-rose-500 border border-rose-500/30 text-xs font-bold">
                    UNBALANCED (SELISIH: Rp {{ number_format(abs($totalDebit - $totalCredit), 2, ',', '.') }})
                </span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-5 py-3 w-32">Kode Akun</th>
                        <th class="px-5 py-3">Nama Akun</th>
                        <th class="px-5 py-3 w-28 text-center">Tipe Akun</th>
                        <th class="px-5 py-3 text-right w-36">Mutasi Debit (Rp)</th>
                        <th class="px-5 py-3 text-right w-36">Mutasi Kredit (Rp)</th>
                        <th class="px-5 py-3 text-right w-40">Saldo Akhir Debit (Rp)</th>
                        <th class="px-5 py-3 text-right w-40">Saldo Akhir Kredit (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($rows as $row)
                        @php
                            $acc = $row['account'];
                            $level = $acc->level ?? 1;
                            
                            $rowClass = match ($level) {
                                1 => 'bg-slate-100/90 dark:bg-slate-800/90 font-extrabold text-slate-900 dark:text-white border-t-2 border-slate-200 dark:border-slate-700',
                                2 => 'bg-slate-50/70 dark:bg-slate-800/40 font-bold text-slate-800 dark:text-slate-100',
                                3 => 'font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900',
                                default => 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 bg-white dark:bg-slate-900',
                            };
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td class="px-5 py-2.5 font-mono font-bold text-slate-800 dark:text-slate-200">
                                {{ $acc->code }}
                            </td>
                            <td class="px-5 py-2.5 text-slate-800 dark:text-slate-100" style="padding-left: {{ ($level - 1) * 1.5 + 0.75 }}rem;">
                                @if ($row['has_children'])
                                    <button wire:click="toggleAccount({{ $acc->id }})" class="inline-flex items-center gap-1.5 focus:outline-none group hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                        <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-500 transition-transform duration-200 shrink-0 {{ $row['is_expanded'] ? 'rotate-90 text-indigo-500' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        <span class="text-left">{{ $acc->name }}</span>
                                    </button>
                                @else
                                    <span class="inline-block pl-5 text-left">{{ $acc->name }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-2.5 text-center">
                                <span class="px-2 py-0.5 text-[10px] font-extrabold rounded uppercase {{ $acc->is_group ? 'bg-indigo-500/10 text-indigo-500' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                                    {{ $acc->is_group ? 'Header' : 'Detail' }}
                                </span>
                            </td>
                            <td class="px-5 py-2.5 text-right font-mono text-indigo-600 dark:text-indigo-400">
                                {{ $row['debit_mutation'] > 0.001 ? number_format($row['debit_mutation'], 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-5 py-2.5 text-right font-mono text-purple-600 dark:text-purple-400">
                                {{ $row['credit_mutation'] > 0.001 ? number_format($row['credit_mutation'], 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-5 py-2.5 text-right font-mono font-bold text-indigo-700 dark:text-indigo-300">
                                {{ abs($row['final_debit']) > 0.001 ? number_format($row['final_debit'], 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-5 py-2.5 text-right font-mono font-bold text-purple-700 dark:text-purple-300">
                                {{ abs($row['final_credit']) > 0.001 ? number_format($row['final_credit'], 2, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">
                                Tidak ada data saldo akun pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50/80 dark:bg-slate-800/40 font-bold border-t border-slate-100 dark:border-slate-800 text-sm">
                    <tr>
                        <td colspan="5" class="px-5 py-3.5 text-right uppercase tracking-wider text-slate-500">Total Keseimbangan Neraca Saldo:</td>
                        <td class="px-5 py-3.5 text-right font-mono text-indigo-600 dark:text-indigo-400">
                            Rp {{ number_format($totalDebit, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-right font-mono text-purple-600 dark:text-purple-400">
                            Rp {{ number_format($totalCredit, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
