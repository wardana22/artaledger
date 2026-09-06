<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

@php
    $headCompany = \App\Models\Company::first();
    $headAppName = $headCompany?->app_name ?? config('app.name', 'E-counting');
    $headFaviconUrl = $headCompany?->logo_url ?? asset('favicon.png');
@endphp

<title>
    {{ filled($title ?? null) ? $title.' - '.$headAppName : $headAppName }}
</title>

<link rel="icon" href="{{ $headFaviconUrl }}">
<link rel="shortcut icon" href="{{ $headFaviconUrl }}">
<link rel="apple-touch-icon" href="{{ $headFaviconUrl }}">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
