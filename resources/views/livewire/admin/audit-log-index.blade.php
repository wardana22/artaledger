<div class="p-4 sm:p-5 space-y-3.5">
    <!-- TOP NAV TABS (USER ACCESS NAV) -->
    <x-user-access-nav active="audit-logs" />

    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                Audit Log & Jejak Aktivitas Pengguna (Audit Trail)
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Pemantauan real-time aktivitas sistem: Siapa yang Login/Logout, membuat/memposting Jurnal, serta penutupan periode.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold text-slate-700 dark:text-slate-300">
                🔒 System Security Guard Active
            </span>
        </div>
    </div>

    <!-- FILTER TOOLBAR -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-3.5 rounded-xl shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
            <!-- Search Input -->
            <div class="md:col-span-2">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Cari Deskripsi / User / IP</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input 
                        wire:model.live.debounce.300ms="search" 
                        type="text" 
                        placeholder="Cari aktivitas, kata kunci..." 
                        class="w-full pl-9 pr-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 transition-all"
                    />
                </div>
            </div>

            <!-- Filter Pengguna -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Pengguna (User)</label>
                <select wire:model.live="userFilter" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm font-semibold dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                    <option value="all">🌐 Semua Pengguna</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Kategori Event -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Kategori Aktivitas</label>
                <select wire:model.live="eventFilter" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm font-semibold dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                    <option value="all">⚡ Semua Event</option>
                    <option value="auth">🔑 Otentikasi (Login/Logout)</option>
                    <option value="journal">📝 Jurnal Transaksi (Draft/Post)</option>
                    <option value="period">📅 Periode Akuntansi</option>
                </select>
            </div>

            <!-- Range Tanggal -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Tanggal</label>
                <input wire:model.live="startDate" type="date" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs md:text-sm dark:text-slate-100" />
            </div>
        </div>
    </div>

    <!-- AUDIT LOG TABLE CARD -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-2.5 w-10 text-center">#</th>
                        <th class="px-4 py-2.5 w-36">Waktu & IP Address</th>
                        <th class="px-4 py-2.5 w-48">Pengguna (User)</th>
                        <th class="px-4 py-2.5 w-36">Jenis Event</th>
                        <th class="px-4 py-2.5">Deskripsi Aktivitas</th>
                        <th class="px-4 py-2.5 text-center w-20">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($logs as $index => $log)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-4 py-2.5 text-center text-slate-400 font-mono">
                                {{ $logs->firstItem() + $index }}
                            </td>

                            <td class="px-4 py-2.5 font-mono whitespace-nowrap">
                                <span class="block font-bold text-slate-700 dark:text-slate-200">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </span>
                                <span class="text-[10px] text-slate-400">
                                    IP: {{ $log->ip_address ?: '127.0.0.1' }}
                                </span>
                            </td>

                            <td class="px-4 py-2.5 font-bold text-slate-800 dark:text-slate-100">
                                @if ($log->user)
                                    <span>{{ $log->user->name }}</span>
                                    <span class="block text-[10px] font-normal text-slate-400 font-mono">{{ $log->user->email }}</span>
                                @else
                                    <span class="text-slate-400 italic">Sistem / Tamu</span>
                                @endif
                            </td>

                            <td class="px-4 py-2.5 whitespace-nowrap">
                                @if (str_starts_with($log->event_type, 'auth.'))
                                    <span class="px-2 py-0.5 text-[10px] font-extrabold rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                        🔑 {{ strtoupper($log->event_type) }}
                                    </span>
                                @elseif (str_starts_with($log->event_type, 'journal.'))
                                    <span class="px-2 py-0.5 text-[10px] font-extrabold rounded bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                        📝 {{ strtoupper($log->event_type) }}
                                    </span>
                                @elseif (str_starts_with($log->event_type, 'period.'))
                                    <span class="px-2 py-0.5 text-[10px] font-extrabold rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                        🔒 {{ strtoupper($log->event_type) }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-extrabold rounded bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20">
                                        ⚡ {{ strtoupper($log->event_type) }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-2.5 font-medium text-slate-800 dark:text-slate-200">
                                {{ $log->description }}
                            </td>

                            <td class="px-4 py-2.5 text-center">
                                <button 
                                    wire:click="viewAuditLog({{ $log->id }})"
                                    title="Lihat Detail Audit Log"
                                    class="p-1.5 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-500/30 rounded-lg transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                Belum ada catatan aktivitas Audit Log.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-custom-pagination :paginator="$logs" />

    <!-- MODAL DETAIL AUDIT LOG INSPECTION -->
    @if ($showDetailModal && $selectedAuditLog)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <div class="p-4 bg-slate-50 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            Detail Jejak Audit #{{ $selectedAuditLog->id }}
                            <span class="px-2 py-0.5 text-[10px] font-mono font-bold rounded bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                {{ strtoupper($selectedAuditLog->event_type) }}
                            </span>
                        </h3>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">
                            Waktu: {{ $selectedAuditLog->created_at->format('d F Y H:i:s') }}
                        </p>
                    </div>

                    <button wire:click="closeDetailModal" class="p-1 text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="p-5 space-y-4 overflow-y-auto flex-1 text-xs">
                    <!-- User & Client Info -->
                    <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200/80 dark:border-slate-700/80">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Pengguna (User):</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100">
                                {{ $selectedAuditLog->user?->name ?? 'Sistem / Tamu' }}
                                @if ($selectedAuditLog->user?->email)
                                    ({{ $selectedAuditLog->user->email }})
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Alamat IP & Peranti:</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-100 block">
                                {{ $selectedAuditLog->ip_address ?: '127.0.0.1' }}
                            </span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-slate-400 block text-[11px]">Browser User Agent:</span>
                            <span class="font-mono text-slate-600 dark:text-slate-300 break-all text-[11px]">
                                {{ $selectedAuditLog->user_agent ?: 'Standard Client' }}
                            </span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="p-3 bg-indigo-50/50 dark:bg-slate-800/60 rounded-xl border border-indigo-100 dark:border-slate-700/80">
                        <span class="text-[11px] font-bold text-indigo-700 dark:text-indigo-300 block mb-1 uppercase">Deskripsi Aktivitas</span>
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-100">
                            {{ $selectedAuditLog->description }}
                        </p>
                    </div>

                    <!-- Old vs New Values JSON Diff -->
                    @if ($selectedAuditLog->old_values || $selectedAuditLog->new_values)
                        <div class="space-y-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block">Perubahan Data Transaksi (State Diff)</span>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 font-mono text-[11px]">
                                @if ($selectedAuditLog->old_values)
                                    <div class="p-3 bg-rose-50/70 dark:bg-slate-800/80 border border-rose-200 dark:border-rose-900/50 rounded-xl">
                                        <span class="font-bold text-rose-700 dark:text-rose-400 block mb-1">State Sebelum (Old Values):</span>
                                        <pre class="overflow-x-auto text-slate-700 dark:text-slate-300 whitespace-pre-wrap break-all">{{ json_encode($selectedAuditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                @endif

                                @if ($selectedAuditLog->new_values)
                                    <div class="p-3 bg-emerald-50/70 dark:bg-slate-800/80 border border-emerald-200 dark:border-emerald-900/50 rounded-xl">
                                        <span class="font-bold text-emerald-700 dark:text-emerald-400 block mb-1">State Sesudah (New Values):</span>
                                        <pre class="overflow-x-auto text-slate-700 dark:text-slate-300 whitespace-pre-wrap break-all">{{ json_encode($selectedAuditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div class="p-3 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button 
                        type="button" 
                        wire:click="closeDetailModal" 
                        class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-md shadow-indigo-500/20">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
