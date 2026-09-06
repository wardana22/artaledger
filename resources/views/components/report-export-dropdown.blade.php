@props([
    'pdfUrl',
    'excelUrl',
    'label' => 'Cetak / Ekspor',
])

<div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block text-left z-20">
    <!-- Trigger Button -->
    <button 
        @click="open = !open" 
        type="button"
        class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 flex items-center gap-2 transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
        aria-haspopup="true" 
        :aria-expanded="open">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        <span>{{ $label }}</span>
        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <!-- Dropdown Menu -->
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        class="absolute right-0 mt-2 w-56 rounded-2xl bg-white dark:bg-slate-900 shadow-2xl border border-slate-200/80 dark:border-slate-800 py-1.5 z-50 divide-y divide-slate-100 dark:divide-slate-800"
        style="display: none;">
        
        <div class="px-3.5 py-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
            Pilih Format Ekspor
        </div>

        <div class="py-1">
            <!-- Option 1: PDF -->
            <a 
                href="{{ $pdfUrl }}" 
                target="_blank"
                @click="open = false"
                class="flex items-center gap-3 px-3.5 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors group">
                <span class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center group-hover:scale-105 transition-transform shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </span>
                <div class="flex flex-col text-left">
                    <span class="font-bold text-slate-900 dark:text-white">Cetak / Unduh PDF</span>
                    <span class="text-[10px] text-slate-400 font-normal">Format cetak siap dokumen</span>
                </div>
            </a>

            <!-- Option 2: Excel -->
            <a 
                href="{{ $excelUrl }}" 
                @click="open = false"
                class="flex items-center gap-3 px-3.5 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors group">
                <span class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:scale-105 transition-transform shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <div class="flex flex-col text-left">
                    <span class="font-bold text-slate-900 dark:text-white">Download Excel (.xlsx)</span>
                    <span class="text-[10px] text-slate-400 font-normal">Spreadsheet dapat diedit</span>
                </div>
            </a>
        </div>
    </div>
</div>
