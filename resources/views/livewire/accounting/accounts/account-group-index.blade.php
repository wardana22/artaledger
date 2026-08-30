<div>
    <x-settings-nav active="coa" />

    <div class="space-y-6">
        @if (session()->has('message'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-sm font-semibold animate-fade-in">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ session('message') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-sm font-semibold animate-fade-in">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Header Card -->
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Grup Akun COA Kustom</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Kelola pengelompokan akun COA dinamis sebagai variabel rumus matematika KPI pada dashboard.</p>
                    </div>
                </div>
            </div>

            <button type="button" wire:click="openCreateGroupModal" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-md shadow-indigo-500/20 w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Tambah Grup Akun Baru</span>
            </button>
        </div>

        <!-- Group List Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($groups as $group)
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 relative group hover:border-indigo-500/30 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-extrabold tracking-wider font-mono
                            {{ $group->color_theme === 'emerald' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : '' }}
                            {{ $group->color_theme === 'rose' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' : '' }}
                            {{ $group->color_theme === 'indigo' ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20' : '' }}
                            {{ $group->color_theme === 'amber' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' : '' }}
                            {{ $group->color_theme === 'sky' ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20' : '' }}
                            {{ $group->color_theme === 'violet' ? 'bg-violet-500/10 text-violet-600 dark:text-violet-400 border border-violet-500/20' : '' }}
                        ">
                            [{{ $group->code }}]
                        </span>

                        <div class="flex items-center gap-1.5">
                            <button type="button" wire:click="openEditGroupModal({{ $group->id }})" class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all" title="Edit Grup">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            @if (!$group->is_system)
                                <button type="button" wire:click="deleteGroup({{ $group->id }})" wire:confirm="Apakah Anda yakin ingin menghapus grup akun ini?" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 transition-all" title="Hapus Grup">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h4 class="text-base font-bold text-slate-900 dark:text-white">{{ $group->name }}</h4>
                        @if ($group->description)
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">{{ $group->description }}</p>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2 text-xs">
                        <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Anggota Grup:</span>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($group->members as $m)
                                @if ($m->account_prefix)
                                    <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium text-[11px]">
                                        Awalan Kode: <strong class="text-indigo-600 dark:text-indigo-400">{{ $m->account_prefix }}*</strong>
                                    </span>
                                @elseif ($m->account_type)
                                    <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium text-[11px]">
                                        Tipe: <strong class="text-indigo-600 dark:text-indigo-400 uppercase">{{ $m->account_type }}</strong>
                                    </span>
                                @elseif ($m->account)
                                    <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium text-[11px]">
                                        {{ $m->account->code }} - {{ $m->account->name }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 text-center bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold">Belum ada Grup Akun COA Kustom. Klik tombol "Tambah Grup Akun Baru" untuk membuatnya.</p>
                </div>
            @endforelse
        </div>

        <!-- Modal CRUD Grup Akun -->
        @if ($showGroupModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in">
                <div class="w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                            {{ $editingGroupId ? 'Edit Grup Akun COA' : 'Tambah Grup Akun COA Baru' }}
                        </h3>
                        <button type="button" wire:click="$set('showGroupModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="saveGroup" class="p-6 space-y-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="code" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Kode Grup (Variabel) *</label>
                                <input type="text" id="code" wire:model="code" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold font-mono uppercase focus:ring-2 focus:ring-indigo-500 transition-all" placeholder="Contoh: COGS" />
                                <p class="text-[10px] text-slate-400 mt-1">Digunakan sebagai variabel formula: [COGS]</p>
                                @error('code') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="color_theme" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Tema Warna *</label>
                                <select id="color_theme" wire:model="color_theme" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-indigo-500 transition-all">
                                    <option value="indigo">Indigo Blue (Biru)</option>
                                    <option value="emerald">Emerald Green (Hijau)</option>
                                    <option value="rose">Rose Red (Merah)</option>
                                    <option value="amber">Amber Gold (Kuning)</option>
                                    <option value="sky">Sky Cyan (Biru Langit)</option>
                                    <option value="violet">Violet Purple (Ungu)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Nama Grup Akun *</label>
                            <input type="text" id="name" wire:model="name" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-semibold focus:ring-2 focus:ring-indigo-500 transition-all" placeholder="Contoh: Beban Pokok Pendapatan (COGS)" />
                            @error('name') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Keterangan / Catatan</label>
                            <textarea id="description" wire:model="description" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-indigo-500 transition-all" placeholder="Catatan opsional mengenai isi grup akun ini..."></textarea>
                        </div>

                        <hr class="border-slate-200 dark:border-slate-800" />

                        <!-- Penentuan Anggota Grup -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Metode Penentuan Anggota Grup *</label>
                            <select wire:model.live="member_mode" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                                <option value="prefix">Berdasarkan Awalan Kode COA (Contoh: Kepala 4 atau 5)</option>
                                <option value="type">Berdasarkan Tipe Kategori Akun (Contoh: PENDAPATAN, BEBAN)</option>
                                <option value="specific">Pilih Akun Spesifik Manual</option>
                            </select>
                        </div>

                        @if ($member_mode === 'prefix')
                            <div>
                                <label for="account_prefix" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Awalan Kode Akun (Prefix) *</label>
                                <select id="account_prefix" wire:model="account_prefix" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                                    <option value="4">Kepala 4 (Seluruh Akun Pendapatan)</option>
                                    <option value="5">Kepala 5 (Seluruh Akun Beban Pokok / HPP)</option>
                                    <option value="6">Kepala 6 (Seluruh Akun Beban Operasional)</option>
                                    <option value="7">Kepala 7 (Seluruh Akun Beban Umum)</option>
                                    <option value="8">Kepala 8 (Seluruh Akun Pendapatan Lain-lain)</option>
                                    <option value="9">Kepala 9 (Seluruh Akun Beban Lain-lain)</option>
                                    <option value="1">Kepala 1 (Seluruh Akun Aktiva / Aset)</option>
                                    <option value="11">Kepala 11 (Akun Kas & Kesetaraan Kas)</option>
                                    <option value="2">Kepala 2 (Seluruh Akun Kewajiban / Hutang)</option>
                                    <option value="3">Kepala 3 (Seluruh Akun Ekuitas / Modal)</option>
                                </select>
                            </div>
                        @elseif ($member_mode === 'type')
                            <div>
                                <label for="account_type" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Pilih Tipe Kategori Akun *</label>
                                <select id="account_type" wire:model="account_type" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-indigo-500 transition-all">
                                    <option value="PENDAPATAN">PENDAPATAN</option>
                                    <option value="BEBAN">BEBAN</option>
                                    <option value="BEBAN LAIN-LAIN">BEBAN LAIN-LAIN</option>
                                    <option value="KAS">KAS</option>
                                    <option value="BANK">BANK</option>
                                    <option value="PIUTANG">PIUTANG</option>
                                    <option value="AKTIVA TETAP">AKTIVA TETAP</option>
                                    <option value="HUTANG LANCAR">HUTANG LANCAR</option>
                                    <option value="MODAL">MODAL</option>
                                </select>
                            </div>
                        @else
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Pilih Akun Spesifik (Multi-Select)</label>
                                <div class="max-h-48 overflow-y-auto p-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 space-y-2">
                                    @foreach ($accounts as $acc)
                                        <label class="flex items-center gap-2 text-xs font-medium text-slate-800 dark:text-slate-200 cursor-pointer">
                                            <input type="checkbox" value="{{ $acc->id }}" wire:model="selectedAccountIds" class="size-4 rounded text-indigo-600 border-slate-300 dark:border-slate-700" />
                                            <span>{{ $acc->code }} - {{ $acc->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                            <button type="button" wire:click="$set('showGroupModal', false)" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-all">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-md shadow-indigo-500/20">
                                Simpan Grup Akun
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
