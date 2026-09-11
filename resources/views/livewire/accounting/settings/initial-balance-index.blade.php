<div class="p-4 sm:p-5 space-y-4">
    <!-- TOP NAV TABS MASTER AKUNTANSI -->
    <x-settings-nav active="initial-balance" />

    <!-- FLASH MESSAGES -->
    @if (session()->has('message'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 rounded-2xl text-xs font-medium flex items-center gap-2.5 shadow-sm">
            <svg class="w-5 h-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-400 rounded-2xl text-xs font-medium flex items-center gap-2.5 shadow-sm">
            <svg class="w-5 h-5 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- AUDIT HEADER / STATUS CARD -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Saldo Awal Perdana (Initial Cut-Off Balance)
                </h1>

                @if ($isLocked)
                    <span class="px-3 py-1 rounded-full text-xs font-bold font-mono bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        🔒 TERKUNCI RESMI ({{ $existingEntryNumber ?: 'SA-INITIAL' }})
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-bold font-mono bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                        🔓 MODE PENGISIAN / KOREKSI ({{ $existingEntryNumber ?: 'BARU' }})
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed max-w-3xl">
                Pengaturan saldo awal neraca saat pertama kali memulai pembukuan di ArtaLedger. Dirancang sekali pakai (*initial setup*) dengan proteksi gembok audit dan penyeimbang otomatis ke akun <strong>Laba Ditahan</strong>.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            @if ($isLocked)
                @if (auth()->user()?->hasRole('Super Admin'))
                    <button 
                        type="button" 
                        wire:click="openUnlockModal"
                        class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/30 text-slate-700 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400 border border-slate-200 dark:border-slate-700 hover:border-rose-300 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shadow-xs"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                        Buka Kunci Darurat (Super Admin)
                    </button>
                @endif
            @else
                <button 
                    type="button" 
                    wire:click="lockAgain"
                    class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition-all"
                >
                    Kunci Kembali
                </button>
                <button 
                    type="button" 
                    wire:click="openSaveModal"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-500/20 transition-all flex items-center gap-1.5"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Simpan & Posting Saldo Awal
                </button>
            @endif
        </div>
    </div>

    <!-- PARAMETER HEADER GRID -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5 text-xs">
            <div>
                <label class="font-semibold text-slate-700 dark:text-slate-300 block mb-1">Tanggal Cut-Off Efektif *</label>
                <input 
                    type="date" 
                    wire:model="entryDate" 
                    @disabled($isLocked)
                    class="w-full px-3 py-2 bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-lg text-xs outline-none focus:ring-2 focus:ring-indigo-500 disabled:bg-slate-50 dark:disabled:bg-slate-900/60 disabled:cursor-not-allowed font-medium"
                >
            </div>

            <div>
                <label class="font-semibold text-slate-700 dark:text-slate-300 block mb-1">Nomor Bukti / Jurnal *</label>
                <input 
                    type="text" 
                    wire:model="documentNumber" 
                    @disabled($isLocked)
                    class="w-full px-3 py-2 bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-lg text-xs outline-none focus:ring-2 focus:ring-indigo-500 disabled:bg-slate-50 dark:disabled:bg-slate-900/60 disabled:cursor-not-allowed font-mono font-bold"
                >
            </div>

            <div>
                <label class="font-semibold text-slate-700 dark:text-slate-300 block mb-1">Akun Penyeimbang (Laba Ditahan) *</label>
                <select 
                    wire:model="retainedEarningsAccountId" 
                    @disabled($isLocked)
                    class="w-full px-3 py-2 bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-lg text-xs outline-none focus:ring-2 focus:ring-indigo-500 disabled:bg-slate-50 dark:disabled:bg-slate-900/60 disabled:cursor-not-allowed font-medium"
                >
                    @if ($retainedAccount)
                        <option value="{{ $retainedAccount->id }}">{{ $retainedAccount->code }} - {{ $retainedAccount->name }}</option>
                    @else
                        <option value="">-- Pilih Akun Laba Ditahan --</option>
                    @endif
                </select>
            </div>

            <div>
                <label class="font-semibold text-slate-700 dark:text-slate-300 block mb-1">Unit Bisnis / Cabang</label>
                <select 
                    wire:model="unitId" 
                    @disabled($isLocked)
                    class="w-full px-3 py-2 bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-lg text-xs outline-none focus:ring-2 focus:ring-indigo-500 disabled:bg-slate-50 dark:disabled:bg-slate-900/60 disabled:cursor-not-allowed font-medium"
                >
                    @foreach ($units as $u)
                        <option value="{{ $u->id }}">{{ $u->code }} - {{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- FILTER BAR & KATEGORI AKUN -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50 dark:bg-slate-850 p-3 rounded-2xl border border-slate-200 dark:border-slate-800">
        <!-- TAB KATEGORI -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 text-xs font-semibold">
            @php
                $categories = [
                    'all' => 'Semua Akun',
                    'kas' => 'Kas & Bank',
                    'piutang' => 'Piutang',
                    'aset' => 'Aset',
                    'hutang' => 'Hutang / Kewajiban',
                    'modal' => 'Modal / Ekuitas',
                ];
            @endphp
            @foreach ($categories as $catKey => $catLabel)
                <button 
                    type="button" 
                    wire:click="setCategory('{{ $catKey }}')"
                    class="px-3 py-1.5 rounded-lg whitespace-nowrap transition-all {{ $selectedCategory === $catKey ? 'bg-indigo-600 text-white shadow-xs font-bold' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}"
                >
                    {{ $catLabel }}
                </button>
            @endforeach
        </div>

        <!-- SEARCH BAR -->
        <div class="relative w-full sm:w-64">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input 
                type="text" 
                wire:model.live.debounce.250ms="search" 
                placeholder="Cari kode atau nama akun..." 
                class="w-full pl-8 pr-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs outline-none focus:ring-2 focus:ring-indigo-500"
            >
        </div>
    </div>

    <!-- TABLE DAFTAR AKUN SALDO AWAL -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto max-h-[500px]">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 dark:bg-slate-800/80 sticky top-0 z-10 backdrop-blur-xs border-b border-slate-200 dark:border-slate-800">
                    <tr class="text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold">
                        <th class="py-3 px-4 w-32 font-mono">Kode Akun</th>
                        <th class="py-3 px-4">Nama Akun</th>
                        <th class="py-3 px-3 w-28 text-center">Tipe Akun</th>
                        <th class="py-3 px-3 w-24 text-center">Saldo Normal</th>
                        <th class="py-3 px-4 w-52 text-right">Nominal Saldo Awal (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                    @forelse ($filteredBalances as $accId => $row)
                        @php
                            $isRetainedAcc = $retainedEarningsAccountId && (int) $accId === (int) $retainedEarningsAccountId;
                            $hasBalance = abs((float) ($row['amount'] ?? 0)) > 0.001;
                        @endphp
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-850/50 transition-colors {{ $hasBalance ? 'bg-indigo-50/20 dark:bg-indigo-950/10' : '' }}">
                            <td class="py-2.5 px-4 font-mono font-bold text-slate-800 dark:text-slate-200">
                                {{ $row['code'] }}
                            </td>
                            <td class="py-2.5 px-4 text-slate-700 dark:text-slate-300">
                                <div class="flex items-center gap-2">
                                    <span>{{ $row['name'] }}</span>
                                    @if ($isRetainedAcc)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
                                            Penyeimbang Otomatis
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                    {{ $row['type'] }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $row['normal_balance'] === 'debit' ? 'text-indigo-600 bg-indigo-500/10' : 'text-amber-600 bg-amber-500/10' }}">
                                    {{ $row['normal_balance'] }}
                                </span>
                            </td>
                            <td class="py-2.5 px-4 text-right">
                                @if ($isRetainedAcc)
                                    <div class="font-mono font-bold text-xs text-purple-600 dark:text-purple-400 py-1.5 px-2 bg-purple-50 dark:bg-purple-950/40 rounded-lg text-right">
                                        Rp {{ number_format($calc['retained_earnings_amount'], 2, ',', '.') }}
                                    </div>
                                @else
                                    <div x-data="{
                                        raw: @entangle('balances.'.$accId.'.amount').live,
                                        formatted: '',
                                        scale: '',
                                        updateFormatted() {
                                            this.formatted = window.formatMoneyId(this.raw);
                                            this.scale = window.getTerbilangScale(this.raw);
                                        },
                                        onInput(e) {
                                            let clean = e.target.value.replace(/[^0-9]/g, '');
                                            this.raw = clean === '' ? 0 : parseFloat(clean);
                                            this.formatted = window.formatMoneyId(clean);
                                            this.scale = window.getTerbilangScale(this.raw);
                                        }
                                    }" x-init="updateFormatted(); $watch('raw', () => updateFormatted())" class="relative">
                                        <input 
                                            type="text" 
                                            inputmode="numeric"
                                            x-model="formatted" 
                                            @input="onInput($event)"
                                            @disabled($isLocked)
                                            class="w-full text-right px-2.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-mono font-bold outline-none focus:ring-2 focus:ring-indigo-500 disabled:bg-transparent disabled:border-transparent disabled:text-slate-800 dark:disabled:text-slate-200"
                                            placeholder="0"
                                        >
                                        <template x-if="scale">
                                            <div class="text-[9px] font-semibold text-indigo-600 dark:text-indigo-400 text-right mt-0.5 tracking-tighter truncate" x-text="scale"></div>
                                        </template>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                Tidak ada akun yang cocok dengan kata kunci "{{ $search }}".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- FLOATING LIVE BALANCE SUMMARY BAR -->
    <div class="sticky bottom-4 z-20 bg-slate-900/95 text-white dark:bg-slate-850/95 backdrop-blur-md p-4 rounded-2xl border border-slate-700 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-6 flex-wrap text-xs font-mono">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-sans">Total Aset (Debit)</span>
                <span class="text-sm font-black text-emerald-400">Rp {{ number_format($calc['final_debit'], 2, ',', '.') }}</span>
            </div>

            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-sans">Total Kewajiban & Modal (Kredit)</span>
                <span class="text-sm font-black text-emerald-400">Rp {{ number_format($calc['final_credit'], 2, ',', '.') }}</span>
            </div>

            <div class="pl-4 border-l border-slate-700">
                <span class="text-slate-400 block text-[10px] uppercase font-sans">Alokasi Otomatis ke Laba Ditahan</span>
                <span class="text-sm font-bold text-purple-400">
                    Rp {{ number_format($calc['retained_earnings_amount'], 2, ',', '.') }}
                    <span class="text-[10px] text-slate-400 font-sans">({{ $calc['retained_earnings_amount'] >= 0 ? 'Kredit / Laba' : 'Debit / Rugi' }})</span>
                </span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-bold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>100% BALANCE TERJAMIN</span>
            </div>

            @if (! $isLocked)
                <button 
                    type="button" 
                    wire:click="openSaveModal"
                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-600/30 transition-all"
                >
                    Posting Saldo Awal
                </button>
            @endif
        </div>
    </div>

    <!-- MODAL REOPEN / BUKA KUNCI DARURAT DENGAN PASSWORD SUPER ADMIN -->
    @if ($showUnlockModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-xs p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-rose-500/10">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-lg bg-rose-500 text-white shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-rose-700 dark:text-rose-400">Buka Kunci Saldo Awal Perdana</h3>
                            <p class="text-[11px] text-slate-500">Otorisasi Khusus Super Admin & Audit Trail</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeUnlockModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-base font-bold">✕</button>
                </div>

                <div class="p-6 space-y-4 text-xs">
                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 text-amber-800 dark:text-amber-300 space-y-1">
                        <p class="font-bold">⚠️ Perhatian Pengawasan Audit:</p>
                        <p class="text-[11px] leading-relaxed">
                            Membuka kunci saldo awal dapat mempengaruhi laporan keuangan neraca masa lalu. Seluruh tindakan ini dicatat ke dalam log aktivitas sistem (Audit Trail).
                        </p>
                    </div>

                    <div class="space-y-1">
                        <label class="font-semibold text-slate-700 dark:text-slate-300">Alasan Audit Membuka Kunci *</label>
                        <textarea 
                            wire:model="unlockReason" 
                            rows="2" 
                            placeholder="Contoh: Koreksi saldo awal kas bank berdasarkan rekomendasi auditor independen..."
                            class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs outline-none focus:ring-2 focus:ring-rose-500"
                        ></textarea>
                        @error('unlockReason') <span class="text-[10px] text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="font-semibold text-slate-700 dark:text-slate-300">Password Akun Super Admin Anda *</label>
                        <input 
                            type="password" 
                            wire:model="unlockPassword" 
                            placeholder="Masukkan kata sandi login Anda..." 
                            class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs outline-none focus:ring-2 focus:ring-rose-500"
                        >
                        @error('unlockPassword') <span class="text-[10px] text-rose-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="px-6 py-3.5 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                    <button type="button" wire:click="closeUnlockModal" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-bold hover:bg-slate-200">
                        Batal
                    </button>
                    <button type="button" wire:click="confirmUnlock" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold shadow-md shadow-rose-500/20 transition-all">
                        Verifikasi & Buka Kunci
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL KONFIRMASI SIMPAN -->
    @if ($showConfirmModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-xs p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2.5 bg-indigo-50 dark:bg-indigo-950/30">
                    <div class="p-2 rounded-lg bg-indigo-600 text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-indigo-900 dark:text-indigo-300">Konfirmasi Posting Saldo Awal</h3>
                        <p class="text-[11px] text-slate-500">Posting ke General Ledger & Kunci Resmi</p>
                    </div>
                </div>

                <div class="p-6 space-y-3 text-xs text-slate-600 dark:text-slate-400">
                    <p>
                        Sistem akan memposting transaksi Jurnal Saldo Awal dengan rincian:
                    </p>
                    <ul class="space-y-1.5 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-[11px] font-mono">
                        <li>Tanggal: <strong>{{ $entryDate }}</strong></li>
                        <li>No. Bukti: <strong>{{ $documentNumber }}</strong></li>
                        <li>Total Nilai Neraca: <strong>Rp {{ number_format($calc['final_debit'], 2, ',', '.') }}</strong></li>
                        <li>Penyeimbang Laba Ditahan: <strong>Rp {{ number_format($calc['retained_earnings_amount'], 2, ',', '.') }}</strong></li>
                    </ul>

                    @if (session()->has('error'))
                        <div class="p-3 bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-400 rounded-xl text-[11px] font-semibold flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    <p class="text-[11px] text-amber-600 dark:text-amber-400 font-semibold">
                        🔒 Setelah disimpan, status saldo awal akan otomatis terkunci demi integritas audit.
                    </p>
                </div>

                <div class="px-6 py-3.5 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                    <button type="button" wire:click="closeSaveModal" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-bold hover:bg-slate-200" wire:loading.attr="disabled">
                        Batal
                    </button>
                    <button 
                        type="button" 
                        wire:click="saveAndPost" 
                        wire:loading.attr="disabled"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg text-xs font-bold shadow-md shadow-indigo-500/20 transition-all flex items-center gap-2"
                    >
                        <span wire:loading.remove wire:target="saveAndPost">Ya, Posting & Kunci Resmi</span>
                        <span wire:loading wire:target="saveAndPost" class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Memposting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
