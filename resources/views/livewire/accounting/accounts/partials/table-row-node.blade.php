@props(['account', 'depth' => 0])

@php
    $isExpanded = in_array($account->id, $expandedAccountIds);
    $hasChildren = $account->children->count() > 0 || $account->is_group;
    $paddingLeft = ($depth * 24) + 12;
@endphp

<tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors {{ $depth === 0 ? 'bg-slate-50/50 dark:bg-slate-800/30 font-bold' : '' }} border-b border-slate-100 dark:border-slate-800/60">
    <!-- 1. KODE AKUN -->
    <td class="px-4 py-2.5 font-mono font-bold text-slate-800 dark:text-slate-100 whitespace-nowrap text-xs">
        <span class="px-2 py-0.5 rounded border {{ $account->is_group ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700' }}">
            {{ $account->code }}
        </span>
    </td>

    <!-- 2. NAMA AKUN WITH INDENTATION & ACCORDION TOGGLE -->
    <td class="px-4 py-2.5 font-medium text-slate-800 dark:text-slate-100 min-w-[280px]">
        <div class="flex items-center gap-2" style="padding-left: {{ $paddingLeft }}px">
            
            <!-- Expand / Collapse Chevron Button for Group Accounts -->
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

            <!-- Icon (Folder for Group, File for Posting) -->
            @if ($account->is_group)
                <div 
                    wire:click="toggleExpand({{ $account->id }})" 
                    class="cursor-pointer shrink-0 text-amber-500 dark:text-amber-400">
                    @if ($isExpanded)
                        <!-- Open Folder Icon -->
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2-2M5 19l2-8h14"></path>
                        </svg>
                    @else
                        <!-- Closed Folder Icon -->
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                        </svg>
                    @endif
                </div>
            @else
                <div class="shrink-0 text-slate-400 dark:text-slate-500">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            @endif

            <!-- Account Name -->
            <span class="truncate text-xs md:text-sm {{ $account->is_group ? 'font-bold text-slate-900 dark:text-slate-100' : 'font-medium text-slate-700 dark:text-slate-300' }}">
                {{ $account->name }}
            </span>
        </div>
    </td>

    <!-- 3. TIPE / KATEGORI -->
    <td class="px-4 py-2.5 text-xs font-medium text-slate-500 dark:text-slate-400 uppercase whitespace-nowrap">
        {{ $account->type ?? '-' }}
    </td>

    <!-- 4. STATUS (HEADER / POSTING) -->
    <td class="px-4 py-2.5 text-center whitespace-nowrap">
        @if ($account->is_group)
            <span class="inline-block px-2.5 py-0.5 text-[10px] font-extrabold tracking-wider rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/25 shadow-2xs whitespace-nowrap">
                HEADER (GROUP)
            </span>
        @else
            <span class="inline-block px-2.5 py-0.5 text-[10px] font-extrabold tracking-wider rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/25 shadow-2xs whitespace-nowrap">
                POSTING
            </span>
        @endif
    </td>

    <!-- 5. SALDO NORMAL -->
    <td class="px-4 py-2.5 text-xs font-bold uppercase tracking-wider whitespace-nowrap">
        <span class="{{ $account->normal_balance === 'debit' ? 'text-indigo-600 dark:text-indigo-400' : 'text-purple-600 dark:text-purple-400' }}">
            {{ $account->normal_balance }}
        </span>
    </td>

    <!-- 6. LAPORAN -->
    <td class="px-4 py-2.5 text-xs capitalize font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap">
        {{ str_replace('_', ' ', $account->report_type) }}
    </td>

    <!-- 7. AKSI -->
    <td class="px-4 py-2.5 text-right whitespace-nowrap">
        <div class="inline-flex items-center justify-end gap-1">
            @if ($account->is_group)
                @can('accounts.create')
                    <button 
                        wire:click="createChildAccount({{ $account->id }})"
                        title="Tambah Sub-Akun"
                        class="p-1 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-600 dark:hover:text-emerald-400 hover:border-emerald-500/30 shadow-2xs transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </button>
                @endcan
            @endif

            @can('accounts.edit')
                <button 
                    wire:click="editAccount({{ $account->id }})"
                    title="Edit Akun"
                    class="p-1 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-indigo-500/10 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-500/30 shadow-2xs transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </button>
            @endcan

            @can('accounts.delete')
                <button 
                    wire:click="deleteAccount({{ $account->id }})"
                    wire:confirm="Apakah Anda yakin ingin menghapus akun ini?"
                    title="Hapus Akun"
                    class="p-1 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-400 dark:text-slate-400 hover:bg-rose-500/10 hover:text-rose-600 dark:hover:text-rose-400 hover:border-rose-500/30 shadow-2xs transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            @endcan
        </div>
    </td>
</tr>

<!-- RECURSIVE CHILDREN TABLE ROWS (IF EXPANDED) -->
@if ($account->is_group && $isExpanded && $account->children->count() > 0)
    @foreach ($account->children as $child)
        @include('livewire.accounting.accounts.partials.table-row-node', ['account' => $child, 'depth' => $depth + 1])
    @endforeach
@endif
