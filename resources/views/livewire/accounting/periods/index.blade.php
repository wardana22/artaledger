<div class="p-6 space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Periode Akuntansi (Accounting Periods)
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Atur pembukaan & penutupan periode akuntansi bulanan. Posting jurnal hanya diizinkan pada periode berstatus OPEN.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <select wire:model.live="selectedYear" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                @for ($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                    <option value="{{ $y }}">Tahun {{ $y }}</option>
                @endfor
            </select>

            <button 
                wire:click="generateYearPeriods"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-xl shadow-md shadow-indigo-500/20 gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Generate 12 Bulan
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 rounded-xl text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @forelse ($periods as $period)
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-3 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono font-bold text-slate-400">
                        {{ $period->year }} / {{ sprintf('%02d', $period->month) }}
                    </span>
                    
                    @if ($period->status === 'open')
                        <span class="px-2.5 py-0.5 text-[11px] font-extrabold rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/25">
                            OPEN
                        </span>
                    @elseif ($period->status === 'closed')
                        <span class="px-2.5 py-0.5 text-[11px] font-extrabold rounded-full bg-amber-500/10 text-amber-500 border border-amber-500/25">
                            CLOSED
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 text-[11px] font-extrabold rounded-full bg-rose-500/10 text-rose-500 border border-rose-500/25">
                            LOCKED
                        </span>
                    @endif
                </div>

                <div>
                    <h4 class="text-lg font-bold text-slate-800 dark:text-slate-100">
                        {{ $period->start_date->format('F Y') }}
                    </h4>
                    <p class="text-xs text-slate-400 mt-0.5">
                        {{ $period->start_date->format('d M') }} - {{ $period->end_date->format('d M Y') }}
                    </p>
                </div>

                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-xs text-slate-400 font-medium">Klik untuk ubah status</span>
                    <button 
                        wire:click="toggleStatus({{ $period->id }})"
                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-lg transition-all">
                        Status &rarr;
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full p-12 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl text-center text-slate-400">
                Belum ada periode akuntansi untuk tahun {{ $selectedYear }}. Klik <strong>"Generate 12 Bulan"</strong> untuk membuatnya.
            </div>
        @endforelse
    </div>
</div>
