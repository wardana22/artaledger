<div class="p-6 space-y-6">
    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                Import Jurnal Transaksi Excel
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Unggah file Excel format pembukuan (.xlsx) untuk dibaca, divalidasi, dan diposting ke General Ledger.
            </p>
        </div>

        <!-- TAB NAVIGATION SWITCHER -->
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-800/80 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 self-start md:self-auto shadow-2xs">
            <button 
                type="button"
                wire:click="switchTab('import')"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200 whitespace-nowrap {{ $activeTab === 'import' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/30 ring-2 ring-emerald-400/50 scale-[1.01]' : 'text-slate-600 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-white/70 dark:hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                Unggah & Impor
            </button>
            <button 
                type="button"
                wire:click="switchTab('settings')"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200 whitespace-nowrap {{ $activeTab === 'settings' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/30 ring-2 ring-emerald-400/50 scale-[1.01]' : 'text-slate-600 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-white/70 dark:hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Pengaturan Mapping & Preset
            </button>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    @if (session()->has('message'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 rounded-2xl text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-400 rounded-2xl text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    @if ($activeTab === 'import')
        @if (! $activeBatch)
            @if ($wizardStep === 1)
            <!-- STEP 1: UPLOAD AREA -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                <div class="max-w-xl mx-auto space-y-4 text-center">
                    <!-- IDLE UPLOAD DROPZONE -->
                    <div wire:loading.remove wire:target="file" class="p-8 border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-400 rounded-2xl bg-slate-50/50 dark:bg-slate-800/40 transition-all group cursor-pointer">
                        <svg class="w-12 h-12 mx-auto text-emerald-600 dark:text-emerald-400 mb-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>

                        <label class="cursor-pointer block">
                            <span class="text-base font-bold text-emerald-600 dark:text-emerald-400 hover:underline block">
                                Pilih File Excel (.xlsx) - Konversi Otomatis
                            </span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 block mt-1.5 leading-relaxed">
                                Sistem mendukung pemetaan kolom kustom, deteksi rumus otomatis, dan validasi data sebelum diposting.
                            </span>
                            <input type="file" wire:model.live="file" accept=".xlsx,.xls" class="hidden" aria-label="Unggah File Excel Transaksi Jurnal" />
                        </label>
                    </div>

                    <!-- LOADING INDICATOR -->
                    <div wire:loading wire:target="file" class="p-6 border border-emerald-500/30 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 text-xs font-bold flex items-center justify-center gap-3 animate-pulse">
                        <svg class="w-5 h-5 animate-spin text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Membaca dan menganalisis lembar kerja Excel... Mohon tunggu sebentar...</span>
                    </div>

                    @error('file') <span class="text-xs text-rose-500 font-semibold block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- RIWAYAT BATCH IMPOR TABLE -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50/80 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Riwayat Batch Impor Excel
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar riwayat file yang telah diunggah dan diproses ke sistem.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-5 py-3.5">WAKTU IMPOR</th>
                                <th class="px-5 py-3.5 font-mono">KODE BATCH</th>
                                <th class="px-5 py-3.5">NAMA FILE</th>
                                <th class="px-5 py-3.5 text-center">TOTAL BARIS</th>
                                <th class="px-5 py-3.5 text-center">BARIS VALID</th>
                                <th class="px-5 py-3.5 text-center">STATUS</th>
                                <th class="px-5 py-3.5 text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($recentBatches as $batchItem)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <div class="font-medium text-slate-700 dark:text-slate-200">
                                            {{ $batchItem->created_at ? $batchItem->created_at->translatedFormat('d M Y, H:i') : '-' }}
                                        </div>
                                        <div class="text-[11px] text-slate-400">
                                            Oleh: {{ $batchItem->user ? $batchItem->user->name : 'Sistem' }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 font-mono font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                                        {{ $batchItem->batch_code }}
                                    </td>
                                    <td class="px-5 py-3.5 font-medium text-slate-800 dark:text-slate-100 max-w-xs truncate">
                                        {{ $batchItem->file_name }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center font-mono font-bold text-slate-700 dark:text-slate-200">
                                        {{ number_format($batchItem->total_rows) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center font-mono">
                                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">
                                            {{ number_format($batchItem->valid_rows) }}
                                        </span>
                                        @if ($batchItem->error_rows > 0)
                                            <span class="text-rose-500 font-bold ml-1">
                                                ({{ number_format($batchItem->error_rows) }} err)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        @if ($batchItem->status === 'posted')
                                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                ✓ POSTED
                                            </span>
                                        @elseif ($batchItem->error_rows > 0)
                                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                                ⚠️ HAS ERROR
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                                STAGED
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <div class="inline-flex items-center justify-center gap-1.5">
                                            @if ($batchItem->status !== 'posted')
                                                <button 
                                                    type="button"
                                                    wire:click="loadBatch({{ $batchItem->id }})"
                                                    title="Lanjutkan Import"
                                                    aria-label="Lanjutkan Import {{ $batchItem->batch_code }}"
                                                    class="p-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-600 border border-emerald-500/20 hover:border-emerald-600 text-emerald-600 dark:text-emerald-400 hover:text-white shadow-2xs hover:shadow-md hover:shadow-emerald-500/20 transition-all duration-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                                    </svg>
                                                </button>
                                            @endif

                                            <button 
                                                type="button"
                                                wire:click="deleteBatch({{ $batchItem->id }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus seluruh data transaksi jurnal yang diposting dari batch file ini?"
                                                wire:loading.attr="disabled"
                                                wire:target="deleteBatch({{ $batchItem->id }})"
                                                title="Hapus Batch Impor"
                                                aria-label="Hapus Batch Impor {{ $batchItem->batch_code }}"
                                                class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-rose-600 hover:text-white hover:border-rose-600 shadow-2xs hover:shadow-md hover:shadow-rose-500/20 disabled:opacity-50 transition-all duration-200">
                                                <svg wire:loading.remove wire:target="deleteBatch({{ $batchItem->id }})" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                <svg wire:loading wire:target="deleteBatch({{ $batchItem->id }})" class="w-3.5 h-3.5 animate-spin text-rose-500" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-400">
                                        Belum ada riwayat batch impor file Excel.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif ($wizardStep === 2)
            <!-- STEP 2: DYNAMIC MAPPING & LIVE PREVIEW -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-6">
                <!-- TOP CONTROLS & PRESET SELECTOR -->
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 pb-5 border-b border-slate-200 dark:border-slate-800">
                    <div>
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">
                            Langkah 2: Pemetaan Kolom Excel
                        </span>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 mt-2">
                            Konfigurasi Mapping: {{ $originalFilename }}
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Tentukan baris awal data dan pilih kolom yang bersesuaian dengan format data pada file Anda.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Quick Presets -->
                        <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                            <span class="text-[11px] font-semibold text-slate-400 px-2">Preset Cepat:</span>
                            <button 
                                type="button" 
                                wire:click="applySystemPreset('cleaned')"
                                class="px-2.5 py-1 text-xs font-bold rounded-lg bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:text-emerald-600 shadow-2xs transition-colors">
                                Standard Bersih
                            </button>
                            <button 
                                type="button" 
                                wire:click="applySystemPreset('legacy')"
                                class="px-2.5 py-1 text-xs font-bold rounded-lg bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:text-emerald-600 shadow-2xs transition-colors">
                                Template Mentah
                            </button>
                        </div>

                        @if ($presets->count() > 0)
                            <select 
                                wire:change="applyPreset($event.target.value)" 
                                aria-label="Pilih Preset Mapping Tersimpan"
                                class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500">
                                <option value="">-- Pilih Preset Tersimpan --</option>
                                @foreach ($presets as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        @endif

                        <button 
                            type="button"
                            wire:click="$set('showSavePresetModal', true)"
                            class="px-3 py-1.5 text-xs font-bold rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 transition-colors flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            Simpan Sebagai Preset
                        </button>
                    </div>
                </div>

                <!-- FORM PEMETAAN KOLOM -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Sheet Selector -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            PILIH SHEET EXCEL
                        </label>
                        <select 
                            wire:model.live="selectedSheet"
                            aria-label="Pilih Lembar Kerja Sheet"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500">
                            @foreach ($availableSheets as $sheet)
                                <option value="{{ $sheet }}">{{ $sheet }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Start Row -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            MULAI DARI BARIS KE-
                        </label>
                        <input 
                            type="number" 
                            wire:model.live.debounce.300ms="startRow" 
                            min="1"
                            aria-label="Baris Awal Data"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500" />
                        @error('startRow') <span class="text-[11px] text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <!-- Col Date -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            KOLOM TANGGAL <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            wire:model.live="colDate"
                            aria-label="Pilih Kolom Tanggal"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Pilih Kolom --</option>
                            @foreach ($availableColumns as $col)
                                <option value="{{ $col }}">Kolom {{ $col }}</option>
                            @endforeach
                        </select>
                        @error('colDate') <span class="text-[11px] text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <!-- Col Doc No -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            KOLOM NO. BUKTI (OPSIONAL)
                        </label>
                        <select 
                            wire:model.live="colDocNo"
                            aria-label="Pilih Kolom No. Bukti"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Kosongkan Jika Tidak Ada --</option>
                            @foreach ($availableColumns as $col)
                                <option value="{{ $col }}">Kolom {{ $col }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Col Desc -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            KOLOM KETERANGAN
                        </label>
                        <select 
                            wire:model.live="colDesc"
                            aria-label="Pilih Kolom Keterangan"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Kosongkan Jika Tidak Ada --</option>
                            @foreach ($availableColumns as $col)
                                <option value="{{ $col }}">Kolom {{ $col }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Col Account -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            KOLOM KODE AKUN <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            wire:model.live="colAccount"
                            aria-label="Pilih Kolom Kode Akun"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Pilih Kolom --</option>
                            @foreach ($availableColumns as $col)
                                <option value="{{ $col }}">Kolom {{ $col }}</option>
                            @endforeach
                        </select>
                        @error('colAccount') <span class="text-[11px] text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <!-- Col Sub Account -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            KOLOM SUB AKUN (OPSIONAL)
                        </label>
                        <select 
                            wire:model.live="colSubAccount"
                            aria-label="Pilih Kolom Sub Akun"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Kosongkan Jika Tidak Ada --</option>
                            @foreach ($availableColumns as $col)
                                <option value="{{ $col }}">Kolom {{ $col }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Col Unit -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            KOLOM UNIT (OPSIONAL)
                        </label>
                        <select 
                            wire:model.live="colUnit"
                            aria-label="Pilih Kolom Unit"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Kosongkan (Default Pusat) --</option>
                            @foreach ($availableColumns as $col)
                                <option value="{{ $col }}">Kolom {{ $col }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Col Debit -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            KOLOM DEBIT <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            wire:model.live="colDebit"
                            aria-label="Pilih Kolom Debit"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Pilih Kolom --</option>
                            @foreach ($availableColumns as $col)
                                <option value="{{ $col }}">Kolom {{ $col }}</option>
                            @endforeach
                        </select>
                        @error('colDebit') <span class="text-[11px] text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <!-- Col Credit -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            KOLOM KREDIT <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            wire:model.live="colCredit"
                            aria-label="Pilih Kolom Kredit"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Pilih Kolom --</option>
                            @foreach ($availableColumns as $col)
                                <option value="{{ $col }}">Kolom {{ $col }}</option>
                            @endforeach
                        </select>
                        @error('colCredit') <span class="text-[11px] text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- LIVE PREVIEW 5 BARIS PERTAMA -->
                <div class="space-y-2 pt-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            Live Preview Hasil Pemetaan (5 Baris Pertama Mulai Baris {{ $startRow }})
                        </h4>
                        <span class="text-[11px] text-slate-400">Pastikan kolom yang Anda pilih terpetakan secara presisi</span>
                    </div>

                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                            <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-4 py-2 font-mono">BARIS</th>
                                    <th class="px-4 py-2">TANGGAL ({{ $colDate ?: '-' }})</th>
                                    <th class="px-4 py-2">NO. BUKTI ({{ $colDocNo ?: '-' }})</th>
                                    <th class="px-4 py-2">KETERANGAN ({{ $colDesc ?: '-' }})</th>
                                    <th class="px-4 py-2">AKUN ({{ $colSubAccount ? ($colAccount ? $colSubAccount.' / '.$colAccount : $colSubAccount) : ($colAccount ?: '-') }})</th>
                                    <th class="px-4 py-2">UNIT ({{ $colUnit ?: '-' }})</th>
                                    <th class="px-4 py-2 text-right">DEBIT ({{ $colDebit ?: '-' }})</th>
                                    <th class="px-4 py-2 text-right">KREDIT ({{ $colCredit ?: '-' }})</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-mono text-[11px]">
                                @forelse ($livePreviewRows as $pRow)
                                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                                        <td class="px-4 py-2 text-slate-400 font-bold">#{{ $pRow['row_index'] }}</td>
                                        <td class="px-4 py-2 text-slate-700 dark:text-slate-200">{{ $pRow['date'] ?: '-' }}</td>
                                        <td class="px-4 py-2 text-slate-700 dark:text-slate-200">{{ $pRow['doc_no'] ?: '-' }}</td>
                                        <td class="px-4 py-2 text-slate-700 dark:text-slate-200 font-sans truncate max-w-xs">{{ $pRow['description'] ?: '-' }}</td>
                                        <td class="px-4 py-2 text-indigo-600 dark:text-indigo-400 font-bold">{{ $pRow['account'] ?: '-' }}</td>
                                        <td class="px-4 py-2 text-slate-600 dark:text-slate-300 font-sans">{{ $pRow['unit'] ?: 'Pusat' }}</td>
                                        <td class="px-4 py-2 text-right text-slate-800 dark:text-slate-100">{{ is_numeric($pRow['debit']) ? number_format((float)$pRow['debit'], 2) : ($pRow['debit'] ?: '0') }}</td>
                                        <td class="px-4 py-2 text-right text-slate-800 dark:text-slate-100">{{ is_numeric($pRow['credit']) ? number_format((float)$pRow['credit'], 2) : ($pRow['credit'] ?: '0') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="p-6 text-center text-slate-400 font-sans">
                                            Tidak ada data pratinjau yang sesuai pada sheet atau baris yang dipilih.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ACTION BUTTONS STEP 2 -->
                <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-800">
                    <button 
                        type="button" 
                        wire:click="resetWizard" 
                        class="px-4 py-2 text-xs font-bold rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors">
                        ← Pilih Berkas Lain
                    </button>

                    <button 
                        type="button" 
                        wire:click="processImportWithMapping"
                        wire:loading.attr="disabled"
                        class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                        <svg wire:loading.remove wire:target="processImportWithMapping" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                        <svg wire:loading wire:target="processImportWithMapping" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="processImportWithMapping">Lanjutkan ke Validasi & Posting</span>
                        <span wire:loading wire:target="processImportWithMapping">Memproses dan Memvalidasi Data...</span>
                    </button>
                </div>
            </div>
        @endif
    @else
        <!-- STEP 2: TOP ACTION BAR & CONTROL BADGES -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-xl text-center">
                    <span class="text-xl font-bold font-mono text-indigo-600 dark:text-indigo-400 block leading-none">{{ number_format($activeBatch->total_rows) }}</span>
                    <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 mt-0.5 block">TRANSAKSI</span>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Pratinjau File: {{ $activeBatch->file_name }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Kode Batch: <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $activeBatch->batch_code }}</span></p>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex items-center gap-3">
                @if ($activeBatch->error_rows === 0 && $batchDifference <= 1.00)
                    <button 
                        type="button"
                        wire:click="commitPosting"
                        wire:confirm="Apakah Anda yakin ingin memposting seluruh {{ number_format($activeBatch->total_rows) }} jurnal transaksi ini ke General Ledger?"
                        wire:loading.attr="disabled"
                        wire:target="commitPosting"
                        aria-label="Proses Import Sekarang"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-75 disabled:cursor-wait text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-500/20 active:scale-[0.98] transition-all duration-200 gap-2">
                        
                        <!-- Icon Normal (Tampil saat idle) -->
                        <svg wire:loading.remove wire:target="commitPosting" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>

                        <!-- Icon Spinner (Tampil saat memproses) -->
                        <svg wire:loading wire:target="commitPosting" class="w-4 h-4 shrink-0 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>

                        <!-- Teks Dinamis -->
                        <span wire:loading.remove wire:target="commitPosting">Proses Import Sekarang</span>
                        <span wire:loading wire:target="commitPosting">Memposting Transaksi...</span>
                    </button>
                @else
                    <button 
                        type="button"
                        disabled
                        aria-label="Proses Import Ditahan"
                        class="inline-flex items-center px-5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 font-bold text-xs uppercase tracking-wider rounded-xl border border-slate-200 dark:border-slate-700 cursor-not-allowed gap-2">
                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        Proses Import Ditahan (Ada Error)
                    </button>
                @endif

                <button 
                    type="button"
                    wire:click="resetWizard"
                    wire:loading.attr="disabled"
                    wire:target="commitPosting"
                    aria-label="Batal Import"
                    class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition-all">
                    ✕ Batal
                </button>
            </div>
        </div>

        <!-- STEP 3: DITEMUKAN KESALAHAN PADA DATA EXCEL (RED ALERT CARD - ONLY IF ERRORS EXIST) -->
        @if ($activeBatch->error_rows > 0)
            <div class="p-5 bg-rose-500/10 border border-rose-500/30 rounded-2xl space-y-3">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-rose-500/20 text-rose-600 dark:text-rose-400 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>

                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-rose-700 dark:text-rose-400">
                            Ditemukan Kesalahan pada Data Excel
                        </h3>
                        <p class="text-xs text-rose-600 dark:text-rose-300 mt-0.5">
                            Terdapat <strong class="font-bold">{{ number_format($activeBatch->error_rows) }} baris transaksi</strong> yang tidak valid. 
                            @if ($headerAccountErrorCount > 0)
                                Ditemukan <strong class="font-bold">{{ number_format($headerAccountErrorCount) }} transaksi</strong> menggunakan <strong class="underline">Akun Header/Grup</strong>. 
                            @endif
                            Mohon perbaiki file Excel Anda sebelum melanjutkan import.
                        </p>
                    </div>
                </div>

                <!-- EMBEDDED ERROR TABLE (SCROLLABLE) -->
                <div class="border border-rose-500/20 rounded-xl overflow-hidden bg-white dark:bg-slate-900 max-h-52 overflow-y-auto">
                    <table class="w-full text-left text-xs font-mono text-slate-600 dark:text-slate-300">
                        <thead class="bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 uppercase font-semibold border-b border-rose-500/20 sticky top-0">
                            <tr>
                                <th class="px-4 py-2.5 w-16">Baris</th>
                                <th class="px-4 py-2.5 w-36">No. Bukti</th>
                                <th class="px-4 py-2.5 w-28">Kode Akun</th>
                                <th class="px-4 py-2.5">Keterangan Baris</th>
                                <th class="px-4 py-2.5 text-right text-rose-600 dark:text-rose-400">Keterangan Error</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rose-100 dark:divide-rose-950/50">
                            @foreach ($errorRowsSummary as $errRow)
                                <tr class="hover:bg-rose-50/50 dark:hover:bg-rose-900/20 transition-colors">
                                    <td class="px-4 py-2 text-slate-400 font-bold">{{ $errRow->row_index }}</td>
                                    <td class="px-4 py-2 text-slate-700 dark:text-slate-200">{{ $errRow->document_number ?: '-' }}</td>
                                    <td class="px-4 py-2 text-slate-900 dark:text-slate-100 font-bold">{{ $errRow->raw_account_code ?: 'KOSONG' }}</td>
                                    <td class="px-4 py-2 text-slate-500 dark:text-slate-400 truncate max-w-xs">{{ $errRow->description ?: '-' }}</td>
                                    <td class="px-4 py-2 text-right text-rose-600 dark:text-rose-400 font-bold uppercase">
                                        {{ implode(' | ', $errRow->validation_messages ?? ['ERROR']) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- STEP 4: GLOBAL SUMMARY CARDS (3 CARDS - TOTAL DEBIT, TOTAL KREDIT, SELISIH) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- TOTAL DEBIT GLOBAL -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 block">TOTAL DEBIT GLOBAL</span>
                <span class="text-xl font-bold font-mono text-slate-800 dark:text-slate-100 mt-1 block">
                    Rp {{ number_format($totalBatchDebit, 2, ',', '.') }}
                </span>
            </div>

            <!-- TOTAL KREDIT GLOBAL -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 block">TOTAL KREDIT GLOBAL</span>
                <span class="text-xl font-bold font-mono text-slate-800 dark:text-slate-100 mt-1 block">
                    Rp {{ number_format($totalBatchCredit, 2, ',', '.') }}
                </span>
            </div>

            <!-- STATUS KESEIMBANGAN / SELISIH -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
                <span class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 block">STATUS KESEIMBANGAN / SELISIH</span>
                <div class="mt-1 flex items-center gap-2">
                    @if ($batchDifference <= 1.00)
                        <span class="text-base font-bold font-mono text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5" title="Selisih Rp {{ number_format($batchDifference, 2, ',', '.') }} berada dalam batas toleransi pembulatan <= Rp 1,00">
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

        <!-- STEP 5: PREVIEW DATA TRANSAKSI TABLE WITH SEARCH & STATUS FILTERING -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 bg-slate-50/80 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                        Preview Data Transaksi
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar baris transaksi yang berhasil diekstrak dan divalidasi.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- SEARCH INPUT -->
                    <div class="relative w-full sm:w-64">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            placeholder="Cari no.bukti, akun, ket..."
                            aria-label="Cari Baris Transaksi Preview"
                            class="w-full pl-9 pr-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-200" 
                        />
                    </div>

                    <!-- STATUS FILTER SELECT -->
                    <select 
                        wire:model.live="statusFilter" 
                        aria-label="Filter Status Transaksi"
                        class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="all">Semua Status</option>
                        <option value="error">Hanya Status Error</option>
                        <option value="valid">Hanya Status Valid</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-5 py-3.5">TANGGAL</th>
                            <th class="px-5 py-3.5">NO. BUKTI</th>
                            <th class="px-5 py-3.5 font-sans">KETERANGAN</th>
                            <th class="px-5 py-3.5 font-sans">UNIT (AUTO)</th>
                            <th class="px-5 py-3.5">AKUN</th>
                            <th class="px-5 py-3.5 text-right">DEBIT</th>
                            <th class="px-5 py-3.5 text-right">KREDIT</th>
                            <th class="px-5 py-3.5 text-center">STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($rows as $row)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors @if($row->validation_status === 'error') bg-rose-50/50 dark:bg-rose-950/20 @endif">
                                <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    {{ $row->entry_date ? $row->entry_date->format('Y-m-d') : '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-slate-800 dark:text-slate-100 font-bold whitespace-nowrap">
                                    {{ $row->document_number ?: '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300 font-sans font-medium">
                                    {{ $row->description ?: '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300 font-sans font-medium whitespace-nowrap">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ $row->unit ? $row->unit->name : 'Kantor Pusat' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($row->account && ! $row->account->is_group)
                                        <span class="font-bold text-indigo-600 dark:text-indigo-400 font-mono">{{ $row->raw_account_code }}</span>
                                    @elseif ($row->account && $row->account->is_group)
                                        <div class="space-y-0.5">
                                            <span class="font-bold text-rose-600 dark:text-rose-400 font-mono">{{ $row->raw_account_code }}</span>
                                            <span class="block text-[10px] font-sans font-bold text-rose-600 dark:text-rose-500 uppercase">
                                                ✕ AKUN HEADER (TIDAK BOLEH)
                                            </span>
                                        </div>
                                    @else
                                        <div class="space-y-0.5">
                                            <span class="font-bold text-rose-600 dark:text-rose-400 font-mono">{{ $row->raw_account_code ?: 'KOSONG' }}</span>
                                            <span class="block text-[10px] font-sans font-bold text-rose-600 dark:text-rose-500 uppercase">
                                                ✕ TIDAK TERDAFTAR
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ $row->debit > 0 ? number_format($row->debit, 2, ',', '.') : '0' }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ $row->credit > 0 ? number_format($row->credit, 2, ',', '.') : '0' }}
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    @if ($row->validation_status === 'valid')
                                        <span class="text-emerald-600 dark:text-emerald-400 font-black text-sm">✓</span>
                                    @else
                                        <span class="text-rose-600 dark:text-rose-400 font-black text-sm">✕</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-400 font-sans">
                                    Tidak ada data baris transaksi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <x-custom-pagination :paginator="$rows" />
    @endif
    @elseif ($activeTab === 'settings')
        <!-- SETTINGS TAB: PENGATURAN MAPPING & PRESET -->
        <div class="space-y-6">
            <!-- INFO HEADER CARD -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Manajemen Konfigurasi Pemetaan (Preset Mapping)
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Kelola profil pemetaan kolom Excel Anda agar proses import jurnal dari berbagai sumber file (format bank, software akuntansi lain, dsb.) dapat dilakukan secara instan.
                        </p>
                    </div>

                    <button 
                        type="button"
                        wire:click="switchTab('import')"
                        class="px-4 py-2 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition-colors flex items-center gap-1.5 self-start md:self-auto shadow-sm shadow-emerald-600/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Mulai Impor File Baru
                    </button>
                </div>
            </div>

            <!-- SYSTEM PRESETS OVERVIEW -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Standard Bersih -->
                <div class="p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                            Preset Bawaan Sistem
                        </span>
                        <span class="text-xs font-mono text-slate-400">Mulai Baris: 4</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Standard Bersih (Cleaned)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Format rapi dengan baris header standar. Sangat cocok untuk file laporan yang diekspor dari sistem aplikasi modern.
                    </p>
                    <div class="flex flex-wrap gap-1.5 pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] font-mono text-slate-600 dark:text-slate-400">
                        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800">Tgl: B</span>
                        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800">Bukti: C</span>
                        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800">Ket: D</span>
                        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800">Akun: E</span>
                        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800">Unit: G</span>
                        <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-semibold">Debit: H</span>
                        <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-semibold">Kredit: I</span>
                    </div>
                </div>

                <!-- Template Mentah Legacy -->
                <div class="p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                            Preset Bawaan Sistem
                        </span>
                        <span class="text-xs font-mono text-slate-400">Mulai Baris: 10</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Template Mentah (Legacy)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Format pembukuan spreadsheet tradisional dengan tabel rekap dan rumus bawaan di sheet <em>Jurnal Umum</em>.
                    </p>
                    <div class="flex flex-wrap gap-1.5 pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] font-mono text-slate-600 dark:text-slate-400">
                        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800">Tgl: M</span>
                        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800">Bukti: N</span>
                        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800">Ket: O</span>
                        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800">Akun: S</span>
                        <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-semibold">Debit: T</span>
                        <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-semibold">Kredit: U</span>
                    </div>
                </div>
            </div>

            <!-- USER CUSTOM PRESETS TABLE -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50/80 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            Daftar Preset Mapping Kustom Pengguna
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Preset yang Anda simpan saat konfigurasi langkah pemetaan kolom.</p>
                    </div>

                    <button 
                        type="button" 
                        wire:click="openCreatePresetModal"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs shadow-md shadow-emerald-500/20 active:scale-[0.98] transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah Preset
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-5 py-3.5">NAMA PRESET</th>
                                <th class="px-5 py-3.5 text-center font-mono">BARIS AWAL</th>
                                <th class="px-5 py-3.5 text-center">SHEET TARGET</th>
                                <th class="px-5 py-3.5">KONFIGURASI PEMETAAN KOLOM</th>
                                <th class="px-5 py-3.5 text-center">DIBUAT PADA</th>
                                <th class="px-5 py-3.5 text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($presets as $preset)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="px-5 py-3.5 font-bold text-slate-800 dark:text-slate-100">
                                        {{ $preset->name }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center font-mono font-bold text-slate-700 dark:text-slate-200">
                                        Baris {{ $preset->start_row }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-mono text-[11px]">
                                            {{ $preset->sheet_name ?: 'Sheet Aktif' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @php $cfg = $preset->mapping_config ?? []; @endphp
                                        <div class="flex flex-wrap gap-1 font-mono text-[11px]">
                                            @if(!empty($cfg['col_date'])) <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 rounded">Tgl:{{ $cfg['col_date'] }}</span> @endif
                                            @if(!empty($cfg['col_doc_no'])) <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 rounded">No:{{ $cfg['col_doc_no'] }}</span> @endif
                                            @if(!empty($cfg['col_desc'])) <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 rounded">Ket:{{ $cfg['col_desc'] }}</span> @endif
                                            @if(!empty($cfg['col_account'])) <span class="px-1.5 py-0.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 font-bold rounded">Akun:{{ $cfg['col_account'] }}</span> @endif
                                            @if(!empty($cfg['col_sub_account'])) <span class="px-1.5 py-0.5 bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 font-bold rounded">Sub:{{ $cfg['col_sub_account'] }}</span> @endif
                                            @if(!empty($cfg['col_unit'])) <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 rounded">Unit:{{ $cfg['col_unit'] }}</span> @endif
                                            @if(!empty($cfg['col_debit'])) <span class="px-1.5 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-bold rounded">D:{{ $cfg['col_debit'] }}</span> @endif
                                            @if(!empty($cfg['col_credit'])) <span class="px-1.5 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-bold rounded">K:{{ $cfg['col_credit'] }}</span> @endif
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-center text-slate-400 text-[11px] whitespace-nowrap">
                                        {{ $preset->created_at ? $preset->created_at->translatedFormat('d M Y') : '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <div class="inline-flex items-center justify-center gap-1">
                                            <button 
                                                type="button"
                                                wire:click="openEditPresetModal({{ $preset->id }})"
                                                title="Edit Preset"
                                                aria-label="Edit Preset {{ $preset->name }}"
                                                class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-500 dark:text-slate-400 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 shadow-2xs hover:shadow-md hover:shadow-indigo-500/20 transition-all duration-200">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button 
                                                type="button"
                                                wire:click="deletePreset({{ $preset->id }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus preset '{{ $preset->name }}' ini?"
                                                title="Hapus Preset"
                                                aria-label="Hapus Preset {{ $preset->name }}"
                                                class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-500 dark:text-slate-400 hover:bg-rose-600 hover:text-white hover:border-rose-600 shadow-2xs hover:shadow-md hover:shadow-rose-500/20 transition-all duration-200">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400">
                                        Belum ada preset mapping kustom yang disimpan. Anda dapat menyimpannya saat melakukan konfigurasi import pada Langkah 2 atau menekan tombol Tambah Preset di atas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL SIMPAN PRESET BARU -->
    @if ($showSavePresetModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                        Simpan Preset Mapping Kolom
                    </h3>
                    <button type="button" wire:click="$set('showSavePresetModal', false)" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            NAMA PRESET <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            wire:model="newPresetName" 
                            placeholder="Contoh: Format Rekening Koran BCA / Format POS" 
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500" />
                        @error('newPresetName') <span class="text-[11px] text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl space-y-1 text-xs text-slate-600 dark:text-slate-400 font-mono">
                        <div>Mulai Baris: <strong>{{ $startRow }}</strong></div>
                        <div>Tgl: <strong>{{ $colDate ?: '-' }}</strong> | No.Bukti: <strong>{{ $colDocNo ?: '-' }}</strong></div>
                        <div>Akun: <strong>{{ $colAccount ?: '-' }}</strong> | Sub: <strong>{{ $colSubAccount ?: '-' }}</strong></div>
                        <div>Unit: <strong>{{ $colUnit ?: '-' }}</strong></div>
                        <div>Debit: <strong>{{ $colDebit ?: '-' }}</strong> | Kredit: <strong>{{ $colCredit ?: '-' }}</strong></div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                    <button 
                        type="button" 
                        wire:click="$set('showSavePresetModal', false)" 
                        class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200">
                        Batal
                    </button>
                    <button 
                        type="button" 
                        wire:click="savePreset" 
                        class="px-4 py-2 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm">
                        Simpan Preset
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL EDIT PRESET -->
    @if ($showEditPresetModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if ($editingPresetId)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            @endif
                        </svg>
                        {{ $editingPresetId ? 'Edit Konfigurasi Preset Mapping' : 'Tambah Preset Mapping Baru' }}
                    </h3>
                    <button type="button" wire:click="$set('showEditPresetModal', false)" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            NAMA PRESET <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            wire:model="editPresetName" 
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500" />
                        @error('editPresetName') <span class="text-[11px] text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            MULAI BARIS KE- <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            wire:model="editStartRow" 
                            min="1"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500" />
                        @error('editStartRow') <span class="text-[11px] text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">KOLOM TANGGAL (misal: A, B)</label>
                            <input type="text" wire:model="editColDate" class="w-full px-3 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 uppercase font-mono" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">KOLOM NO. BUKTI</label>
                            <input type="text" wire:model="editColDocNo" class="w-full px-3 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 uppercase font-mono" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">KOLOM KETERANGAN</label>
                            <input type="text" wire:model="editColDesc" class="w-full px-3 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 uppercase font-mono" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">KOLOM KODE AKUN *</label>
                            <input type="text" wire:model="editColAccount" class="w-full px-3 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 uppercase font-mono" />
                            @error('editColAccount') <span class="text-[10px] text-rose-500 font-semibold">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">KOLOM SUB AKUN (OPSIONAL)</label>
                            <input type="text" wire:model="editColSubAccount" class="w-full px-3 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 uppercase font-mono" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">KOLOM UNIT (OPSIONAL)</label>
                            <input type="text" wire:model="editColUnit" class="w-full px-3 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 uppercase font-mono" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">KOLOM DEBIT *</label>
                            <input type="text" wire:model="editColDebit" class="w-full px-3 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 uppercase font-mono" />
                            @error('editColDebit') <span class="text-[10px] text-rose-500 font-semibold">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">KOLOM KREDIT *</label>
                            <input type="text" wire:model="editColCredit" class="w-full px-3 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 uppercase font-mono" />
                            @error('editColCredit') <span class="text-[10px] text-rose-500 font-semibold">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                    <button 
                        type="button" 
                        wire:click="$set('showEditPresetModal', false)" 
                        class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200">
                        Batal
                    </button>
                    <button 
                        type="button" 
                        wire:click="updatePreset" 
                        class="px-4 py-2 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white shadow-sm transition-all">
                        {{ $editingPresetId ? 'Simpan Perubahan' : 'Tambah Preset' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
