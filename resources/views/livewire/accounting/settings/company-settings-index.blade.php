<div>
    <x-settings-nav active="company" />

    <div class="space-y-6">
        @if (session()->has('message'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-sm font-semibold animate-fade-in">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ session('message') }}</span>
            </div>
        @endif

        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Pengaturan Branding & Perusahaan</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola nama aplikasi, logo identitas, serta rincian legalitas perusahaan utama.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800">
                    <span class="size-2 rounded-full bg-indigo-500 animate-pulse"></span>
                    Branding & Profile
                </span>
            </div>

            <form wire:submit.prevent="save" class="mt-6 space-y-8">
                <!-- Section 1: Logo & Branding Header -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-900 dark:text-white">Logo Aplikasi & Perusahaan</label>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Unggah logo resmi (format PNG, JPG, WebP, atau SVG). Logo ini akan ditampilkan pada header sidebar dan dokumen laporan formal.</p>
                    </div>

                    <div class="lg:col-span-2 flex flex-col sm:flex-row items-center gap-6">
                        <div class="relative group">
                            <div class="size-24 rounded-2xl bg-slate-100 dark:bg-slate-800 border-2 border-dashed border-slate-300 dark:border-slate-700 flex items-center justify-center overflow-hidden shadow-inner">
                                @if ($logo)
                                    <img src="{{ $logo->temporaryUrl() }}" class="size-full object-contain p-2" alt="Preview Logo" />
                                @elseif ($company?->logo_url)
                                    <img src="{{ $company->logo_url }}" class="size-full object-contain p-2" alt="Logo Perusahaan" />
                                @else
                                    <div class="flex flex-col items-center gap-1 text-slate-400 dark:text-slate-500">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-[10px] font-semibold">Tidak ada logo</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-3 w-full sm:w-auto">
                            <div class="flex items-center gap-3">
                                <label class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all cursor-pointer shadow-md shadow-indigo-500/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                    </svg>
                                    <span>Unggah Logo Baru</span>
                                    <input type="file" wire:model="logo" accept="image/*" class="hidden" />
                                </label>

                                @if ($company?->logo_path || $logo)
                                    <button type="button" wire:click="removeLogo" wire:confirm="Apakah Anda yakin ingin menghapus logo ini?" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-bold transition-all border border-rose-500/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Hapus
                                    </button>
                                @endif
                            </div>

                            <p class="text-[11px] text-slate-400 dark:text-slate-500">Ukuran berkas maks 2MB. Format direkomendasikan: PNG / SVG dengan latar belakang transparan.</p>
                            @error('logo') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-slate-200 dark:border-slate-800" />

                <!-- Section 2: Nama Aplikasi & Identitas Utama -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-900 dark:text-white">Identitas Aplikasi & Perusahaan</label>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pengaturan ini mengontrol nama sistem yang tampil pada bilah samping navigasi dan judul peramban.</p>
                    </div>

                    <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="app_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Nama Aplikasi *</label>
                            <input type="text" id="app_name" wire:model="app_name" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" placeholder="Contoh: ArtaLedger" />
                            @error('app_name') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Nama Perusahaan Utama *</label>
                            <input type="text" id="name" wire:model="name" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" placeholder="Contoh: PT Arta Ledger Indonesia" />
                            @error('name') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="code" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Kode Perusahaan *</label>
                            <input type="text" id="code" wire:model="code" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-semibold uppercase focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" placeholder="Contoh: ALT" />
                            @error('code') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="tax_number" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">NPWP / Tax ID</label>
                            <input type="text" id="tax_number" wire:model="tax_number" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" placeholder="00.000.000.0-000.000" />
                            @error('tax_number') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-slate-200 dark:border-slate-800" />

                <!-- Section 3: Alamat & Kontak -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-900 dark:text-white">Kontak & Alamat Kantor Pusat</label>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Informasi ini dicantumkan pada bagian kop laporan keuangan yang diekspor/dicetak.</p>
                    </div>

                    <div class="lg:col-span-2 space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Email Perusahaan</label>
                                <input type="email" id="email" wire:model="email" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" placeholder="info@artaledger.com" />
                                @error('email') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Nomor Telepon</label>
                                <input type="text" id="phone" wire:model="phone" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" placeholder="+62 21 555-0199" />
                                @error('phone') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="address" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Alamat Lengkap Kantor Pusat</label>
                            <textarea id="address" wire:model="address" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" placeholder="Jl. Sudirman No. 45, Jakarta Selatan 12190"></textarea>
                            @error('address') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-800">
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold transition-all shadow-lg shadow-indigo-500/25 disabled:opacity-50">
                        <svg wire:loading class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
