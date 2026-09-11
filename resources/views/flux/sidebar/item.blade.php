@blaze

@php $tooltipPosition = $tooltipPosition ??= $attributes->pluck('tooltip:position'); @endphp
@php $tooltipKbd = $tooltipKbd ??= $attributes->pluck('tooltip:kbd'); @endphp
@php $tooltip = $tooltip ??= $attributes->pluck('tooltip'); @endphp
@php $iconTrailing ??= $attributes->pluck('icon:trailing'); @endphp
@php $iconVariant ??= $attributes->pluck('icon:variant'); @endphp

@props([
    'tooltipPosition' => 'right',
    'tooltipKbd' => null,
    'tooltip' => null,
    'iconVariant' => 'outline',
    'iconTrailing' => null,
    'badgeColor' => null,
    'iconDot' => null,
    'accent' => true,
    'badge' => null,
    'icon' => null,
])

@php
$tooltip ??= $slot->isNotEmpty() ? (string) $slot : null;

// Size-up icons in square/icon-only buttons...
$iconClasses = Flux::classes('size-4')
    ->add('in-data-flux-sidebar-group-dropdown:text-zinc-400! dark:in-data-flux-sidebar-group-dropdown:text-white/80!')
    ->add('[[data-flux-sidebar-item]:hover_&]:text-current!')
    ->add('[[data-flux-sidebar-item][data-active]_&]:text-current!');

$classes = Flux::classes()
    ->add('h-9 in-data-flux-sidebar-on-mobile:h-10 relative flex items-center gap-3 rounded-xl transition-all duration-200 group/sidebar-item')
    ->add('in-data-flux-sidebar-collapsed-desktop:w-10 in-data-flux-sidebar-collapsed-desktop:justify-center')
    ->add('py-0 text-start w-full px-3 has-data-flux-navlist-badge:not-in-data-flux-sidebar-collapsed-desktop:pe-1.5 my-0.5')
    ->add('text-slate-600 dark:text-slate-400 font-medium text-sm')
    ->add([
        // Active / Current State
        'data-current:bg-indigo-50/90 dark:data-current:bg-indigo-950/60',
        'data-current:text-indigo-600 dark:data-current:text-indigo-400 font-semibold',
        'data-current:border-l-3 data-current:border-indigo-600 dark:data-current:border-indigo-500',
        'data-current:shadow-xs data-current:shadow-indigo-500/10',
        // Hover State (non-active)
        'hover:text-indigo-600 dark:hover:text-indigo-400',
        'hover:bg-slate-100/80 dark:hover:bg-slate-800/80',
        'border-l-3 border-transparent',
    ])
    // Override the default styles to match dropdowns for when the item is inside a collapsed group dropdown...
    ->add('in-data-flux-sidebar-group-dropdown:w-auto! in-data-flux-sidebar-group-dropdown:px-2!')
    ->add('in-data-flux-sidebar-group-dropdown:focus:outline-hidden!')
    ->add('in-data-flux-sidebar-group-dropdown:text-zinc-800! in-data-flux-sidebar-group-dropdown:bg-white! in-data-flux-sidebar-group-dropdown:hover:bg-zinc-50!')
    ->add('in-data-flux-sidebar-group-dropdown:data-active:bg-zinc-50!')
    ->add('dark:in-data-flux-sidebar-group-dropdown:text-white! dark:in-data-flux-sidebar-group-dropdown:bg-transparent! dark:in-data-flux-sidebar-group-dropdown:hover:bg-zinc-600! dark:in-data-flux-sidebar-group-dropdown:data-active:bg-zinc-600!')
    ;
@endphp

<flux:tooltip :position="$tooltipPosition">
    <flux:button-or-link :attributes="$attributes->class($classes)" data-flux-sidebar-item>
        <?php if ($icon): ?>
            <div class="relative">
                <?php if (is_string($icon) && $icon !== ''): ?>
                    <flux:icon :$icon :variant="$iconVariant" class="{!! $iconClasses !!}" />
                <?php else: ?>
                    {{ $icon }}
                <?php endif; ?>

                <?php if ($iconDot): ?>
                    <div class="absolute top-[-2px] end-[-2px]">
                        <div class="size-[6px] rounded-full bg-zinc-500 dark:bg-zinc-400"></div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($slot->isNotEmpty()): ?>
            <div class="
                in-data-flux-sidebar-collapsed-desktop:not-in-data-flux-sidebar-group-dropdown:hidden
                flex-1 text-sm font-medium truncate [[data-nav-footer]_&]:hidden [[data-nav-sidebar]_[data-nav-footer]_&]:block" data-content>{{ $slot }}</div>
        <?php endif; ?>

        <?php if (is_string($iconTrailing) && $iconTrailing !== ''): ?>
            <flux:icon :icon="$iconTrailing" :variant="$iconVariant" class="in-data-flux-sidebar-collapsed-desktop:not-in-data-flux-sidebar-group-dropdown:hidden size-4!" />
        <?php elseif ($iconTrailing): ?>
            {{ $iconTrailing }}
        <?php endif; ?>

        <?php if (isset($badge) && $badge !== ''): ?>
            <?php $badgeAttributes = Flux::attributesAfter('badge:', $attributes, ['color' => $badgeColor]); ?>
            <flux:navlist.badge :attributes="$badgeAttributes" class="in-data-flux-sidebar-collapsed-desktop:not-in-data-flux-sidebar-group-dropdown:hidden">{{ $badge }}</flux:navlist.badge>
        <?php endif; ?>
    </flux:button-or-link>

    <flux:tooltip.content :kbd="$tooltipKbd" class="not-in-data-flux-sidebar-collapsed-desktop:hidden in-data-flux-sidebar-group-dropdown:hidden cursor-default">
        {{ $tooltip }}
    </flux:tooltip.content>
</flux:tooltip>
