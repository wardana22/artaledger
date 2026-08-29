@props(['active' => null])

@php
    $currentStatus = request()->get('status');
    $isDraftActive = $active === 'draft' || ($active === null && request()->routeIs('accounting.journals.index') && $currentStatus === 'draft');
    $isTemplatesActive = $active === 'templates' || ($active === null && (request()->routeIs('accounting.journals.templates.*') || request()->is('accounting/journals/templates*')));
    $isJurnalUmumActive = $active === 'jurnal-umum' || ($active === null && request()->routeIs('accounting.journals.index') && $currentStatus !== 'draft');
    $isAjpActive = $active === 'ajp' || ($active === null && (request()->routeIs('accounting.adjustments.*') || request()->is('accounting/adjustments*')));
@endphp

<div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-2 mb-3.5 overflow-x-auto">
    <a 
        href="{{ route('accounting.journals.index') }}" 
        wire:navigate
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $isJurnalUmumActive ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 ring-2 ring-indigo-400/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Jurnal Umum (Posted)
    </a>

    <a 
        href="{{ route('accounting.journals.index', ['status' => 'draft']) }}" 
        wire:navigate
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $isDraftActive ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 ring-2 ring-indigo-400/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
        <svg class="w-4 h-4 {{ $isDraftActive ? 'text-white' : 'text-amber-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
        </svg>
        Draft Jurnal
    </a>

    <a 
        href="{{ route('accounting.journals.templates.index') }}" 
        wire:navigate
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $isTemplatesActive ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 ring-2 ring-indigo-400/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
        <svg class="w-4 h-4 {{ $isTemplatesActive ? 'text-white' : 'text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
        </svg>
        Template Jurnal
    </a>

    <a 
        href="{{ route('accounting.adjustments.index') }}" 
        wire:navigate
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $isAjpActive ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 ring-2 ring-indigo-400/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
        Jurnal Penyesuaian (AJP)
    </a>
</div>
