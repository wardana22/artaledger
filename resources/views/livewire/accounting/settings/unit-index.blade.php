<div class="p-6 space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M13 12h.01M13 16h.01M17 12h.01M17 16h.01"></path>
                </svg>
                Master Unit Perusahaan / Departemen
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Kelola data unit kerja/kantor cabang dan kata kunci (keywords) untuk pemetaan impor jurnal otomatis.
            </p>
        </div>

        <button 
            wire:click="openCreateModal"
            class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-medium text-sm rounded-xl shadow-lg shadow-indigo-500/25 transition-all duration-200 gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Unit Perusahaan
        </button>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 rounded-xl text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 rounded-xl text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Search Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm">
        <div class="relative w-full sm:w-80">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input 
                wire:model.live.debounce.300ms="search" 
                type="text" 
                placeholder="Cari kode, nama, atau kata kunci unit..." 
                class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-200"
            />
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5 w-12 text-center">#</th>
                        <th class="px-5 py-3.5 w-28">Kode Unit</th>
                        <th class="px-5 py-3.5 w-64">Nama Unit</th>
                        <th class="px-5 py-3.5">Kata Kunci Mapping (Keywords)</th>
                        <th class="px-5 py-3.5 text-center w-36">Penggunaan Line</th>
                        <th class="px-5 py-3.5 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($units as $index => $unit)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-5 py-3.5 text-center text-slate-400">
                                {{ $units->firstItem() + $index }}
                            </td>
                            <td class="px-5 py-3.5 font-mono">
                                <span class="px-2 py-0.5 text-xs font-extrabold rounded-md bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                    {{ $unit->code }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 font-bold text-slate-800 dark:text-slate-100">
                                {{ $unit->name }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                                {{ $unit->keywords ?: '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-center font-mono">
                                <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                    {{ $unit->journal_lines_count }} Baris
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap space-x-1">
                                <button 
                                    wire:click="openEditModal({{ $unit->id }})"
                                    class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-bold transition-all">
                                    Edit
                                </button>
                                <button 
                                    wire:click="delete({{ $unit->id }})"
                                    wire:confirm="Apakah Anda yakin ingin menghapus unit '{{ $unit->name }}'?"
                                    class="px-2.5 py-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 rounded-lg text-xs font-bold transition-all">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                Tidak ada data unit perusahaan ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-custom-pagination :paginator="$units" />

    <!-- MODAL FORM DIALOG -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">
                        {{ $editingId ? 'Edit Unit Perusahaan' : 'Tambah Unit Perusahaan Baru' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Kode Unit *</label>
                        <input 
                            wire:model="code" 
                            type="text" 
                            placeholder="misal: KP, RST, KU, KPN" 
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono font-bold focus:ring-2 focus:ring-indigo-500 uppercase dark:text-slate-100" 
                            required 
                        />
                        @error('code') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Nama Unit *</label>
                        <input 
                            wire:model="name" 
                            type="text" 
                            placeholder="misal: Kantor Pusat / RS Tandun" 
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:text-slate-100" 
                            required 
                        />
                        @error('name') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Kata Kunci Mapping (Keywords, pisahkan dengan koma)</label>
                        <textarea 
                            wire:model="keywords" 
                            rows="3" 
                            placeholder="misal: RST, TANDUN, RUMAH SAKIT TANDUN" 
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:text-slate-100"
                        ></textarea>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Sistem akan mencocokkan kata kunci ini dengan teks Keterangan pada file Excel impor untuk mendeteksi unit secara otomatis.
                        </p>
                        @error('keywords') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                        <button 
                            type="button" 
                            wire:click="$set('showModal', false)" 
                            class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-semibold rounded-xl">
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md shadow-indigo-500/20 transition-all">
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
