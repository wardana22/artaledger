<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav class="space-y-2">
                <flux:sidebar.group :heading="__('Manajemen Akuntansi')" class="grid">
                    <flux:sidebar.item icon="calendar" :href="route('accounting.periods.index')" :current="request()->routeIs('accounting.periods.*')" wire:navigate>
                        {{ __('Periode Akuntansi') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="arrow-down-tray" :href="route('accounting.import.index')" :current="request()->routeIs('accounting.import.*')" wire:navigate>
                        {{ __('Import Jurnal Excel') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-text" :href="route('accounting.journals.index')" :current="request()->routeIs('accounting.journals.*') || request()->routeIs('accounting.adjustments.*')" wire:navigate>
                        {{ __('Jurnal Transaksi') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:separator class="my-3 border-zinc-200/80 dark:border-zinc-800/80" />

                <flux:sidebar.group :heading="__('Laporan Keuangan')" class="grid">
                    <flux:sidebar.item icon="book-open" :href="route('accounting.reports.general-ledger')" :current="request()->routeIs('accounting.reports.general-ledger') || request()->routeIs('accounting.reports.subsidiary-ledger')" wire:navigate>
                        {{ __('Buku Besar') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="scale" :href="route('accounting.reports.worksheet')" :current="request()->routeIs('accounting.reports.worksheet') || request()->routeIs('accounting.reports.trial-balance') || request()->routeIs('accounting.reports.balance-sheet')" wire:navigate>
                        {{ __('Neraca') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="arrow-trending-up" :href="route('accounting.reports.profit-loss')" :current="request()->routeIs('accounting.reports.profit-loss')" wire:navigate>
                        {{ __('Laba Rugi') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="currency-dollar" :href="route('accounting.reports.cash-flow')" :current="request()->routeIs('accounting.reports.cash-flow')" wire:navigate>
                        {{ __('Arus Kas') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="calculator" :href="route('accounting.reports.opening-balance')" :current="request()->routeIs('accounting.reports.opening-balance')" wire:navigate>
                        {{ __('Saldo Awal') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-chart-bar" :href="route('accounting.reports.changes-in-equity')" :current="request()->routeIs('accounting.reports.changes-in-equity')" wire:navigate>
                        {{ __('Perubahan Ekuitas') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:separator class="my-3 border-zinc-200/80 dark:border-zinc-800/80" />

                <flux:sidebar.group :heading="__('Pengaturan')" class="grid">
                    <flux:sidebar.item icon="cog-6-tooth" :href="route('accounting.accounts.index')" :current="request()->routeIs('accounting.accounts.*')" wire:navigate>
                        {{ __('Master COA') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="tag" :href="route('accounting.journal-types.index')" :current="request()->routeIs('accounting.journal-types.*')" wire:navigate>
                        {{ __('Jenis Jurnal') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="building-office-2" :href="route('accounting.units.index')" :current="request()->routeIs('accounting.units.*')" wire:navigate>
                        {{ __('Unit Perusahaan') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="shield-check" :href="route('admin.roles.index')" :current="request()->routeIs('admin.roles.*')" wire:navigate>
                        {{ __('Peran & Hak Akses') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
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
                            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                            color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a',
                            backdrop: `rgba(15, 23, 42, 0.75)`
                        }).then((result) => {
                            if (result.isConfirmed) {
                                isBypassingConfirm = true;
                                target.click();
                                isBypassingConfirm = false;
                            }
                        });
                    }, true);
                });
            })();
        </script>
    </body>
</html>
