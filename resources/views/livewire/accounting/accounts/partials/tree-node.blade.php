@props(['account', 'depth' => 0])

@php
    $isExpanded = in_array($account->id, $expandedAccountIds);
    $hasChildren = $account->children->count() > 0 || $account->is_group;
    $indentClasses = [
        0 => 'pl-3',
        1 => 'pl-8 sm:pl-10',
        2 => 'pl-14 sm:pl-16',
        3 => 'pl-20 sm:pl-24',
        4 => 'pl-26 sm:pl-32',
    ][$depth] ?? 'pl-32';
@endphp

<div class="tree-node-wrapper group border-b border-slate-100 dark:border-slate-800/80 last:border-0">
    <div class="flex items-center justify-between py-2.5 px-3 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors {{ $depth === 0 ? 'bg-slate-50/40 dark:bg-slate-800/30 font-bold' : '' }}">
        
        <!-- Left: Tree Structure, Icon, Code & Name -->
        <div class="flex items-center gap-2.5 min-w-0 flex-1 {{ $indentClasses }} relative">
            
            <!-- File Explorer Tree Branch Line Indicator for Level > 0 -->
            @if ($depth > 0)
                <div class="absolute left-[-16px] top-0 bottom-1/2 w-3 border-l-2 border-b-2 border-slate-300 dark:border-slate-700 rounded-bl-sm pointer-events-none"></div>
            @endif

            <!-- Expand / Collapse Chevron Button (For Group Accounts) -->
            @if ($hasChildren)
                <button 
                    wire:click="toggleExpand({{ $account->id }})"
                    type="button" 
                    class="p-1 rounded-md text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-500/10 transition-all shrink-0">
                    <svg class="w-4 h-4 transform transition-transform duration-200 {{ $isExpanded ? 'rotate-90 text-indigo-500' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            @else
                <span class="w-6 shrink-0 inline-block"></span>
            @endif

            <!-- Node Type Icon (Folder for Group, File for Posting) -->
            @if ($account->is_group)
                <div 
                    wire:click="toggleExpand({{ $account->id }})" 
                    class="cursor-pointer shrink-0 text-amber-500 dark:text-amber-400">
                    @if ($isExpanded)
                        <!-- Open Folder Icon -->
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2-2M5 19l2-8h14"></path>
                        </svg>
                    @else
                        <!-- Closed Folder Icon -->
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                        </svg>
                    @endif
                </div>
            @else
                <!-- Document / Posting Icon -->
                <div class="shrink-0 text-slate-400 dark:text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            @endif

            <!-- Account Code -->
            <span class="font-mono text-xs font-bold px-2 py-0.5 rounded border shrink-0 {{ $account->is_group ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700' }}">
                {{ $account->code }}
            </span>

            <!-- Account Name -->
            <span class="truncate text-xs md:text-sm {{ $account->is_group ? 'font-bold text-slate-900 dark:text-slate-100' : 'font-medium text-slate-700 dark:text-slate-300' }}">
                {{ $account->name }}
            </span>

            <!-- Children Count Badge (for Group Accounts) -->
            @if ($account->is_group && $account->children->count() > 0)
                <span class="hidden sm:inline-flex px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 shrink-0">
                    {{ $account->children->count() }} Sub-Akun
                </span>
            @endif
        </div>

        <!-- Right: Badges & Action Buttons -->
        <div class="flex items-center gap-3 shrink-0 ml-2">
            
            <!-- Badges (Hidden on mobile for compactness) -->
            <div class="hidden md:flex items-center gap-2">
                <!-- Normal Balance Badge -->
                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md border {{ $account->normal_balance === 'debit' ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20' : 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20' }}">
                    {{ $account->normal_balance }}
                </span>

                <!-- Report Type Badge -->
                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md border {{ $account->report_type === 'neraca' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20' }}">
                    {{ $account->report_type === 'neraca' ? 'Neraca' : 'Laba Rugi' }}
                </span>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-1">
                <!-- Add Child Account (If Group) -->
                @if ($account->is_group)
                    @can('accounts.create')
                        <button 
                            wire:click="createChildAccount({{ $account->id }})"
                            title="Tambah Sub-Akun di bawah {{ $account->name }}"
                            class="p-1.5 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 rounded-lg transition-all shadow-xs flex items-center gap-1 text-[11px] font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span class="hidden sm:inline">Sub</span>
                        </button>
                    @endcan
                @endif

                <!-- Edit Button -->
                @can('accounts.edit')
                    <button 
                        wire:click="editAccount({{ $account->id }})"
                        title="Edit {{ $account->name }}"
                        class="p-1.5 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-500/30 rounded-lg transition-all shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                @endcan

                <!-- Delete Button -->
                @can('accounts.delete')
                    <button 
                        wire:click="deleteAccount({{ $account->id }})"
                        wire:confirm="Apakah Anda yakin ingin menghapus akun '{{ $account->name }}'?"
                        title="Hapus {{ $account->name }}"
                        class="p-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 rounded-lg transition-all shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                @endcan
            </div>
        </div>
    </div>

    <!-- Recursive Sub-Accounts Rendering (If Expanded) -->
    @if ($account->is_group && $isExpanded && $account->children->count() > 0)
        <div class="relative border-l border-slate-200/60 dark:border-slate-800 ml-5">
            @foreach ($account->children as $child)
                @include('livewire.accounting.accounts.partials.tree-node', ['account' => $child, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
