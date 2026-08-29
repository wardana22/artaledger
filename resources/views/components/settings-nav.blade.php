@props(['active' => null])

@php
    $isCoaActive = $active === 'coa' || ($active === null && request()->routeIs('accounting.accounts.*'));
    $isJournalTypeActive = $active === 'journal-types' || ($active === null && request()->routeIs('accounting.journal-types.*'));
    $isUnitsActive = $active === 'units' || ($active === null && request()->routeIs('accounting.units.*'));
@endphp

<div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-2 mb-3.5 overflow-x-auto">
    @if (auth()->user()?->can('accounts.view') || auth()->user()?->can('settings.manage'))
        <a 
            href="{{ route('accounting.accounts.index') }}" 
            wire:navigate
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $isCoaActive ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 ring-2 ring-indigo-400/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M3 7l9 6 9-6M3 7l9-6 9 6"></path>
            </svg>
            Master COA
        </a>
    @endif

    @if (auth()->user()?->can('settings.journal_types') || auth()->user()?->can('settings.manage'))
        <a 
            href="{{ route('accounting.journal-types.index') }}" 
            wire:navigate
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $isJournalTypeActive ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 ring-2 ring-indigo-400/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10m-8 5h8"></path>
            </svg>
            Jenis Jurnal
        </a>
    @endif

    @if (auth()->user()?->can('settings.units') || auth()->user()?->can('settings.manage'))
        <a 
            href="{{ route('accounting.units.index') }}" 
            wire:navigate
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $isUnitsActive ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 ring-2 ring-indigo-400/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            Unit Perusahaan
        </a>
    @endif
</div>
