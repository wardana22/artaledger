@php
    $isWorksheetActive = request()->routeIs('accounting.reports.worksheet*') || request()->is('accounting/reports/worksheet*');
    $isTrialBalanceActive = request()->routeIs('accounting.reports.trial-balance*') || request()->is('accounting/reports/trial-balance*');
    $isBalanceSheetActive = request()->routeIs('accounting.reports.balance-sheet*') || request()->is('accounting/reports/balance-sheet*');
@endphp

<div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3 mb-6 overflow-x-auto">
    <a 
        href="{{ route('accounting.reports.worksheet') }}" 
        wire:navigate
        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $isWorksheetActive ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 ring-2 ring-indigo-400/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
        </svg>
        Neraca Lajur
    </a>

    <a 
        href="{{ route('accounting.reports.trial-balance') }}" 
        wire:navigate
        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $isTrialBalanceActive ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 ring-2 ring-indigo-400/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 17h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M12 7h.01M15 7h.01"></path>
        </svg>
        Neraca Saldo
    </a>

    <a 
        href="{{ route('accounting.reports.balance-sheet') }}" 
        wire:navigate
        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $isBalanceSheetActive ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 ring-2 ring-indigo-400/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
        </svg>
        Laporan Neraca
    </a>
</div>
