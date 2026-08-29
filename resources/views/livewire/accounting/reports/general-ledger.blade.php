<div class="p-4 sm:p-5 space-y-3.5">
    <x-ledger-nav active="general-ledger" />

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                Buku Besar (General Ledger - Akun Header)
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Laporan konsolidasi mutasi & saldo berjalan (*running balance*) untuk <strong>Akun Header (Group)</strong> yang menghimpun seluruh sub-akun di bawahnya.
            </p>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-3.5 rounded-xl shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                    Dari Tanggal
                </label>
                <input wire:model.live="startDate" type="date" aria-label="Dari Tanggal" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm font-medium dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 transition-all" />
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                    Sampai Tanggal
                </label>
                <input wire:model.live="endDate" type="date" aria-label="Sampai Tanggal" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm font-medium dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 transition-all" />
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                    Unit Perusahaan
                </label>
                <select wire:model.live="unitFilter" aria-label="Unit Perusahaan" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm font-semibold focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 transition-all">
                    <option value="all">🌐 Konsolidasi (Seluruh 11 Unit)</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->code }} - {{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                    Pilih Akun Header (Group) *
                </label>
                <select wire:model.live="selectedAccountId" aria-label="Pilih Akun Header" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm font-mono focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 transition-all">
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- LEDGER TABLE -->
    @if ($selectedAccount)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">
                        {{ $selectedAccount->code }} - {{ $selectedAccount->name }}
                    </h3>
                    <p class="text-xs text-slate-400">
                        Saldo Normal: <span class="uppercase font-bold text-indigo-500">{{ $selectedAccount->normal_balance }}</span> | Tipe: {{ $selectedAccount->type ?? '-' }} | Menghimpun <strong>{{ $childAccountsCount }}</strong> sub-akun
                    </p>
                </div>

                <span class="px-3 py-1 text-xs font-bold rounded-full bg-indigo-500/10 text-indigo-500 border border-indigo-500/25">
                    AKUN HEADER (GROUP)
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-3 w-28">Tanggal</th>
                            <th class="px-5 py-3 w-32">No Jurnal</th>
                            <th class="px-5 py-3 w-40">Sub Akun Detail</th>
                            <th class="px-5 py-3">Keterangan</th>
                            <th class="px-5 py-3 text-right w-36">Debit (Rp)</th>
                            <th class="px-5 py-3 text-right w-36">Kredit (Rp)</th>
                            <th class="px-5 py-3 text-right w-40">Saldo Akhir (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <!-- Saldo Awal Row -->
                        <tr class="bg-slate-50/40 dark:bg-slate-800/20 font-semibold">
                            <td class="px-5 py-3 font-mono">{{ $startDate }}</td>
                            <td class="px-5 py-3 font-mono text-slate-400">-</td>
                            <td class="px-5 py-3 font-mono text-slate-400">-</td>
                            <td class="px-5 py-3 italic text-slate-500">SALDO AWAL KONSOLIDASI PERIODE</td>
                            <td class="px-5 py-3 text-right text-slate-400">-</td>
                            <td class="px-5 py-3 text-right text-slate-400">-</td>
                            <td class="px-5 py-3 text-right font-mono font-bold text-slate-800 dark:text-slate-100">
                                {{ number_format($openingBalance, 2, ',', '.') }}
                            </td>
                        </tr>

                        @php
                            $runningBalance = $openingBalance;
                            $totalDebit = 0;
                            $totalCredit = 0;
                        @endphp

                        @foreach ($lines as $line)
                            @php
                                $debit = (float) $line->debit;
                                $credit = (float) $line->credit;
                                $totalDebit += $debit;
                                $totalCredit += $credit;

                                if ($selectedAccount->normal_balance === 'debit') {
                                    $runningBalance += ($debit - $credit);
                                } else {
                                    $runningBalance += ($credit - $debit);
                                }
                            @endphp
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-5 py-3 font-mono whitespace-nowrap">{{ $line->journalEntry->entry_date->format('Y-m-d') }}</td>
                                <td class="px-5 py-3 font-mono font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                                    {{ $line->journalEntry->entry_number }}
                                </td>
                                <td class="px-5 py-3 font-mono font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $line->account->code ?? '-' }}
                                </td>
                                <td class="px-5 py-3 text-slate-700 dark:text-slate-200">
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">[{{ $line->account->name ?? '' }}]</span>
                                    {{ $line->description ?: $line->journalEntry->description }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono text-indigo-600 dark:text-indigo-400 font-semibold whitespace-nowrap">
                                    {{ $debit > 0 ? number_format($debit, 2, ',', '.') : '-' }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono text-purple-600 dark:text-purple-400 font-semibold whitespace-nowrap">
                                    {{ $credit > 0 ? number_format($credit, 2, ',', '.') : '-' }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                    {{ number_format($runningBalance, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50/80 dark:bg-slate-800/40 font-bold border-t border-slate-100 dark:border-slate-800">
                        <tr>
                            <td colspan="4" class="px-5 py-3 text-right uppercase tracking-wider text-slate-500">Total Mutasi & Saldo Akhir:</td>
                            <td class="px-5 py-3 text-right font-mono text-indigo-600 dark:text-indigo-400">
                                Rp {{ number_format($totalDebit, 2, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-purple-600 dark:text-purple-400">
                                Rp {{ number_format($totalCredit, 2, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-emerald-600 dark:text-emerald-400 text-sm">
                                Rp {{ number_format($runningBalance, 2, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</div>
