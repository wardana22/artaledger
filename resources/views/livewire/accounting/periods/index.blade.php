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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($periods as $period)
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-3 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono font-bold text-slate-400">
                        {{ $period->year }} / {{ sprintf('%02d', $period->month) }}
                    </span>
                    
                    @if ($period->status === 'open')
                        <span class="px-2.5 py-0.5 text-[11px] font-extrabold rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/25">
                            🟢 OPEN
                        </span>
                    @elseif ($period->status === 'closed')
                        <span class="px-2.5 py-0.5 text-[11px] font-extrabold rounded-full bg-amber-500/10 text-amber-500 border border-amber-500/25">
                            🟡 CLOSED (TETAP)
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 text-[11px] font-extrabold rounded-full bg-rose-500/10 text-rose-500 border border-rose-500/25">
                            🔴 LOCKED (TERKUNCI TOTAL)
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

                    @if ($period->closed_at)
                        <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 space-y-0.5 font-mono">
                            <span class="block">Ditutup: {{ $period->closed_at->format('d/m/Y H:i') }} {{ $period->closedBy ? 'oleh ' . $period->closedBy->name : '' }}</span>
                        </div>
                    @endif

                    @if ($period->status !== 'open' && $isSuperAdmin)
                        <div class="mt-2.5 pt-2 border-t border-dashed border-slate-200 dark:border-slate-800">
                            <button 
                                type="button"
                                wire:click="toggleRevealKey({{ $period->id }})"
                                class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                                {{ $revealedKeyPeriodId === $period->id ? 'Sembunyikan Kunci' : '🔑 Lihat Lock Key (SuperAdmin)' }}
                            </button>

                            @if ($revealedKeyPeriodId === $period->id)
                                <div class="mt-1.5 p-2 bg-indigo-50 dark:bg-slate-800 border border-indigo-200 dark:border-indigo-500/30 rounded-lg text-xs font-mono font-bold text-indigo-700 dark:text-indigo-300 select-all">
                                    {{ $period->lock_key ?: 'Belum di-generate' }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- PERIOD ACTION BUTTONS -->
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-1.5 flex-wrap">
                    @if ($period->status === 'open')
                        <button 
                            wire:click="closePeriod({{ $period->id }})"
                            wire:confirm="Apakah Anda yakin ingin MENUTUP periode {{ $period->name }}? Sistem akan secara otomatis meng-generate Kunci Keamanan Rahasia (Lock Key)."
                            class="px-3 py-1.5 bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/30 rounded-lg text-xs font-bold transition-all flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            🔒 Tutup Periode
                        </button>
                    @else
                        @if ($period->status === 'closed')
                            <button 
                                wire:click="lockPeriod({{ $period->id }})"
                                wire:confirm="Apakah Anda yakin ingin MENGUNCI TOTAL (LOCKED) periode ini?"
                                class="px-2.5 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 rounded-lg text-xs font-bold transition-all">
                                🔐 Lock Total
                            </button>
                        @endif

                        <button 
                            wire:click="openReopenModal({{ $period->id }})"
                            class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all flex items-center gap-1 shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                            </svg>
                            🔓 Buka Periode
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full p-12 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl text-center text-slate-400">
                Belum ada periode akuntansi untuk tahun {{ $selectedYear }}. Klik <strong>"Generate 12 Bulan"</strong> untuk membuatnya.
            </div>
        @endforelse
    </div>

    <!-- MODAL PEMBUKAAN KEMBALI PERIODE BERBASIS LOCK KEY -->
    @if ($showReopenModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="p-4 bg-indigo-50/50 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        Otentikasi Kunci Rahasia Pembukaan Periode
                    </h3>
                    <button wire:click="$set('showReopenModal', false)" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="confirmReopenPeriod" class="p-5 space-y-4">
                    <div class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-500/30 rounded-xl text-xs text-amber-800 dark:text-amber-300 space-y-1">
                        <span class="font-bold block">⚠️ Peringatan Keamanan Audit:</span>
                        <p>Kunci Keamanan Rahasia (Lock Key) hanya dapat diperoleh dari **Super Admin / Auditor Kepala**. Membuka kembali periode ditutup wajib disertai alasan resmi.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Kunci Keamanan Rahasia (Lock Key) *</label>
                        <input 
                            wire:model="inputLockKey" 
                            type="text" 
                            placeholder="misal: LOCK-202608-X892F1" 
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 focus:ring-2 focus:ring-indigo-500" 
                            required 
                        />
                        @error('inputLockKey') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Alasan Pembukaan Kembali (Audit Reason) *</label>
                        <textarea 
                            wire:model="reopenReason" 
                            rows="3" 
                            placeholder="Jelaskan alasan resmi pembukaan kembali periode ini..." 
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs dark:text-slate-100 focus:ring-2 focus:ring-indigo-500" 
                            required></textarea>
                        @error('reopenReason') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                        <button 
                            type="button" 
                            wire:click="$set('showReopenModal', false)" 
                            class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg">
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-md shadow-indigo-500/20 transition-all">
                            Konfirmasi & Buka Periode
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
