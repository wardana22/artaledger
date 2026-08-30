<div class="p-4 sm:p-5 space-y-3.5">
    <!-- TOP NAV TABS -->
    <x-journal-nav active="templates" />

    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                Template Jurnal Transaksi
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Kelola racikan template akun Debit & Kredit untuk transaksi berulang (seperti Beban Sewa, Listrik, Gaji, dll).
            </p>
        </div>

        <button 
            wire:click="openCreateModal"
            class="inline-flex items-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-xs rounded-lg shadow-md shadow-indigo-500/20 transition-all duration-150 gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Template Baru
        </button>
    </div>

    <!-- FLASH MESSAGES -->
    @if (session()->has('message'))
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 rounded-xl text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ session('message') }}
        </div>
    @endif

    <!-- SEARCH TOOLBAR -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-3 rounded-xl shadow-xs flex items-center justify-between">
        <div class="relative w-full sm:w-80">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input 
                wire:model.live.debounce.300ms="search" 
                type="text" 
                placeholder="Cari kode, nama template..." 
                class="w-full pl-9 pr-4 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-200 transition-all"
            />
        </div>
    </div>

    <!-- TEMPLATES TABLE CARD -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-2.5 w-12 text-center">#</th>
                        <th class="px-4 py-2.5 w-36">Kode Template</th>
                        <th class="px-4 py-2.5 w-64">Nama & Jenis Jurnal</th>
                        <th class="px-4 py-2.5">Susunan Akun (Racikan Template)</th>
                        <th class="px-4 py-2.5 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($templates as $index => $tpl)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-4 py-2.5 text-center text-slate-400 font-mono">
                                {{ $templates->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-2.5 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $tpl->template_code }}
                            </td>
                            <td class="px-4 py-2.5 font-bold text-slate-800 dark:text-slate-100">
                                {{ $tpl->name }}
                                @if ($tpl->journalType)
                                    <span class="block text-[11px] font-semibold text-slate-400 font-mono">
                                        Jenis: {{ $tpl->journalType->code }} - {{ $tpl->journalType->name }}
                                    </span>
                                @endif
                                @if ($tpl->description)
                                    <span class="block text-[11px] text-slate-500 font-normal italic">
                                        "{{ $tpl->description }}"
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="space-y-1 max-w-lg">
                                    @foreach ($tpl->lines as $line)
                                        <div class="flex items-center justify-between text-[11px] p-1 bg-slate-50 dark:bg-slate-800/60 rounded-md border border-slate-200/60 dark:border-slate-700/60">
                                            <div>
                                                <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $line->account?->code }}</span>
                                                <span class="font-medium text-slate-700 dark:text-slate-300">- {{ $line->account?->name }}</span>
                                                @if ($line->unit)
                                                    <span class="px-1 text-[9px] font-bold bg-indigo-500/10 text-indigo-600 rounded">[{{ $line->unit->code }}]</span>
                                                @endif
                                            </div>
                                            <div class="font-mono font-bold text-[10px]">
                                                @if ($line->debit > 0)
                                                    <span class="text-indigo-600">D: {{ number_format($line->debit, 0, ',', '.') }}</span>
                                                @elseif ($line->credit > 0)
                                                    <span class="text-purple-600">K: {{ number_format($line->credit, 0, ',', '.') }}</span>
                                                @else
                                                    <span class="text-slate-400">D/K: 0</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-2.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button 
                                        wire:click="openEditModal({{ $tpl->id }})"
                                        title="Edit Template {{ $tpl->name }}"
                                        class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 shadow-2xs hover:shadow-md hover:shadow-indigo-500/20 transition-all duration-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>

                                    <button 
                                        wire:click="deleteTemplate({{ $tpl->id }})"
                                        wire:confirm="Apakah Anda yakin ingin menghapus template '{{ $tpl->name }}'?"
                                        title="Hapus Template"
                                        class="p-1.5 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-rose-600 hover:text-white hover:border-rose-600 shadow-2xs hover:shadow-md hover:shadow-rose-500/20 transition-all duration-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-slate-400">
                                Belum ada template jurnal dibuat. Klik <strong>"Tambah Template Baru"</strong> untuk membuat template transaksi berulang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-custom-pagination :paginator="$templates" />

    <!-- MODAL CREATE / EDIT TEMPLATE -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh]">
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                        {{ $editingTemplateId ? 'Edit Template Jurnal' : 'Buat Template Jurnal Baru' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveTemplate" class="p-4 space-y-4 overflow-y-auto flex-1">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Kode Template *</label>
                            <input wire:model="template_code" type="text" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs md:text-sm font-mono font-bold dark:text-slate-100" required />
                            @error('template_code') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Nama Template *</label>
                            <input wire:model="name" type="text" placeholder="misal: Beban Sewa Kantor Bulanan" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs md:text-sm font-bold dark:text-slate-100" required />
                            @error('name') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Jenis Jurnal *</label>
                            <select wire:model="journal_type_id" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs md:text-sm font-semibold dark:text-slate-100">
                                @foreach ($journalTypes as $jt)
                                    <option value="{{ $jt->id }}">{{ $jt->code }} - {{ $jt->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Keterangan Utama Default</label>
                            <input wire:model="description" type="text" placeholder="Contoh: Pembayaran Beban Sewa Kantor" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs md:text-sm dark:text-slate-100" />
                        </div>
                    </div>

                    <!-- LINES BUILDER -->
                    <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Susunan Akun Debit & Kredit Default (Racikan Template)</label>
                            <button type="button" wire:click="addLine" class="px-2.5 py-1 bg-indigo-600 text-white text-xs font-bold rounded-md">
                                + Tambah Baris
                            </button>
                        </div>

                        <div class="space-y-2 max-h-60 overflow-y-auto">
                            @foreach ($lines as $index => $line)
                                <div class="grid grid-cols-12 gap-2 items-center p-2 bg-slate-50 dark:bg-slate-800/60 rounded-lg border border-slate-200 dark:border-slate-700">
                                    <div class="col-span-4">
                                        <select wire:model="lines.{{ $index }}.account_id" class="w-full px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-xs font-mono dark:text-slate-100">
                                            <option value="">-- Pilih Akun --</option>
                                            @foreach ($accounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-3">
                                        <select wire:model="lines.{{ $index }}.unit_id" class="w-full px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-xs font-semibold dark:text-slate-100">
                                            <option value="">-- Unit (Opsional) --</option>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->code }} - {{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-2">
                                        <input wire:model="lines.{{ $index }}.debit" type="number" step="0.01" placeholder="Debit" class="w-full px-2 py-1 text-right font-mono font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-xs text-indigo-600" />
                                    </div>
                                    <div class="col-span-2">
                                        <input wire:model="lines.{{ $index }}.credit" type="number" step="0.01" placeholder="Kredit" class="w-full px-2 py-1 text-right font-mono font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-xs text-purple-600" />
                                    </div>
                                    <div class="col-span-1 text-center">
                                        <button type="button" wire:click="removeLine({{ $index }})" class="text-slate-400 hover:text-rose-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                        <button 
                            type="button" 
                            wire:click="$set('showModal', false)" 
                            class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg">
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-md shadow-indigo-500/20 transition-all">
                            Simpan Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
