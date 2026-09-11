<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('accounting.budgets.index') }}" wire:navigate class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                    {{ $budgetId ? 'Ubah Rencana Anggaran' : 'Buat Anggaran Baru' }}
                </h1>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Tentukan plafon batas pengeluaran tahunan dan alokasi bulanan per akun biaya.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('accounting.budgets.index') }}" wire:navigate class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-200">
                Kembali
            </a>
            <button wire:click="save" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-sm transition-all">
                Simpan Anggaran
            </button>
        </div>
    </div>

    <!-- Main Form Details -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60 space-y-4">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-3">
            Informasi Anggaran
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">Tahun Fiskal *</label>
                <input type="number" wire:model="fiscal_year" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white">
                @error('fiscal_year') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">Nama Anggaran *</label>
                <input type="text" wire:model="name" placeholder="Contoh: Rencana Kerja & Anggaran Biaya (RKAB) 2027" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white">
                @error('name') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">Deskripsi & Catatan Kebijakan</label>
                <textarea wire:model="description" rows="2" placeholder="Catatan peruntukan atau asumsi penyusunan anggaran..." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">Status Anggaran</label>
                <select wire:model="status" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white">
                    <option value="draft">Draft</option>
                    <option value="active">Aktif</option>
                    <option value="closed">Ditutup</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">Mode Penegakan Pagu</label>
                <select wire:model="enforcement_mode" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white">
                    <option value="warning_only">Warning Only (Peringatan Visual Saat Penginputan)</option>
                    <option value="strict_block">Strict Block (Tolak Transaksi Melebihi Pagu)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">Ambang Peringatan Serapan (%)</label>
                <input type="number" step="0.5" wire:model="warning_threshold_pct" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white">
                <span class="text-xs text-gray-400">Peringatan muncul jika serapan dana mencapai angka ini (default 80%).</span>
            </div>
        </div>
    </div>

    <!-- Budget Allocation Lines -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60 overflow-hidden">
        <div class="p-4 bg-gray-50/70 dark:bg-gray-700/40 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white text-base">Alokasi Plafon Akun Biaya</h3>
                <p class="text-xs text-gray-400">Tentukan plafon tahunan dan distribusikan ke bulan 1-12.</p>
            </div>

            <button type="button" wire:click="addLine" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-xl text-xs font-semibold hover:bg-indigo-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Baris Akun
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-100/50 dark:bg-gray-700/30 uppercase font-semibold text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 min-w-[220px]">Akun Biaya</th>
                        <th class="px-4 py-3 min-w-[140px]">Unit Bisnis</th>
                        <th class="px-4 py-3 min-w-[180px] text-right">Plafon Tahunan (Rp)</th>
                        <th class="px-3 py-3 text-center min-w-[80px]">Auto 1/12</th>
                        <th class="px-3 py-3 min-w-[125px] text-right">Jan</th>
                        <th class="px-3 py-3 min-w-[125px] text-right">Feb</th>
                        <th class="px-3 py-3 min-w-[125px] text-right">Mar</th>
                        <th class="px-3 py-3 min-w-[125px] text-right">Apr</th>
                        <th class="px-3 py-3 min-w-[125px] text-right">Mei</th>
                        <th class="px-3 py-3 min-w-[125px] text-right">Jun</th>
                        <th class="px-3 py-3 min-w-[125px] text-right">Jul</th>
                        <th class="px-3 py-3 min-w-[125px] text-right">Ags</th>
                        <th class="px-3 py-3 min-w-[125px] text-right">Sep</th>
                        <th class="px-3 py-3 min-w-[125px] text-right">Okt</th>
                        <th class="px-3 py-3 min-w-[125px] text-right">Nov</th>
                        <th class="px-3 py-3 min-w-[125px] text-right">Des</th>
                        <th class="px-3 py-3 text-center">Hapus</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                    @forelse ($lines as $idx => $line)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20">
                            <!-- Akun -->
                            <td class="px-4 py-2">
                                <select wire:model="lines.{{ $idx }}.account_id" class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1.5 text-xs text-gray-900 dark:text-gray-100">
                                    <option value="">-- Pilih Akun --</option>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <!-- Unit -->
                            <td class="px-4 py-2">
                                <select wire:model="lines.{{ $idx }}.unit_id" class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1.5 text-xs text-gray-900 dark:text-gray-100">
                                    <option value="">Semua Unit</option>
                                    @foreach ($units as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <!-- Annual Plafon -->
                            <td class="px-4 py-2">
                                <div x-data="{
                                    raw: @entangle('lines.'.$idx.'.annual_amount').live,
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
                                        class="w-full text-right bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1.5 text-xs font-bold font-mono text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                        placeholder="0"
                                    >
                                    <template x-if="scale">
                                        <div class="text-[10px] font-medium text-indigo-600 dark:text-indigo-400 text-right mt-0.5 tracking-tight truncate" x-text="scale"></div>
                                    </template>
                                </div>
                            </td>

                            <!-- Auto Split Button -->
                            <td class="px-3 py-2 text-center">
                                <button type="button" wire:click="autoDistributeRow({{ $idx }})" title="Bagi rata tahunan ke 12 bulan" class="px-2 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-indigo-50 dark:hover:bg-indigo-950 text-indigo-600 dark:text-indigo-400 font-semibold rounded text-[10px]">
                                    Bagi 12
                                </button>
                            </td>

                            <!-- Month 1 - 12 -->
                            @for ($m = 1; $m <= 12; $m++)
                                @php $pad = str_pad((string)$m, 2, '0', STR_PAD_LEFT); @endphp
                                <td class="px-2 py-2">
                                    <div x-data="{
                                        raw: @entangle('lines.'.$idx.'.m'.$pad.'_amount').live,
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
                                            $wire.sumMonthlyToAnnual({{ $idx }});
                                        }
                                    }" x-init="updateFormatted(); $watch('raw', () => updateFormatted())" class="relative">
                                        <input 
                                            type="text" 
                                            inputmode="numeric"
                                            x-model="formatted" 
                                            @input="onInput($event)"
                                            class="w-28 text-right bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1 text-[11px] font-mono text-gray-900 dark:text-white focus:ring-1 focus:ring-indigo-500"
                                            placeholder="0"
                                        >
                                        <template x-if="scale">
                                            <div class="text-[9px] font-semibold text-gray-500 dark:text-gray-400 text-right mt-0.5 tracking-tighter truncate" x-text="scale"></div>
                                        </template>
                                    </div>
                                </td>
                            @endfor

                            <!-- Remove Line Button -->
                            <td class="px-3 py-2 text-center">
                                <button type="button" wire:click="removeLine({{ $idx }})" class="text-rose-500 hover:text-rose-700 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="17" class="text-center py-6 text-gray-400">
                                Belum ada alokasi akun. Silakan klik tombol 'Tambah Baris Akun'.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
