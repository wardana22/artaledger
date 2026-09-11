@blaze(fold: true, unsafe: ['tooltip:position', 'tooltip:kbd', 'tooltip'])

@php $tooltipPosition = $tooltipPosition ??= $attributes->pluck('tooltip:position'); @endphp
@php $tooltipKbd = $tooltipKbd ??= $attributes->pluck('tooltip:kbd'); @endphp
@php $tooltip = $tooltip ??= $attributes->pluck('tooltip'); @endphp

@props([
    'tooltipPosition' => 'right',
    'tooltipKbd' => null,
    'tooltip' => __('Toggle sidebar'),
    'inset' => null,
])

@php
$classes = Flux::classes()
    ->add('w-8 h-8 shrink-0 flex items-center justify-center')
    ->add($inset ? Flux::applyInset($inset, top: '-mt-2.5', right: '-me-2.5', bottom: '-mb-2.5', left: '-ms-2.5') : '')
    ;

$buttonClasses = Flux::classes()
    ->add('size-8 relative items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none text-sm rounded-lg inline-flex bg-slate-100/80 hover:bg-indigo-50 dark:bg-slate-800/80 dark:hover:bg-slate-700 text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-white transition-all duration-200 cursor-pointer shadow-2xs border border-slate-200/60 dark:border-slate-700/60')
    ->add('rtl:rotate-180')
    ;
@endphp

<ui-sidebar-toggle {{ $attributes->class($classes) }} data-flux-sidebar-collapse>
    <flux:tooltip :content="$tooltip" :position="$tooltipPosition" :kbd="$tooltipKbd">
        <button type="button" class="{{ $buttonClasses }}">
            <svg class="text-zinc-500 dark:text-zinc-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7.5 3.75V16.25M3.4375 16.25H16.5625C17.08 16.25 17.5 15.83 17.5 15.3125V4.6875C17.5 4.17 17.08 3.75 16.5625 3.75H3.4375C2.92 3.75 2.5 4.17 2.5 4.6875V15.3125C2.5 15.83 2.92 16.25 3.4375 16.25Z" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </button>
    </flux:tooltip>
</ui-sidebar-toggle>
