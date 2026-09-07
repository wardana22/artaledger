<div class="p-4 sm:p-5 space-y-3.5">
    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-6 h-6 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Laporan Arus Kas (Cash Flow Statement)
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Laporan arus kas metode langsung (Aktivitas Operasi, Investasi, dan Pembiayaan).
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button 
                wire:click="openManageModal" 
                type="button" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 hover:bg-cyan-500/20 border border-cyan-500/30 transition-all shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Kelola Baris & Rumus
            </button>

            <x-report-export-dropdown 
                :pdfUrl="route('accounting.reports.export.pdf', ['type' => 'cash-flow', 'start_date' => $startDate, 'end_date' => $endDate, 'unit' => $unitFilter])"
                :excelUrl="route('accounting.reports.export.excel', ['type' => 'cash-flow', 'start_date' => $startDate, 'end_date' => $endDate, 'unit' => $unitFilter])"
            />
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-3.5 rounded-xl shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end max-w-3xl">
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Dari Tanggal</label>
                <input wire:model.live="startDate" type="date" aria-label="Dari Tanggal" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm font-medium dark:text-slate-100 focus:ring-2 focus:ring-cyan-500 transition-all" />
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Sampai Tanggal</label>
                <input wire:model.live="endDate" type="date" aria-label="Sampai Tanggal" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm font-medium dark:text-slate-100 focus:ring-2 focus:ring-cyan-500 transition-all" />
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Unit Perusahaan</label>
                <select wire:model.live="unitFilter" aria-label="Unit Perusahaan" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm font-semibold focus:ring-2 focus:ring-cyan-500 dark:text-slate-100 transition-all">
                    @if (auth()->user()?->hasGlobalUnitAccess())
                        <option value="all">🌐 Konsolidasi (Semua Unit)</option>
                    @endif
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->code }} - {{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGE -->
    @if (session()->has('success'))
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 rounded-xl text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- MAIN STATEMENT CARD -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">
        <!-- REPORT TITLE BANNER -->
        <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/40 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <span class="text-xs font-black uppercase tracking-wider text-cyan-600 dark:text-cyan-400">Laporan Arus Kas</span>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">
                    Periode {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}
                </h2>
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Klik pada baris untuk melihat akun pembentuk nilai (drilldown).
            </div>
        </div>

        <!-- TABLE SECTION -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-3 w-3/4">URAIAN / KETERANGAN</th>
                        <th class="px-6 py-3 w-1/4 text-right">REALISASI (RP)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    <!-- ================= SECTION A: OPERASI ================= -->
                    <tr class="bg-slate-100/90 dark:bg-slate-800/90 font-extrabold text-slate-900 dark:text-white">
                        <td colspan="2" class="px-6 py-3 uppercase tracking-wider text-xs">
                            A. ARUS KAS DARI KEGIATAN OPERASI
                        </td>
                    </tr>
                    @forelse ($sections['operating']['rows'] as $row)
                        @php
                            $isExpanded = in_array($row['id'], $expandedRows);
                            $val = $row['value'];
                            $isZero = abs($val) < 0.005;
                            $isNegative = $val < 0;
                        @endphp
                        <tr 
                            wire:click="toggleRow({{ $row['id'] }})"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer transition-colors select-none group {{ $isExpanded ? 'bg-cyan-50/40 dark:bg-cyan-950/20' : '' }}">
                            <td class="px-6 py-2.5 pl-10 flex items-center justify-between gap-2">
                                <span class="text-slate-700 dark:text-slate-200 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 font-medium">
                                    {{ $row['label'] }}
                                </span>
                                <span class="text-slate-400 group-hover:text-cyan-500 transition-transform duration-200 {{ $isExpanded ? 'rotate-90 text-cyan-500' : '' }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </span>
                            </td>
                            <td class="px-6 py-2.5 font-mono text-right font-semibold {{ $isZero ? 'text-slate-400 dark:text-slate-500' : ($isNegative ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-100') }}">
                                @if ($isZero)
                                    -
                                @elseif ($isNegative)
                                    ({{ number_format(abs($val), 2, ',', '.') }})
                                @else
                                    {{ number_format($val, 2, ',', '.') }}
                                @endif
                            </td>
                        </tr>

                        <!-- ACCORDION DRILLDOWN DRAWER -->
                        @if ($isExpanded)
                            <tr class="bg-slate-50/80 dark:bg-slate-950/50">
                                <td colspan="2" class="px-10 py-3">
                                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-3 shadow-inner space-y-2">
                                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                                Akun Pembentuk Nilai: <span class="text-cyan-600 dark:text-cyan-400">{{ $row['label'] }}</span>
                                            </span>
                                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 font-mono text-slate-600 dark:text-slate-300">
                                                {{ count($rowBreakdowns[$row['id']] ?? []) }} Akun Terlibat
                                            </span>
                                        </div>

                                        @if (!empty($rowBreakdowns[$row['id']]))
                                            <div class="overflow-x-auto">
                                                <table class="w-full text-left text-[11px]">
                                                    <thead class="text-slate-400 uppercase font-semibold border-b border-slate-100 dark:border-slate-800">
                                                        <tr>
                                                            <th class="py-1 px-2">Kode Akun</th>
                                                            <th class="py-1 px-2">Nama Akun</th>
                                                            <th class="py-1 px-2 text-right">Debit (Rp)</th>
                                                            <th class="py-1 px-2 text-right">Kredit (Rp)</th>
                                                            <th class="py-1 px-2 text-right">Kontribusi Bersih (Rp)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40">
                                                        @foreach ($rowBreakdowns[$row['id']] as $accItem)
                                                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 font-mono">
                                                                <td class="py-1 px-2 font-bold text-slate-800 dark:text-slate-200">{{ $accItem['code'] }}</td>
                                                                <td class="py-1 px-2 font-sans text-slate-600 dark:text-slate-300">{{ $accItem['name'] }}</td>
                                                                <td class="py-1 px-2 text-right text-slate-600 dark:text-slate-400">{{ number_format($accItem['debit'], 2, ',', '.') }}</td>
                                                                <td class="py-1 px-2 text-right text-slate-600 dark:text-slate-400">{{ number_format($accItem['credit'], 2, ',', '.') }}</td>
                                                                <td class="py-1 px-2 text-right font-bold {{ $accItem['net'] < 0 ? 'text-rose-500' : 'text-emerald-500' }}">
                                                                    {{ $accItem['net'] < 0 ? '('.number_format(abs($accItem['net']), 2, ',', '.').')' : number_format($accItem['net'], 2, ',', '.') }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-xs text-slate-400 italic py-1">Tidak ada mutasi akun jurnal pada periode ini.</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="2" class="px-10 py-2.5 text-slate-400 italic text-center">Belum ada baris arus kas operasi yang dikonfigurasi.</td>
                        </tr>
                    @endforelse

                    <!-- SUBTOTAL OPERASI -->
                    <tr class="bg-slate-50/80 dark:bg-slate-800/40 font-bold border-t border-slate-200 dark:border-slate-700">
                        <td class="px-6 py-2.5 text-slate-800 dark:text-slate-200 uppercase tracking-wide">
                            Jumlah Arus Kas dari Kegiatan Operasi
                        </td>
                        <td class="px-6 py-2.5 font-mono text-right font-extrabold {{ $totalOperating < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">
                            @if (abs($totalOperating) < 0.005)
                                -
                            @elseif ($totalOperating < 0)
                                ({{ number_format(abs($totalOperating), 2, ',', '.') }})
                            @else
                                {{ number_format($totalOperating, 2, ',', '.') }}
                            @endif
                        </td>
                    </tr>

                    <!-- ================= SECTION B: INVESTASI ================= -->
                    <tr class="bg-slate-100/90 dark:bg-slate-800/90 font-extrabold text-slate-900 dark:text-white">
                        <td colspan="2" class="px-6 py-3 uppercase tracking-wider text-xs">
                            B. ARUS KAS UNTUK KEGIATAN INVESTASI
                        </td>
                    </tr>
                    @forelse ($sections['investing']['rows'] as $row)
                        @php
                            $isExpanded = in_array($row['id'], $expandedRows);
                            $val = $row['value'];
                            $isZero = abs($val) < 0.005;
                            $isNegative = $val < 0;
                        @endphp
                        <tr 
                            wire:click="toggleRow({{ $row['id'] }})"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer transition-colors select-none group {{ $isExpanded ? 'bg-cyan-50/40 dark:bg-cyan-950/20' : '' }}">
                            <td class="px-6 py-2.5 pl-10 flex items-center justify-between gap-2">
                                <span class="text-slate-700 dark:text-slate-200 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 font-medium">
                                    {{ $row['label'] }}
                                </span>
                                <span class="text-slate-400 group-hover:text-cyan-500 transition-transform duration-200 {{ $isExpanded ? 'rotate-90 text-cyan-500' : '' }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </span>
                            </td>
                            <td class="px-6 py-2.5 font-mono text-right font-semibold {{ $isZero ? 'text-slate-400 dark:text-slate-500' : ($isNegative ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-100') }}">
                                @if ($isZero)
                                    -
                                @elseif ($isNegative)
                                    ({{ number_format(abs($val), 2, ',', '.') }})
                                @else
                                    {{ number_format($val, 2, ',', '.') }}
                                @endif
                            </td>
                        </tr>

                        <!-- ACCORDION DRILLDOWN DRAWER -->
                        @if ($isExpanded)
                            <tr class="bg-slate-50/80 dark:bg-slate-950/50">
                                <td colspan="2" class="px-10 py-3">
                                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-3 shadow-inner space-y-2">
                                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                                Akun Pembentuk Nilai: <span class="text-cyan-600 dark:text-cyan-400">{{ $row['label'] }}</span>
                                            </span>
                                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 font-mono text-slate-600 dark:text-slate-300">
                                                {{ count($rowBreakdowns[$row['id']] ?? []) }} Akun Terlibat
                                            </span>
                                        </div>

                                        @if (!empty($rowBreakdowns[$row['id']]))
                                            <div class="overflow-x-auto">
                                                <table class="w-full text-left text-[11px]">
                                                    <thead class="text-slate-400 uppercase font-semibold border-b border-slate-100 dark:border-slate-800">
                                                        <tr>
                                                            <th class="py-1 px-2">Kode Akun</th>
                                                            <th class="py-1 px-2">Nama Akun</th>
                                                            <th class="py-1 px-2 text-right">Debit (Rp)</th>
                                                            <th class="py-1 px-2 text-right">Kredit (Rp)</th>
                                                            <th class="py-1 px-2 text-right">Kontribusi Bersih (Rp)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40">
                                                        @foreach ($rowBreakdowns[$row['id']] as $accItem)
                                                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 font-mono">
                                                                <td class="py-1 px-2 font-bold text-slate-800 dark:text-slate-200">{{ $accItem['code'] }}</td>
                                                                <td class="py-1 px-2 font-sans text-slate-600 dark:text-slate-300">{{ $accItem['name'] }}</td>
                                                                <td class="py-1 px-2 text-right text-slate-600 dark:text-slate-400">{{ number_format($accItem['debit'], 2, ',', '.') }}</td>
                                                                <td class="py-1 px-2 text-right text-slate-600 dark:text-slate-400">{{ number_format($accItem['credit'], 2, ',', '.') }}</td>
                                                                <td class="py-1 px-2 text-right font-bold {{ $accItem['net'] < 0 ? 'text-rose-500' : 'text-emerald-500' }}">
                                                                    {{ $accItem['net'] < 0 ? '('.number_format(abs($accItem['net']), 2, ',', '.').')' : number_format($accItem['net'], 2, ',', '.') }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-xs text-slate-400 italic py-1">Tidak ada mutasi akun jurnal pada periode ini.</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="2" class="px-10 py-2.5 text-slate-400 italic text-center">Belum ada baris arus kas investasi yang dikonfigurasi.</td>
                        </tr>
                    @endforelse

                    <!-- SUBTOTAL INVESTASI -->
                    <tr class="bg-slate-50/80 dark:bg-slate-800/40 font-bold border-t border-slate-200 dark:border-slate-700">
                        <td class="px-6 py-2.5 text-slate-800 dark:text-slate-200 uppercase tracking-wide">
                            Jumlah Arus Kas untuk Kegiatan Investasi
                        </td>
                        <td class="px-6 py-2.5 font-mono text-right font-extrabold {{ $totalInvesting < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">
                            @if (abs($totalInvesting) < 0.005)
                                -
                            @elseif ($totalInvesting < 0)
                                ({{ number_format(abs($totalInvesting), 2, ',', '.') }})
                            @else
                                {{ number_format($totalInvesting, 2, ',', '.') }}
                            @endif
                        </td>
                    </tr>

                    <!-- ================= SECTION C: PEMBIAYAAN ================= -->
                    <tr class="bg-slate-100/90 dark:bg-slate-800/90 font-extrabold text-slate-900 dark:text-white">
                        <td colspan="2" class="px-6 py-3 uppercase tracking-wider text-xs">
                            C. ARUS KAS PEMBIAYAAN
                        </td>
                    </tr>
                    @forelse ($sections['financing']['rows'] as $row)
                        @php
                            $isExpanded = in_array($row['id'], $expandedRows);
                            $val = $row['value'];
                            $isZero = abs($val) < 0.005;
                            $isNegative = $val < 0;
                        @endphp
                        <tr 
                            wire:click="toggleRow({{ $row['id'] }})"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer transition-colors select-none group {{ $isExpanded ? 'bg-cyan-50/40 dark:bg-cyan-950/20' : '' }}">
                            <td class="px-6 py-2.5 pl-10 flex items-center justify-between gap-2">
                                <span class="text-slate-700 dark:text-slate-200 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 font-medium">
                                    {{ $row['label'] }}
                                </span>
                                <span class="text-slate-400 group-hover:text-cyan-500 transition-transform duration-200 {{ $isExpanded ? 'rotate-90 text-cyan-500' : '' }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </span>
                            </td>
                            <td class="px-6 py-2.5 font-mono text-right font-semibold {{ $isZero ? 'text-slate-400 dark:text-slate-500' : ($isNegative ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-100') }}">
                                @if ($isZero)
                                    -
                                @elseif ($isNegative)
                                    ({{ number_format(abs($val), 2, ',', '.') }})
                                @else
                                    {{ number_format($val, 2, ',', '.') }}
                                @endif
                            </td>
                        </tr>

                        <!-- ACCORDION DRILLDOWN DRAWER -->
                        @if ($isExpanded)
                            <tr class="bg-slate-50/80 dark:bg-slate-950/50">
                                <td colspan="2" class="px-10 py-3">
                                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-3 shadow-inner space-y-2">
                                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                                Akun Pembentuk Nilai: <span class="text-cyan-600 dark:text-cyan-400">{{ $row['label'] }}</span>
                                            </span>
                                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 font-mono text-slate-600 dark:text-slate-300">
                                                {{ count($rowBreakdowns[$row['id']] ?? []) }} Akun Terlibat
                                            </span>
                                        </div>

                                        @if (!empty($rowBreakdowns[$row['id']]))
                                            <div class="overflow-x-auto">
                                                <table class="w-full text-left text-[11px]">
                                                    <thead class="text-slate-400 uppercase font-semibold border-b border-slate-100 dark:border-slate-800">
                                                        <tr>
                                                            <th class="py-1 px-2">Kode Akun</th>
                                                            <th class="py-1 px-2">Nama Akun</th>
                                                            <th class="py-1 px-2 text-right">Debit (Rp)</th>
                                                            <th class="py-1 px-2 text-right">Kredit (Rp)</th>
                                                            <th class="py-1 px-2 text-right">Kontribusi Bersih (Rp)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40">
                                                        @foreach ($rowBreakdowns[$row['id']] as $accItem)
                                                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 font-mono">
                                                                <td class="py-1 px-2 font-bold text-slate-800 dark:text-slate-200">{{ $accItem['code'] }}</td>
                                                                <td class="py-1 px-2 font-sans text-slate-600 dark:text-slate-300">{{ $accItem['name'] }}</td>
                                                                <td class="py-1 px-2 text-right text-slate-600 dark:text-slate-400">{{ number_format($accItem['debit'], 2, ',', '.') }}</td>
                                                                <td class="py-1 px-2 text-right text-slate-600 dark:text-slate-400">{{ number_format($accItem['credit'], 2, ',', '.') }}</td>
                                                                <td class="py-1 px-2 text-right font-bold {{ $accItem['net'] < 0 ? 'text-rose-500' : 'text-emerald-500' }}">
                                                                    {{ $accItem['net'] < 0 ? '('.number_format(abs($accItem['net']), 2, ',', '.').')' : number_format($accItem['net'], 2, ',', '.') }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-xs text-slate-400 italic py-1">Tidak ada mutasi akun jurnal pada periode ini.</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="2" class="px-10 py-2.5 text-slate-400 italic text-center">Belum ada baris arus kas pembiayaan yang dikonfigurasi.</td>
                        </tr>
                    @endforelse

                    <!-- SUBTOTAL PEMBIAYAAN -->
                    <tr class="bg-slate-50/80 dark:bg-slate-800/40 font-bold border-t border-slate-200 dark:border-slate-700">
                        <td class="px-6 py-2.5 text-slate-800 dark:text-slate-200 uppercase tracking-wide">
                            Jumlah Arus Kas Pembiayaan
                        </td>
                        <td class="px-6 py-2.5 font-mono text-right font-extrabold {{ $totalFinancing < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">
                            @if (abs($totalFinancing) < 0.005)
                                -
                            @elseif ($totalFinancing < 0)
                                ({{ number_format(abs($totalFinancing), 2, ',', '.') }})
                            @else
                                {{ number_format($totalFinancing, 2, ',', '.') }}
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- SUMMARY RECONCILIATION CARDS -->
        <div class="p-6 bg-slate-50/50 dark:bg-slate-900/50 space-y-3">
            <div class="flex items-center justify-between p-3.5 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 shadow-xs">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                        KENAIKAN BERSIH KAS (A + B + C)
                    </span>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Total mutasi bersih arus kas aktivitas selama periode berjalan.</p>
                </div>
                <span class="font-mono text-base font-black {{ $netCashFlow < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                    @if (abs($netCashFlow) < 0.005)
                        Rp 0,00
                    @elseif ($netCashFlow < 0)
                        (Rp {{ number_format(abs($netCashFlow), 2, ',', '.') }})
                    @else
                        Rp {{ number_format($netCashFlow, 2, ',', '.') }}
                    @endif
                </span>
            </div>

            <div class="flex items-center justify-between p-3.5 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 shadow-xs">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                        Saldo Kas Awal Periode
                    </span>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Total kas & setara kas per awal tanggal periode ({{ $startDate }}).</p>
                </div>
                <span class="font-mono text-base font-black text-cyan-600 dark:text-cyan-400">
                    Rp {{ number_format($openingCash, 2, ',', '.') }}
                </span>
            </div>

            <!-- GLOWING FINAL ENDING CASH CARD -->
            <div class="p-5 rounded-2xl bg-gradient-to-r from-cyan-500/10 via-cyan-500/5 to-indigo-500/10 dark:from-cyan-950/40 dark:via-slate-900 dark:to-indigo-950/40 border-2 border-cyan-500/40 dark:border-cyan-500/50 shadow-xl shadow-cyan-500/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30">
                        Posisi Likuiditas Akhir
                    </span>
                    <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white mt-1.5 uppercase tracking-wide">
                        SALDO KAS, AKHIR PERIODE
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Akumulasi Saldo Kas Awal ditambah Kenaikan Bersih Kas periode ini.
                    </p>
                </div>
                <div class="font-mono text-2xl sm:text-3xl font-black text-cyan-600 dark:text-cyan-400 tracking-tight">
                    Rp {{ number_format($endingCash, 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL KELOLA BARIS ARUS KAS ================= -->
    @if ($showManageModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in duration-150">
                <!-- Modal Header -->
                <div class="p-5 bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <svg class="w-5 h-5 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                            Pengaturan Format & Baris Arus Kas (Direct Method)
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Konfigurasi akun pembentuk, grup akun, atau rumus kustom per baris aktivitas.
                        </p>
                    </div>

                    <button 
                        wire:click="closeManageModal" 
                        type="button" 
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body (List of Rows by Section) -->
                <div class="p-5 overflow-y-auto space-y-6 flex-1 text-xs">
                    @foreach (['operating' => 'Aktivitas Operasi', 'investing' => 'Aktivitas Investasi', 'financing' => 'Aktivitas Pembiayaan'] as $secKey => $secTitle)
                        @php
                            $secRows = $allRows->where('section', $secKey);
                        @endphp
                        <div class="space-y-2">
                            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-1.5">
                                <span class="font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                                    {{ $secTitle }} ({{ $secRows->count() }})
                                </span>
                                <button 
                                    wire:click="createRow('{{ $secKey }}')" 
                                    type="button" 
                                    class="px-2.5 py-1 text-[11px] font-bold text-cyan-600 dark:text-cyan-400 bg-cyan-500/10 hover:bg-cyan-500/20 border border-cyan-500/30 rounded-lg transition-all flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Tambah Baris
                                </button>
                            </div>

                            <div class="divide-y divide-slate-100 dark:divide-slate-800 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-slate-50/40 dark:bg-slate-800/30">
                                @forelse ($secRows as $r)
                                    <div class="p-3 flex items-center justify-between gap-3 hover:bg-white dark:hover:bg-slate-800/80 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <!-- Move Up / Down -->
                                            <div class="flex flex-col gap-0.5">
                                                <button 
                                                    wire:click="moveRowUp({{ $r->id }})" 
                                                    type="button" 
                                                    title="Naikkan Urutan" 
                                                    class="text-slate-400 hover:text-cyan-500 p-0.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                    </svg>
                                                </button>
                                                <button 
                                                    wire:click="moveRowDown({{ $r->id }})" 
                                                    type="button" 
                                                    title="Turunkan Urutan" 
                                                    class="text-slate-400 hover:text-cyan-500 p-0.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-slate-800 dark:text-slate-100 text-xs">{{ $r->label }}</span>
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $r->operator_sign === '+' ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-600 border border-rose-500/20' }}">
                                                        Tanda {{ $r->operator_sign }}
                                                    </span>
                                                </div>
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-0.5">
                                                    <span class="capitalize">Sumber: {{ str_replace('_', ' ', $r->source_type) }}</span>
                                                    <span>•</span>
                                                    @if ($r->source_type === 'account_group')
                                                        <span class="text-indigo-600 dark:text-indigo-400 font-medium">Grup: {{ $r->accountGroup?->name ?? 'None' }}</span>
                                                    @elseif ($r->source_type === 'account')
                                                        <span class="text-indigo-600 dark:text-indigo-400 font-medium">Akun: {{ $r->account?->code }} - {{ $r->account?->name }}</span>
                                                    @elseif ($r->source_type === 'formula')
                                                        <span class="font-mono text-cyan-600 dark:text-cyan-400">{{ $r->formula_expression }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-1.5">
                                            <button 
                                                wire:click="editRow({{ $r->id }})" 
                                                type="button" 
                                                class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-semibold transition-colors">
                                                Edit
                                            </button>
                                            <button 
                                                wire:click="deleteRow({{ $r->id }})" 
                                                wire:confirm="Yakin ingin menghapus baris arus kas '{{ $r->label }}'?" 
                                                type="button" 
                                                class="px-2.5 py-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 rounded-lg text-xs font-semibold transition-colors">
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-3 text-center text-slate-400 italic">Belum ada baris pada bagian ini.</div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                    <button 
                        wire:click="closeManageModal" 
                        type="button" 
                        class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 font-bold text-xs rounded-xl transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ================= SUBMODAL FORM EDIT/CREATE ROW ================= -->
    @if ($showRowFormModal)
        <div class="fixed inset-0 z-60 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-150">
                <div class="p-5 bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                        {{ $editingRowId ? 'Edit Baris Arus Kas' : 'Tambah Baris Arus Kas Baru' }}
                    </h3>
                    <button 
                        wire:click="$set('showRowFormModal', false)" 
                        type="button" 
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveRow" class="p-5 space-y-4 text-xs">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Bagian Aktivitas</label>
                        <select wire:model="row_section" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 dark:text-slate-100">
                            <option value="operating">A. Kegiatan Operasi</option>
                            <option value="investing">B. Kegiatan Investasi</option>
                            <option value="financing">C. Kegiatan Pembiayaan</option>
                        </select>
                        @error('row_section') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Nama / Label Baris</label>
                        <input wire:model="row_label" type="text" placeholder="Contoh: Penerimaan Kas dari Pelanggan" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 dark:text-slate-100" />
                        @error('row_label') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Tipe Sumber</label>
                            <select wire:model.live="row_source_type" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 dark:text-slate-100">
                                <option value="account_group">Grup Akun COA</option>
                                <option value="account">Akun Spesifik</option>
                                <option value="formula">Rumus Formula Kustom</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Tanda Operasi</label>
                            <select wire:model="row_operator_sign" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 dark:text-slate-100">
                                <option value="+">+ Penambah (Inflow / Positif)</option>
                                <option value="-">- Pengurang (Outflow / Negatif)</option>
                            </select>
                        </div>
                    </div>

                    @if ($row_source_type === 'account_group')
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Pilih Grup Akun COA</label>
                            <select wire:model="row_account_group_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 dark:text-slate-100">
                                <option value="">-- Pilih Grup Akun --</option>
                                @foreach ($accountGroups as $ag)
                                    <option value="{{ $ag->id }}">{{ $ag->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-slate-400 mt-1">Kelola atau buat grup akun baru di submenu "Grup Akun COA".</p>
                        </div>
                    @elseif ($row_source_type === 'account')
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Pilih Akun Spesifik</label>
                            <select wire:model="row_account_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 dark:text-slate-100">
                                <option value="">-- Pilih Akun --</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @elseif ($row_source_type === 'formula')
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Ekspresi Rumus</label>
                            <input wire:model="row_formula_expression" type="text" placeholder="Contoh: [GROUP:1] - [GROUP:2]" class="w-full px-3 py-2 font-mono bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 dark:text-slate-100" />
                            <p class="text-[10px] text-slate-400 mt-1">
                                Format tag: <code class="bg-slate-100 dark:bg-slate-800 px-1 py-0.5 rounded">[GROUP:id]</code> atau <code class="bg-slate-100 dark:bg-slate-800 px-1 py-0.5 rounded">[ACCOUNT:id]</code> dengan operator +, -, *, /.
                            </p>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Kalkulasi Mutasi</label>
                            <select wire:model="row_calculation_type" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 dark:text-slate-100">
                                <option value="net_mutation">Mutasi Bersih (Normal Balance)</option>
                                <option value="debit_only">Debit Saja (Mutasi Masuk)</option>
                                <option value="credit_only">Kredit Saja (Mutasi Keluar)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Urutan Baris</label>
                            <input wire:model="row_order_index" type="number" min="1" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 dark:text-slate-100" />
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
                        <button 
                            type="button" 
                            wire:click="$set('showRowFormModal', false)" 
                            class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl transition-colors">
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white font-bold rounded-xl shadow-md shadow-cyan-500/20 transition-all">
                            Simpan Baris
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
