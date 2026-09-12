@props(['active' => null])

@php
    $isUsersActive = $active === 'users' || ($active === null && request()->routeIs('admin.users.*'));
    $isRolesActive = $active === 'roles' || ($active === null && request()->routeIs('admin.roles.*'));
    $isAuditLogsActive = $active === 'audit-logs' || ($active === null && (request()->routeIs('admin.audit-logs.*') || request()->is('admin/audit-logs*')));

    $userCount = \App\Models\User::count();
    $roleCount = \Spatie\Permission\Models\Role::count();
    $auditLogCount = \App\Models\AuditLog::count();
@endphp

<div class="flex items-center justify-between gap-3 border-b border-slate-200/80 dark:border-slate-800 pb-3 mb-4 overflow-x-auto">
    <div class="flex items-center gap-1.5 p-1 bg-slate-100/80 dark:bg-slate-900/60 backdrop-blur-md rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-2xs">
        @if (auth()->user()?->can('admin.users'))
            <a 
                href="{{ route('admin.users.index') }}" 
                wire:navigate
                class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all duration-200 whitespace-nowrap {{ $isUsersActive ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 ring-1 ring-indigo-400/50 scale-[1.01]' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-white/70 dark:hover:bg-slate-800/60' }}">
                <div class="w-5 h-5 rounded-lg flex items-center justify-center transition-colors {{ $isUsersActive ? 'bg-white/20 text-white' : 'bg-slate-200/60 dark:bg-slate-800 text-slate-500 group-hover:text-indigo-600' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <span>Daftar Pengguna</span>
                <span class="px-1.5 py-0.5 text-[10px] font-mono font-extrabold rounded-md {{ $isUsersActive ? 'bg-white/20 text-white' : 'bg-slate-200/70 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                    {{ $userCount }}
                </span>
            </a>
        @endif

        @if (auth()->user()?->can('admin.roles') || auth()->user()?->can('settings.manage_roles'))
            <a 
                href="{{ route('admin.roles.index') }}" 
                wire:navigate
                class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all duration-200 whitespace-nowrap {{ $isRolesActive ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 ring-1 ring-indigo-400/50 scale-[1.01]' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-white/70 dark:hover:bg-slate-800/60' }}">
                <div class="w-5 h-5 rounded-lg flex items-center justify-center transition-colors {{ $isRolesActive ? 'bg-white/20 text-white' : 'bg-slate-200/60 dark:bg-slate-800 text-slate-500 group-hover:text-indigo-600' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <span>Peran & Hak Akses (RBAC)</span>
                <span class="px-1.5 py-0.5 text-[10px] font-mono font-extrabold rounded-md {{ $isRolesActive ? 'bg-white/20 text-white' : 'bg-slate-200/70 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                    {{ $roleCount }}
                </span>
            </a>
        @endif

        @if (auth()->user()?->can('admin.audit_logs'))
            <a 
                href="{{ route('admin.audit-logs.index') }}" 
                wire:navigate
                class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all duration-200 whitespace-nowrap {{ $isAuditLogsActive ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 ring-1 ring-indigo-400/50 scale-[1.01]' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-white/70 dark:hover:bg-slate-800/60' }}">
                <div class="w-5 h-5 rounded-lg flex items-center justify-center transition-colors {{ $isAuditLogsActive ? 'bg-white/20 text-white' : 'bg-slate-200/60 dark:bg-slate-800 text-slate-500 group-hover:text-indigo-600' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                </div>
                <span>Audit Log Aktivitas</span>
                <span class="px-1.5 py-0.5 text-[10px] font-mono font-extrabold rounded-md {{ $isAuditLogsActive ? 'bg-white/20 text-white' : 'bg-slate-200/70 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                    {{ $auditLogCount }}
                </span>
            </a>
        @endif
    </div>

    <!-- Security Badge Indicator -->
    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span>RBAC & Multi-Unit Aktif</span>
    </div>
</div>
