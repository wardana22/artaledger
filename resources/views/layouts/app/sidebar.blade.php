<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <flux:sidebar sticky :collapsible="true" class="border-e border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900/95 transition-all duration-300">
            @php
                $company = \App\Models\Company::first();
                $appName = $company?->app_name ?? config('app.name', 'ArtaLedger');
            @endphp
            <flux:sidebar.header class="flex flex-col gap-2 p-2 min-h-fit">
                <!-- Baris Atas: Logo & Tombol Toggle Collapse -->
                <div class="flex items-center justify-between w-full">
                    <x-app-logo href="{{ route('dashboard') }}" wire:navigate class="shrink-0" />
                    <flux:sidebar.collapse class="shrink-0" :tooltip="__('Toggle sidebar')" />
                </div>

                <!-- Baris Bawah: Nama Aplikasi & Perusahaan (Di bawah logo, sembunyi saat collapsed via selektor data attribute yang pasti) -->
                <a href="{{ route('dashboard') }}" wire:navigate class="sidebar-brand-text flex flex-col overflow-hidden leading-tight pt-1.5 border-t border-slate-200/60 dark:border-slate-800">
                    <span class="text-xs font-black tracking-tight text-slate-900 dark:text-white truncate uppercase">{{ $appName }}</span>
                    <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 truncate">{{ $company?->name ?? 'PT ArtaLedger Enterprise' }}</span>
                </a>
            </flux:sidebar.header>

            <flux:sidebar.nav class="space-y-2">
                @if (auth()->user()?->can('dashboard.view') || auth()->user()?->can('reports.view') || auth()->user()?->hasRole('Super Admin'))
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:separator class="my-2 border-zinc-200/80 dark:border-zinc-800/80" />
                @endif
                @if (auth()->user()?->can('journals.view') || auth()->user()?->can('journals.import') || auth()->user()?->can('periods.view') || auth()->user()?->can('periods.manage'))
                    <flux:sidebar.group :heading="__('Manajemen Akuntansi')" class="grid">
                        @if (auth()->user()?->can('journals.view'))
                            <flux:sidebar.item icon="document-text" :href="route('accounting.journals.index')" :current="request()->routeIs('accounting.journals.*') || request()->routeIs('accounting.adjustments.*')" wire:navigate>
                                {{ __('Jurnal Transaksi') }}
                            </flux:sidebar.item>
                        @endif
                        @if (auth()->user()?->can('journals.import'))
                            <flux:sidebar.item icon="arrow-down-tray" :href="route('accounting.import.index')" :current="request()->routeIs('accounting.import.*')" wire:navigate>
                                {{ __('Import Jurnal Excel') }}
                            </flux:sidebar.item>
                        @endif
                        @if (auth()->user()?->can('periods.view') || auth()->user()?->can('periods.manage'))
                            <flux:sidebar.item icon="calendar" :href="route('accounting.periods.index')" :current="request()->routeIs('accounting.periods.*')" wire:navigate>
                                {{ __('Periode Akuntansi') }}
                            </flux:sidebar.item>
                        @endif
                        @if (auth()->user()?->can('reconciliation.view') || auth()->user()?->can('reports.view'))
                            <flux:sidebar.item icon="arrows-right-left" :href="route('accounting.reconciliation.index')" :current="request()->routeIs('accounting.reconciliation.*')" wire:navigate>
                                {{ __('Rekonsiliasi Bank') }}
                            </flux:sidebar.item>
                        @endif
                        @if (auth()->user()?->can('assets.view') || auth()->user()?->can('reports.view'))
                            <flux:sidebar.item icon="building-office-2" :href="route('accounting.fixed-assets.index')" :current="request()->routeIs('accounting.fixed-assets.*')" wire:navigate>
                                {{ __('Aset Tetap & Depresiasi') }}
                            </flux:sidebar.item>
                        @endif
                        <flux:sidebar.item icon="chart-bar" :href="route('accounting.budgets.index')" :current="request()->routeIs('accounting.budgets.*')" wire:navigate>
                            {{ __('Anggaran & Kontrol Biaya') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:separator class="my-3 border-zinc-200/80 dark:border-zinc-800/80" />
                @endif

                @if (auth()->user()?->can('reports.view') || auth()->user()?->can('reports.general_ledger') || auth()->user()?->can('reports.subsidiary_ledger') || auth()->user()?->can('reports.worksheet') || auth()->user()?->can('reports.trial_balance') || auth()->user()?->can('reports.balance_sheet') || auth()->user()?->can('reports.profit_loss') || auth()->user()?->can('reports.cash_flow') || auth()->user()?->can('reports.opening_balance') || auth()->user()?->can('reports.changes_in_equity'))
                    @php
                        $glRoute = route('accounting.reports.general-ledger');
                        if (! auth()->user()?->can('reports.general_ledger') && ! auth()->user()?->can('reports.view') && auth()->user()?->can('reports.subsidiary_ledger')) {
                            $glRoute = route('accounting.reports.subsidiary-ledger');
                        }

                        $neracaRoute = route('accounting.reports.worksheet');
                        if (! auth()->user()?->can('reports.worksheet') && ! auth()->user()?->can('reports.view')) {
                            if (auth()->user()?->can('reports.trial_balance')) {
                                $neracaRoute = route('accounting.reports.trial-balance');
                            } elseif (auth()->user()?->can('reports.balance_sheet')) {
                                $neracaRoute = route('accounting.reports.balance-sheet');
                            }
                        }
                    @endphp
                    <flux:sidebar.group :heading="__('Laporan Keuangan')" class="grid">
                        @if (auth()->user()?->can('reports.general_ledger') || auth()->user()?->can('reports.subsidiary_ledger') || auth()->user()?->can('reports.view'))
                            <flux:sidebar.item icon="book-open" :href="$glRoute" :current="request()->routeIs('accounting.reports.general-ledger') || request()->routeIs('accounting.reports.subsidiary-ledger')" wire:navigate>
                                {{ __('Buku Besar') }}
                            </flux:sidebar.item>
                        @endif
                        @if (auth()->user()?->can('reports.worksheet') || auth()->user()?->can('reports.trial_balance') || auth()->user()?->can('reports.balance_sheet') || auth()->user()?->can('reports.view'))
                            <flux:sidebar.item icon="scale" :href="$neracaRoute" :current="request()->routeIs('accounting.reports.worksheet') || request()->routeIs('accounting.reports.trial-balance') || request()->routeIs('accounting.reports.balance-sheet')" wire:navigate>
                                {{ __('Neraca') }}
                            </flux:sidebar.item>
                        @endif
                        @if (auth()->user()?->can('reports.profit_loss') || auth()->user()?->can('reports.view'))
                            <flux:sidebar.item icon="arrow-trending-up" :href="route('accounting.reports.profit-loss')" :current="request()->routeIs('accounting.reports.profit-loss')" wire:navigate>
                                {{ __('Laba Rugi') }}
                            </flux:sidebar.item>
                        @endif
                        @if (auth()->user()?->can('reports.cash_flow') || auth()->user()?->can('reports.view'))
                            <flux:sidebar.item icon="currency-dollar" :href="route('accounting.reports.cash-flow')" :current="request()->routeIs('accounting.reports.cash-flow')" wire:navigate>
                                {{ __('Arus Kas') }}
                            </flux:sidebar.item>
                        @endif
                        @if (auth()->user()?->can('reports.opening_balance') || auth()->user()?->can('reports.view'))
                            <flux:sidebar.item icon="calculator" :href="route('accounting.reports.opening-balance')" :current="request()->routeIs('accounting.reports.opening-balance')" wire:navigate>
                                {{ __('Saldo Awal') }}
                            </flux:sidebar.item>
                        @endif
                        @if (auth()->user()?->can('reports.changes_in_equity') || auth()->user()?->can('reports.view'))
                            <flux:sidebar.item icon="document-chart-bar" :href="route('accounting.reports.changes-in-equity')" :current="request()->routeIs('accounting.reports.changes-in-equity')" wire:navigate>
                                {{ __('Perubahan Ekuitas') }}
                            </flux:sidebar.item>
                        @endif
                        @if (auth()->user()?->can('reports.view'))
                            <flux:sidebar.item icon="clock" :href="route('accounting.reports.aging')" :current="request()->routeIs('accounting.reports.aging')" wire:navigate>
                                {{ __('Aging Hutang / Piutang') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="chart-pie" :href="route('accounting.budgets.variance-report')" :current="request()->routeIs('accounting.budgets.variance-report')" wire:navigate>
                                {{ __('Laporan Varian Anggaran') }}
                            </flux:sidebar.item>
                        @endif
                    </flux:sidebar.group>

                    <flux:separator class="my-3 border-zinc-200/80 dark:border-zinc-800/80" />
                @endif

                @if (auth()->user()?->can('accounts.view') || auth()->user()?->can('settings.view') || auth()->user()?->can('settings.manage') || auth()->user()?->can('settings.journal_types') || auth()->user()?->can('settings.units') || auth()->user()?->can('admin.users') || auth()->user()?->can('admin.roles') || auth()->user()?->can('admin.audit_logs'))
                    @php
                        $masterRoute = route('accounting.accounts.index');
                        if (! auth()->user()?->can('accounts.view')) {
                            if (auth()->user()?->can('settings.journal_types')) {
                                $masterRoute = route('accounting.journal-types.index');
                            } elseif (auth()->user()?->can('settings.units')) {
                                $masterRoute = route('accounting.units.index');
                            }
                        }

                        $adminRoute = route('admin.users.index');
                        if (! auth()->user()?->can('admin.users')) {
                            if (auth()->user()?->can('admin.roles') || auth()->user()?->can('settings.manage_roles')) {
                                $adminRoute = route('admin.roles.index');
                            } elseif (auth()->user()?->can('admin.audit_logs')) {
                                $adminRoute = route('admin.audit-logs.index');
                            }
                        }
                    @endphp
                    <flux:sidebar.group :heading="__('Pengaturan')" class="grid">
                        @if (auth()->user()?->can('dashboard.settings') || auth()->user()?->can('settings.manage') || auth()->user()?->hasRole('Super Admin'))
                            <flux:sidebar.item icon="adjustments-horizontal" :href="route('dashboard.settings.index')" :current="request()->routeIs('dashboard.settings.*')" wire:navigate>
                                {{ __('Pengaturan Dashboard') }}
                            </flux:sidebar.item>
                        @endif
                        @if (auth()->user()?->can('accounts.view') || auth()->user()?->can('settings.view') || auth()->user()?->can('settings.manage') || auth()->user()?->can('settings.journal_types') || auth()->user()?->can('settings.units'))
                            <flux:sidebar.item icon="cog-6-tooth" :href="$masterRoute" :current="request()->routeIs('accounting.accounts.*') || request()->routeIs('accounting.journal-types.*') || request()->routeIs('accounting.units.*') || request()->routeIs('accounting.settings.company.*')" wire:navigate>
                                {{ __('Master Akuntansi') }}
                            </flux:sidebar.item>
                        @endif
                        @if (auth()->user()?->can('admin.users') || auth()->user()?->can('admin.roles') || auth()->user()?->can('admin.audit_logs') || auth()->user()?->can('settings.manage_roles'))
                            <flux:sidebar.item icon="user-group" :href="$adminRoute" :current="request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.audit-logs.*')" wire:navigate>
                                {{ __('Pengguna & Hak Akses') }}
                            </flux:sidebar.item>
                        @endif
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()?->name ?? 'Dev Admin'" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()?->initials() ?? 'DA'"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()?->name ?? 'Dev Admin'"
                                    :initials="auth()->user()?->initials() ?? 'DA'"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()?->name ?? 'Dev Admin' }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()?->email ?? 'admin@artaledger.com' }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            (function() {
                let isBypassingConfirm = false;

                // Override native window.confirm to prevent standard browser dialog popups
                window.confirm = function(message) {
                    if (isBypassingConfirm) {
                        return true;
                    }
                    return false;
                };

                document.addEventListener('DOMContentLoaded', () => {
                    document.addEventListener('click', function(e) {
                        if (isBypassingConfirm) return;

                        let target = e.target.closest('[wire\\:confirm]');
                        if (!target) return;

                        let message = target.getAttribute('wire:confirm');
                        if (!message) return;

                        e.stopImmediatePropagation();
                        e.preventDefault();

                        let lowerMsg = message.toLowerCase();
                        let isDanger = lowerMsg.includes('hapus') || lowerMsg.includes('delete') || lowerMsg.includes('balikkan') || lowerMsg.includes('reverse');

                        Swal.fire({
                            title: 'Konfirmasi Tindakan',
                            text: message,
                            icon: isDanger ? 'warning' : 'question',
                            showCancelButton: true,
                            confirmButtonText: isDanger ? 'Ya, Eksekusi' : 'Ya, Lanjutkan',
                            cancelButtonText: 'Batal',
                            customClass: {
                                popup: 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl p-6 text-slate-800 dark:text-slate-100 font-sans',
                                title: 'text-slate-900 dark:text-slate-100 font-bold text-lg tracking-tight',
                                htmlContainer: 'text-slate-600 dark:text-slate-300 text-sm mt-2 font-medium',
                                confirmButton: isDanger 
                                    ? 'px-5 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-xl shadow-lg shadow-rose-500/20 transition-all text-xs cursor-pointer ml-3'
                                    : 'px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/20 transition-all text-xs cursor-pointer ml-3',
                                cancelButton: 'px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl border border-slate-200 dark:border-slate-700 transition-all text-xs cursor-pointer'
                            },
                            buttonsStyling: false,
                            background: document.documentElement.classList.contains('dark') ? '#101014' : '#ffffff',
                            color: document.documentElement.classList.contains('dark') ? '#f4f4f5' : '#101014',
                            backdrop: `rgba(9, 9, 11, 0.75)`
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // 1. Attempt direct Livewire action call if wire:click is present
                                let wireClick = target.getAttribute('wire:click');
                                let componentEl = target.closest('[wire\\:id]');
                                if (componentEl && window.Livewire && wireClick) {
                                    let component = window.Livewire.find(componentEl.getAttribute('wire:id'));
                                    if (component) {
                                        let match = wireClick.match(/^([a-zA-Z0-9_]+)(?:\((.*)\))?$/);
                                        if (match) {
                                            let method = match[1];
                                            let rawArgs = match[2];
                                            let args = [];
                                            if (rawArgs !== undefined && rawArgs.trim() !== '') {
                                                args = rawArgs.split(',').map(arg => {
                                                    arg = arg.trim().replace(/^['"]|['"]$/g, '');
                                                    return isNaN(arg) || arg === '' ? arg : Number(arg);
                                                });
                                            }
                                            component.call(method, ...args);
                                            return;
                                        }
                                    }
                                }

                                // 2. Fallback to synthetic click event with temporary bypass
                                isBypassingConfirm = true;
                                target.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));
                                setTimeout(() => {
                                    isBypassingConfirm = false;
                                }, 300);
                            }
                        });
                    }, true);
                });
            })();
        </script>
    </body>
</html>
