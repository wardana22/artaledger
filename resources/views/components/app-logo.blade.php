@props([
    'sidebar' => false,
])

@php
    $company = \App\Models\Company::first();
    $logoUrl = $company?->logo_url;
    $appName = $company?->app_name ?? config('app.name', 'ArtaLedger');
@endphp

@if ($logoUrl)
    <img src="{{ $logoUrl }}" alt="{{ $appName }}" {{ $attributes->merge(['class' => 'size-8 rounded-lg object-contain bg-white/10 dark:bg-slate-800/50 p-1 shadow-sm border border-slate-200/50 dark:border-slate-700/50']) }} />
@else
    <div {{ $attributes->merge(['class' => 'flex aspect-square size-8 items-center justify-center rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-sky-400 text-white shadow-md shadow-indigo-500/20 ring-1 ring-white/20 dark:ring-indigo-400/30']) }}>
        <svg class="size-5 fill-none stroke-current" viewBox="0 0 24 24" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
        </svg>
    </div>
@endif
