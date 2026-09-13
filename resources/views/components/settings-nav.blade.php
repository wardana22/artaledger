@props(['active' => null])

@php
    $isCoaActive = $active === 'coa' || ($active === null && (request()->routeIs('accounting.accounts.*') || request()->routeIs('accounting.account-groups.*')));
    $isJournalTypeActive = $active === 'journal-types' || ($active === null && request()->routeIs('accounting.journal-types.*'));
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



    @if (auth()->user()?->can('settings.manage') || auth()->user()?->can('accounts.view') || auth()->user()?->hasRole('Super Admin'))
        @php
            $isInitialBalanceActive = $active === 'initial-balance' || request()->routeIs('accounting.initial-balance.*');
        @endphp
        <a 
            href="{{ route('accounting.initial-balance.index') }}" 
            wire:navigate
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $isInitialBalanceActive ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 ring-2 ring-indigo-400/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            Saldo Awal Perdana
        </a>
    @endif
</div>
