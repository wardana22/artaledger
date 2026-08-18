@if ($paginator->hasPages() || $paginator->total() > 0)
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 py-3 px-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 text-sm text-slate-600 dark:text-slate-300">
        
        <!-- Left Section: Items Per Page & Item Range Count -->
        <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-start">
            <!-- Items per page dropdown -->
            <div class="flex items-center gap-2">
                <span class="hidden lg:inline text-xs font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap">
                    Items per page
                </span>
                <select 
                    wire:model.live="perPage" 
                    class="px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-slate-200">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            <!-- Item range info -->
            <div class="text-xs font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap">
                <span class="lg:hidden">
                    {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }}
                </span>
                <span class="hidden lg:inline">
                    {{ number_format($paginator->firstItem() ?? 0) }}-{{ number_format($paginator->lastItem() ?? 0) }} of {{ number_format($paginator->total()) }} items
                </span>
            </div>
        </div>

        <!-- Right Section: Navigation Controls -->
        <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto justify-center sm:justify-end">
            <!-- First Page (|<) -->
            <button 
                wire:click="gotoPage(1)" 
                @if ($paginator->onFirstPage()) disabled @endif
                title="First Page"
                class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
            </button>

            <!-- Previous Page (< / < Previous) -->
            <button 
                wire:click="previousPage" 
                @if ($paginator->onFirstPage()) disabled @endif
                class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-medium flex items-center gap-1 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <span class="hidden md:inline">Previous</span>
            </button>

            <!-- Page Number Input / Indicator -->
            <div class="flex items-center gap-1.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                <span class="hidden md:inline text-slate-500 dark:text-slate-400">Page</span>
                
                <input 
                    type="number" 
                    min="1" 
                    max="{{ $paginator->lastPage() }}"
                    value="{{ $paginator->currentPage() }}"
                    wire:change="gotoPage($event.target.value)"
                    class="w-11 px-1.5 py-1 text-center font-bold bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none text-xs dark:text-slate-100"
                />

                <span>of {{ $paginator->lastPage() }}</span>
            </div>

            <!-- Next Page (Next > / >) -->
            <button 
                wire:click="nextPage" 
                @if (!$paginator->hasMorePages()) disabled @endif
                class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-medium flex items-center gap-1 transition-all">
                <span class="hidden md:inline">Next</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>

            <!-- Last Page (>|) -->
            <button 
                wire:click="gotoPage({{ $paginator->lastPage() }})" 
                @if (!$paginator->hasMorePages()) disabled @endif
                title="Last Page"
                class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
    </div>
@endif
