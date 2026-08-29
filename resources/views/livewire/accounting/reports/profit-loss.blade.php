<div class="p-4 sm:p-5 space-y-3.5">
    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                Laporan Laba Rugi (Income Statement)
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Laporan posisi Keuangan Laba Rugi Audited per periode dengan susunan 4-kolom rincian dan total hirarkis.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Laporan Keuangan Official (Audited Excel)
            </span>
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
                <select wire:model.live="unitFilter" aria-label="Filter Unit Perusahaan" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm font-semibold focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 transition-all">
                    <option value="all">🌐 Konsolidasi (Seluruh Unit)</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->code }} - {{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- MAIN EXCEL AUDITED 4-COLUMN TABLE CARD -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <!-- DARK NAVY AUDITED EXCEL HEADER -->
                <thead class="bg-slate-900 text-slate-100 font-extrabold uppercase tracking-wider text-xs border-b-2 border-slate-800">
                    <tr>
                        <th class="px-5 py-4 w-36 font-mono border-r border-slate-800">KODE AKUN</th>
                        <th class="px-5 py-4 border-r border-slate-800">NAMA AKUN</th>
                        <th class="px-5 py-4 text-right w-48 border-r border-slate-800">RINCIAN (Rp)</th>
                        <th class="px-5 py-4 text-right w-52">TOTAL (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-200">
                    @forelse ($rows as $index => $row)
                        @php
                            $acc = $row['account'];
                            $level = $row['level'];
                            $paddingLeft = match ($level) {
                                1 => 'pl-4',
                                2 => 'pl-8',
                                3 => 'pl-12',
                                4 => 'pl-16',
                                default => 'pl-20',
                            };
                            $isLevel1 = $level === 1;
                            $isLevel2 = $level === 2;
                            $isLevel3 = $level === 3;
                            $isHeader = $row['has_children'];

                            $currPrefix = substr($acc->code, 0, 1);
                            $nextRow = $rows[$index + 1] ?? null;
                            $nextPrefix = $nextRow ? substr($nextRow['account']->code, 0, 1) : null;
                        @endphp

                        <tr class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40 
                            {{ $isLevel1 ? 'bg-slate-900/90 dark:bg-slate-900 text-white font-extrabold text-sm' : '' }}
                            {{ $isLevel2 ? 'bg-slate-100/70 dark:bg-slate-800/50 font-bold text-slate-900 dark:text-slate-100' : '' }}
                            {{ $isLevel3 ? 'font-semibold text-slate-800 dark:text-slate-200' : '' }}">
                            
                            <!-- KODE AKUN -->
                            <td class="px-5 py-3 font-mono border-r border-slate-200 dark:border-slate-800/60">
                                <span class="px-2 py-0.5 text-xs font-mono font-bold rounded-md 
                                    {{ $isLevel1 ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20' }}">
                                    {{ $acc->code }}
                                </span>
                            </td>

                            <!-- NAMA AKUN WITH CHEVRON TOGGLE -->
                            <td class="px-5 py-3 border-r border-slate-200 dark:border-slate-800/60 {{ $paddingLeft }}">
                                <div class="flex items-center gap-2">
                                    @if ($row['has_children'])
                                        <button 
                                            wire:click="toggleAccount({{ $acc->id }})" 
                                            type="button" 
                                            aria-label="Toggle Account {{ $acc->code }}"
                                            class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors focus:outline-none">
                                            <svg class="w-4 h-4 transform transition-transform duration-200 {{ $row['is_expanded'] ? 'rotate-90 text-indigo-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </button>
                                    @else
                                        <span class="w-4 h-4 inline-block"></span>
                                    @endif

                                    <span class="{{ $isHeader ? 'uppercase tracking-wide font-extrabold' : 'font-medium' }}">
                                        {{ $acc->name }}
                                    </span>
                                </div>
                            </td>

                            <!-- RINCIAN (Rp) - LEVEL 4 LEAF POSTING ACCOUNTS -->
                            <td class="px-5 py-3 text-right font-mono font-semibold border-r border-slate-200 dark:border-slate-800/60">
                                @if (! $row['has_children'] && $row['rincian'] !== null)
                                    <span>{{ number_format($row['rincian'], 2, ',', '.') }}</span>
                                @else
                                    <span class="text-slate-300 dark:text-slate-700">-</span>
                                @endif
                            </td>

                            <!-- TOTAL (Rp) - GROUP HEADERS & LEVEL 1/2/3 TOTALS -->
                            <td class="px-5 py-3 text-right font-mono font-bold">
                                @if ($row['has_children'] && $row['total'] !== null)
                                    <span class="{{ $isLevel1 ? 'text-indigo-300 font-extrabold' : '' }}">
                                        {{ number_format($row['total'], 2, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-slate-300 dark:text-slate-700">-</span>
                                @endif
                            </td>
                        </tr>

                        {{-- TOTAL BANNER INLINE SETELAH AKUN 4 DAN ANAK-ANAKNYA --}}
                        @if ($currPrefix === '4' && $nextPrefix !== '4')
                            <tr class="bg-slate-950/80 border-y border-slate-800/80">
                                <td colspan="4" class="p-3">
                                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-800/60 border border-slate-700/50">
                                        <span class="text-xs font-extrabold uppercase tracking-wider text-slate-300">TOTAL PENDAPATAN USAHA</span>
                                        <span class="text-base font-mono font-extrabold text-emerald-400">Rp {{ number_format($totalRevenue, 2, ',', '.') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @elseif ($currPrefix === '5' && $nextPrefix !== '5')
                            <tr class="bg-slate-950/80 border-y border-slate-800/80">
                                <td colspan="4" class="p-3 space-y-3">
                                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-800/60 border border-slate-700/50">
                                        <span class="text-xs font-extrabold uppercase tracking-wider text-slate-300">TOTAL BEBAN POKOK PENDAPATAN (HPP)</span>
                                        <span class="text-base font-mono font-extrabold text-rose-400">Rp {{ number_format($totalHpp, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between p-4 rounded-2xl bg-indigo-950/80 border border-indigo-500/30 shadow-md">
                                        <div>
                                            <span class="text-xs font-black uppercase tracking-widest text-indigo-300 block">LABA / RUGI KOTOR (GROSS PROFIT)</span>
                                            <span class="text-xs text-slate-400 block mt-0.5">Pendapatan Usaha dikurangi Beban Pokok Pendapatan (HPP)</span>
                                        </div>
                                        <span class="text-xl font-mono font-black {{ $grossProfit >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                            Rp {{ number_format($grossProfit, 2, ',', '.') }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @elseif ($currPrefix === '6' && $nextPrefix !== '6')
                            <tr class="bg-slate-950/80 border-y border-slate-800/80">
                                <td colspan="4" class="p-3 space-y-3">
                                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-800/60 border border-slate-700/50">
                                        <span class="text-xs font-extrabold uppercase tracking-wider text-slate-300">TOTAL BEBAN OPERASIONAL & ADMINISTRASI</span>
                                        <span class="text-base font-mono font-extrabold text-rose-400">Rp {{ number_format($totalOperatingExpenses, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between p-4 rounded-2xl bg-blue-950/80 border border-blue-500/30 shadow-md">
                                        <div>
                                            <span class="text-xs font-black uppercase tracking-widest text-blue-300 block">LABA / RUGI OPERASIONAL (OPERATING PROFIT)</span>
                                            <span class="text-xs text-slate-400 block mt-0.5">Laba Kotor dikurangi Total Beban Operasional</span>
                                        </div>
                                        <span class="text-xl font-mono font-black {{ $operatingProfit >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                            Rp {{ number_format($operatingProfit, 2, ',', '.') }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @elseif (($currPrefix === '7' || $currPrefix === '8') && ($nextPrefix !== '7' && $nextPrefix !== '8'))
                            <tr class="bg-slate-950/80 border-y border-slate-800/80">
                                <td colspan="4" class="p-3 space-y-3">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-800/40 border border-slate-700/40">
                                            <span class="font-semibold text-slate-400">Pendapatan Non-Operasional:</span>
                                            <span class="font-mono font-bold text-emerald-400">Rp {{ number_format($otherRevenue, 2, ',', '.') }}</span>
                                        </div>
                                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-800/40 border border-slate-700/40">
                                            <span class="font-semibold text-slate-400">Beban Non-Operasional:</span>
                                            <span class="font-mono font-bold text-rose-400">Rp {{ number_format($otherExpense, 2, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-800 border border-slate-700 shadow-md">
                                        <span class="text-xs font-black uppercase tracking-widest text-slate-200">LABA / RUGI SEBELUM PAJAK (PROFIT BEFORE TAX)</span>
                                        <span class="text-xl font-mono font-black {{ $profitBeforeTax >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                            Rp {{ number_format($profitBeforeTax, 2, ',', '.') }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @elseif ($currPrefix === '9' && $nextPrefix !== '9')
                            <tr class="bg-slate-950/80 border-y border-slate-800/80">
                                <td colspan="4" class="p-3">
                                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-800/60 border border-slate-700/50">
                                        <span class="text-xs font-extrabold uppercase tracking-wider text-slate-300">BEBAN PAJAK PENGHASILAN (INCOME TAX)</span>
                                        <span class="text-base font-mono font-extrabold text-rose-400">Rp {{ number_format($taxExpense, 2, ',', '.') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-400 italic">
                                Tidak ada data Laporan Laba Rugi ditemukan untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- FINANCIAL SUMMARY SECTION BANNERS -->
        <div class="bg-slate-900 border-t-2 border-slate-800 p-6 space-y-4 text-slate-100 font-mono">
            <!-- FINAL LABA BERSIH PERIODE BERJALAN (NET PROFIT) GLOWING CARD -->
            <div class="p-6 rounded-3xl bg-gradient-to-r {{ $netProfit >= 0 ? 'from-emerald-950 via-slate-900 to-indigo-950 border-2 border-emerald-500/50 shadow-2xl shadow-emerald-500/10' : 'from-rose-950 via-slate-900 to-amber-950 border-2 border-rose-500/50 shadow-2xl shadow-rose-500/10' }} flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="px-3 py-1 rounded-full text-xs font-black tracking-widest uppercase {{ $netProfit >= 0 ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30' }}">
                        {{ $netProfit >= 0 ? '🟢 LABA BERSIH (NET PROFIT)' : '🔴 RUGI BERSIH (NET LOSS)' }}
                    </span>
                    <h3 class="text-lg font-black text-white mt-2">
                        LABA (RUGI) BERSIH PERIODE BERJALAN
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Hasil akhir akumulasi Laporan Laba Rugi Audited per tanggal {{ $startDate }} s/d {{ $endDate }}
                    </p>
                </div>

                <div class="text-right font-mono text-3xl font-black tracking-tight {{ $netProfit >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                    Rp {{ number_format($netProfit, 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>
