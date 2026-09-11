@blaze(fold: true)

@props([
    'kbd' => null,
])

@php
$classes = Flux::classes([
    'relative py-1.5 px-3',
    'rounded-lg shadow-xl shadow-slate-950/20',
    'text-xs text-white font-medium tracking-wide',
    'bg-slate-900 border border-slate-700/80 dark:bg-slate-800 dark:border-slate-700',
    'p-0 overflow-visible z-50',
]);
@endphp

<div popover="manual" {{ $attributes->class($classes) }} data-flux-tooltip-content>
    {{ $slot }}

    <?php if ($kbd): ?>
        <span class="ps-1 text-zinc-300">{{ $kbd }}</span>
    <?php endif; ?>
</div>
