<div class="p-6 space-y-6">
    <x-ledger-nav active="subsidiary-ledger" />

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Buku Besar Pembantu (Subsidiary Ledger)
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Laporan rincian mutasi transaksi & saldo berjalan (*running balance*) khusus untuk <strong>Akun Detail / Posting</strong>.
            </p>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Pilih Akun Posting / Detail *</label>
            <select wire:model.live="selectedAccountId" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                @foreach ($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                @endforeach
            </select>
        </div>

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

    <!-- LEDGER TABLE -->
    @if ($selectedAccount)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">
                        {{ $selectedAccount->code }} - {{ $selectedAccount->name }}
                    </h3>
                    <p class="text-xs text-slate-400">
                        Saldo Normal: <span class="uppercase font-bold text-indigo-500">{{ $selectedAccount->normal_balance }}</span> | Tipe: {{ $selectedAccount->type ?? '-' }}
                    </p>
                </div>

                <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/25">
                    AKUN POSTING
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-3 w-28">Tanggal</th>
                            <th class="px-5 py-3 w-36">No Jurnal</th>
                            <th class="px-5 py-3 w-32">No Bukti</th>
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
                            <td class="px-5 py-3 italic text-slate-500">SALDO AWAL PERIODE</td>
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
                                <td class="px-5 py-3 font-mono whitespace-nowrap">{{ $line->journalEntry->document_number ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-700 dark:text-slate-200">
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
