<div class="p-6 space-y-6 max-w-5xl mx-auto">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Input Jurnal Umum
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Masukkan detail baris transaksi Debit dan Kredit. Total Debit & Kredit wajib seimbang (Balanced).
            </p>
        </div>

        <a href="{{ route('accounting.journals.index') }}" wire:navigate class="px-4 py-2 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-semibold rounded-xl">
            &larr; Kembali
        </a>
    </div>

    @if (session()->has('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 rounded-xl text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit="saveJournal" class="space-y-6">
        <!-- HEADER INFORMATION -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Tanggal Transaksi *</label>
                <input wire:model.live="entry_date" type="date" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:text-slate-100" />
                @error('entry_date') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Jenis Jurnal *</label>
                <select wire:model.live="journal_type_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 font-medium">
                    <option value="">-- Pilih Jenis Jurnal --</option>
                    @foreach ($journalTypes as $jt)
                        <option value="{{ $jt->id }}">{{ $jt->code }} - {{ $jt->name }}</option>
                    @endforeach
                </select>
                @error('journal_type_id') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-semibold uppercase text-slate-500">No Bukti / Dokumen</label>
                    <label class="inline-flex items-center gap-1 cursor-pointer text-[11px] font-semibold text-indigo-600 dark:text-indigo-400">
                        <input type="checkbox" wire:model.live="is_auto_document_number" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span>Otomatis</span>
                    </label>
                </div>
                <input 
                    wire:model="document_number" 
                    type="text" 
                    placeholder="misal: BM/2026/08/0001" 
                    @if($is_auto_document_number) readonly @endif
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono font-semibold focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 @if($is_auto_document_number) opacity-80 cursor-not-allowed bg-slate-100 dark:bg-slate-800/60 @endif" 
                />
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Deskripsi / Keterangan Utama Jurnal *</label>
                <input wire:model="description" type="text" placeholder="Contoh: Pembayaran Beban Listrik & Air Bulan Agustus" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:text-slate-100" />
                @error('description') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- JOURNAL LINES TABLE -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 bg-slate-50/80 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">
                    Baris Detail Transaksi (Journal Lines)
                </h3>
                <button type="button" wire:click="addLine" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm">
                    + Tambah Baris
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs uppercase font-semibold text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3 w-10 text-center">#</th>
                            <th class="px-4 py-3 min-w-[240px]">Pilih Akun Posting *</th>
                            <th class="px-4 py-3 min-w-[160px]">Unit Perusahaan</th>
                            <th class="px-4 py-3 min-w-[180px]">Deskripsi Line</th>
                            <th class="px-4 py-3 w-36 text-right">Debit (Rp)</th>
                            <th class="px-4 py-3 w-36 text-right">Kredit (Rp)</th>
                            <th class="px-4 py-3 w-12 text-center">Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($lines as $index => $line)
                            <tr>
                                <td class="px-4 py-3 text-center font-bold text-slate-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">
                                    <select wire:model="lines.{{ $index }}.account_id" class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-mono dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-- Pilih Akun --</option>
                                        @foreach ($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ strtoupper($acc->normal_balance) }})</option>
                                        @endforeach
                                    </select>
                                    @error("lines.{$index}.account_id") <span class="text-[10px] text-rose-500 block">{{ $message }}</span> @enderror
                                </td>
                                <td class="px-4 py-3">
                                    <select wire:model="lines.{{ $index }}.unit_id" class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-semibold dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-- Pilih Unit (Opsional) --</option>
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->code }} - {{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                    @error("lines.{$index}.unit_id") <span class="text-[10px] text-rose-500 block">{{ $message }}</span> @enderror
                                </td>
                                <td class="px-4 py-3">
                                    <input wire:model="lines.{{ $index }}.description" type="text" placeholder="Opsional" class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:text-slate-100" />
                                </td>
                                <td class="px-4 py-3">
                                    <input wire:model.live="lines.{{ $index }}.debit" type="number" step="0.01" class="w-full px-2.5 py-1.5 text-right font-mono font-bold bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-indigo-600 dark:text-indigo-400" />
                                </td>
                                <td class="px-4 py-3">
                                    <input wire:model.live="lines.{{ $index }}.credit" type="number" step="0.01" class="w-full px-2.5 py-1.5 text-right font-mono font-bold bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-purple-600 dark:text-purple-400" />
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" wire:click="removeLine({{ $index }})" class="text-slate-400 hover:text-rose-500 p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- SUMMARY & BALANCE INDICATOR -->
            <div class="p-6 bg-slate-50/80 dark:bg-slate-800/40 border-t border-slate-200 dark:border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    @if ($this->isBalanced)
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/30 text-xs font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            BALANCED (SEIMBANG)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-rose-500/10 text-rose-500 border border-rose-500/30 text-xs font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            UNBALANCED (SELISIH: Rp {{ number_format($this->difference, 2, ',', '.') }})
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-6 text-sm font-bold">
                    <div>
                        <span class="text-xs uppercase text-slate-400 block font-normal">Total Debit</span>
                        <span class="text-indigo-600 dark:text-indigo-400 font-mono text-base">Rp {{ number_format($this->totalDebit, 2, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-xs uppercase text-slate-400 block font-normal">Total Kredit</span>
                        <span class="text-purple-600 dark:text-purple-400 font-mono text-base">Rp {{ number_format($this->totalCredit, 2, ',', '.') }}</span>
                    </div>

                    <button 
                        type="submit" 
                        @if (!$this->isBalanced) disabled @endif
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-500/25 transition-all">
                        Posting Jurnal
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
