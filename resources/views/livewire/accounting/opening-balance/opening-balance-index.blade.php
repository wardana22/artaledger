<div class="p-6 space-y-6">
    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                Laporan Saldo Awal Akuntansi
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Laporan rincian posisi Saldo Awal Akuntansi per periode bulan yang terposting pada General Ledger.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Laporan Keuangan Official
            </span>
        </div>
    </div>

    <!-- SUMMARY CARDS (TOTAL DEBIT, TOTAL KREDIT, STATUS BALANCE) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- TOTAL DEBIT -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 block">TOTAL DEBIT SALDO AWAL</span>
            <span class="text-xl font-bold font-mono text-slate-800 dark:text-slate-100 mt-1 block">
                Rp {{ number_format($totalDebit, 2, ',', '.') }}
            </span>
        </div>

        <!-- TOTAL KREDIT -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 block">TOTAL KREDIT SALDO AWAL</span>
            <span class="text-xl font-bold font-mono text-slate-800 dark:text-slate-100 mt-1 block">
                Rp {{ number_format($totalCredit, 2, ',', '.') }}
            </span>
        </div>

        <!-- STATUS KESEIMBANGAN -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 block">STATUS KESEIMBANGAN</span>
            <div class="mt-1 flex items-center gap-2">
                @if ($batchDifference <= 1.00)
                    <span class="text-base font-bold font-mono text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5" title="Selisih Rp {{ number_format($batchDifference, 2, ',', '.') }} berada dalam batas toleransi pembulatan">
                        ✓ BALANCE (Selisih Rp {{ number_format($batchDifference, 2, ',', '.') }})
                    </span>
                @else
                    <span class="text-base font-bold font-mono text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                        ⚠️ UNBALANCED (Selisih Rp {{ number_format($batchDifference, 2, ',', '.') }})
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- FILTER BAR & DATA TABLE -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50/80 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <span>Laporan Saldo Awal</span>
                    @if ($selectedPeriod)
                        <span class="px-2 py-0.5 text-xs font-mono font-bold rounded-md bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                            {{ $selectedPeriod->name }}
                        </span>
                    @endif
                </h3>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- PERIOD SELECTOR -->
                <select 
                    wire:model.live="periodId" 
                    aria-label="Pilih Periode Akuntansi"
                    class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach ($periods as $p)
                        <option value="{{ $p->id }}">📅 {{ $p->name }}</option>
                    @endforeach
                </select>

                <!-- SEARCH INPUT -->
                <div class="relative w-full sm:w-56">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari kode/nama akun..."
                        aria-label="Cari Baris Saldo Awal"
                        class="w-full pl-9 pr-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-200" 
                    />
                </div>

                <!-- UNIT FILTER SELECT -->
                <select 
                    wire:model.live="unitFilter" 
                    aria-label="Filter Unit Perusahaan"
                    class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @if (auth()->user()?->hasGlobalUnitAccess())
                        <option value="all">Semua Unit Perusahaan</option>
                    @endif
                    @foreach ($units as $u)
                        <option value="{{ $u->id }}">{{ $u->code }} - {{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5 w-12 text-center">#</th>
                        <th class="px-5 py-3.5 w-36 font-mono">KODE AKUN</th>
                        <th class="px-5 py-3.5">NAMA AKUN</th>
                        <th class="px-5 py-3.5 w-28 text-center">NORMAL</th>
                        <th class="px-5 py-3.5 text-right w-44">DEBIT</th>
                        <th class="px-5 py-3.5 text-right w-44">KREDIT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($lines as $index => $line)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-5 py-3.5 text-center text-slate-400">
                                {{ $lines->firstItem() + $index }}
                            </td>
                            <td class="px-5 py-3.5 font-mono">
                                <span class="px-2 py-0.5 text-xs font-extrabold rounded-md bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                    {{ $line->account ? $line->account->code : '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 font-bold text-slate-800 dark:text-slate-100">
                                {{ $line->account ? $line->account->name : '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if ($line->account?->normal_balance === 'debit')
                                    <span class="px-2.5 py-0.5 text-xs font-black font-mono rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20" title="Normal Balance: Debit">
                                        D
                                    </span>
                                @elseif ($line->account?->normal_balance === 'credit')
                                    <span class="px-2.5 py-0.5 text-xs font-black font-mono rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20" title="Normal Balance: Kredit">
                                        K
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 text-xs font-mono rounded-md bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700">
                                        -
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                                {{ $line->debit != 0 ? number_format($line->debit, 2, ',', '.') : '0' }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                                {{ $line->credit != 0 ? number_format($line->credit, 2, ',', '.') : '0' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                Tidak ada data baris saldo awal ditemukan untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100/90 dark:bg-slate-800/90 font-bold border-t-2 border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                    <tr>
                        <td colspan="4" class="px-5 py-4 text-right uppercase tracking-wider text-xs">
                            TOTAL SALDO AWAL PERIODE INI:
                        </td>
                        <td class="px-5 py-4 text-right font-mono text-sm text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($totalDebit, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-right font-mono text-sm text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($totalCredit, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if ($lines && method_exists($lines, 'links'))
        <x-custom-pagination :paginator="$lines" />
    @endif
</div>
