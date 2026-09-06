<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $asset->asset_code }} — {{ $asset->name }} | ArtaLedger</title>
    <meta name="description" content="Informasi aset tetap: {{ $asset->name }}, kategori {{ $asset->category->name }}.">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            background: #0f172a;
            color: #e2e8f0;
            font-family: 'Inter', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 24px 16px 48px;
            background: radial-gradient(ellipse 80% 60% at 50% -20%, rgba(99,102,241,0.25) 0%, transparent 60%),
                        linear-gradient(180deg, #0f172a 0%, #0f172a 100%);
        }

        /* ---- Brand header ---- */
        .brand {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }
        .brand-icon {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, #6366f1, #818cf8);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
        }
        .brand-icon svg { width: 18px; height: 18px; color: white; }
        .brand-name {
            font-size: 18px; font-weight: 700;
            background: linear-gradient(90deg, #a5b4fc, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .brand-tag {
            font-size: 10px; font-weight: 600; letter-spacing: 0.08em;
            color: #64748b; text-transform: uppercase; margin-top: 1px;
        }

        /* ---- Card ---- */
        .card {
            width: 100%; max-width: 420px;
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 20px;
            backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5),
                        0 0 0 1px rgba(255,255,255,0.04) inset,
                        0 8px 32px -8px rgba(99,102,241,0.2);
            overflow: hidden;
        }

        /* ---- Card Header ---- */
        .card-header {
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(99,102,241,0.15);
            background: linear-gradient(135deg, rgba(99,102,241,0.08) 0%, transparent 100%);
        }
        .asset-code {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            background: rgba(99,102,241,0.15);
            border: 1px solid rgba(99,102,241,0.3);
            border-radius: 6px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px; font-weight: 600;
            color: #a5b4fc;
            letter-spacing: 0.05em;
            margin-bottom: 10px;
        }
        .asset-name {
            font-size: 20px; font-weight: 800;
            color: #f1f5f9; line-height: 1.25;
            margin-bottom: 6px;
        }
        .asset-badges {
            display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
        }
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 100px;
            font-size: 11px; font-weight: 600; letter-spacing: 0.02em;
        }
        .badge-category {
            background: rgba(16,185,129,0.12);
            border: 1px solid rgba(16,185,129,0.25);
            color: #34d399;
        }
        .badge-status-active {
            background: rgba(16,185,129,0.12);
            border: 1px solid rgba(16,185,129,0.3);
            color: #34d399;
        }
        .badge-status-fully_depreciated {
            background: rgba(245,158,11,0.12);
            border: 1px solid rgba(245,158,11,0.3);
            color: #fbbf24;
        }
        .badge-status-disposed {
            background: rgba(100,116,139,0.12);
            border: 1px solid rgba(100,116,139,0.3);
            color: #94a3b8;
        }
        .badge-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: currentColor;
        }
        .badge-dot.pulse { animation: pulse-dot 2s infinite; }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        /* ---- Info Grid ---- */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1px;
            background: rgba(99,102,241,0.08);
        }
        .info-cell {
            padding: 14px 18px;
            background: rgba(15,23,42,0.5);
        }
        .info-label {
            font-size: 10px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.08em;
            color: #475569; margin-bottom: 4px;
        }
        .info-value {
            font-size: 13px; font-weight: 600; color: #cbd5e1;
            line-height: 1.4;
        }
        .info-value.mono {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
        }

        /* ---- Depreciation Progress ---- */
        .depreciation-section {
            padding: 16px 20px;
            border-top: 1px solid rgba(99,102,241,0.1);
        }
        .progress-label {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 8px;
        }
        .progress-title {
            font-size: 11px; font-weight: 600;
            color: #64748b; text-transform: uppercase; letter-spacing: 0.06em;
        }
        .progress-pct {
            font-size: 12px; font-weight: 700;
            color: #fbbf24; font-family: 'JetBrains Mono', monospace;
        }
        .progress-bar {
            height: 6px; border-radius: 100px;
            background: rgba(255,255,255,0.06);
            overflow: hidden;
        }
        .progress-fill {
            height: 100%; border-radius: 100px;
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .progress-meta {
            display: flex; justify-content: space-between;
            margin-top: 6px;
            font-size: 10px; color: #475569;
        }

        /* ---- Footer ---- */
        .card-footer {
            padding: 14px 20px;
            border-top: 1px solid rgba(99,102,241,0.1);
            background: rgba(99,102,241,0.04);
            display: flex; align-items: center; justify-content: space-between;
        }
        .footer-brand {
            font-size: 11px; color: #475569;
        }
        .footer-brand span { color: #6366f1; font-weight: 600; }
        .footer-scan-time {
            font-size: 10px; color: #334155;
            font-family: 'JetBrains Mono', monospace;
        }

        /* ---- Not Found ---- */
        .not-found {
            text-align: center; padding: 48px 24px;
        }
        .not-found-icon {
            width: 64px; height: 64px;
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
        }

        /* Responsive */
        @media (max-width: 380px) {
            .info-grid { grid-template-columns: 1fr; }
            .asset-name { font-size: 17px; }
        }
    </style>
</head>
<body>
    <div class="page">

        {{-- Brand Header --}}
        <div class="brand">
            <div class="brand-icon">
                <svg fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <div class="brand-name">ArtaLedger</div>
                <div class="brand-tag">Inventaris Aset Tetap</div>
            </div>
        </div>

        {{-- Asset Card --}}
        <div class="card">

            {{-- Header --}}
            <div class="card-header">
                <div class="asset-code">
                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 3.5a.5.5 0 11-1 0 .5.5 0 011 0zM6 20h4"/>
                    </svg>
                    {{ $asset->asset_code }}
                </div>

                <div class="asset-name">{{ $asset->name }}</div>

                <div class="asset-badges">
                    <span class="badge badge-category">
                        {{ $asset->category->name }}
                    </span>
                    @php
                        $statusMap = [
                            'active'            => ['label' => 'Aktif', 'class' => 'badge-status-active', 'pulse' => true],
                            'fully_depreciated' => ['label' => 'Habis Disusutkan', 'class' => 'badge-status-fully_depreciated', 'pulse' => false],
                            'disposed'          => ['label' => 'Dilepas', 'class' => 'badge-status-disposed', 'pulse' => false],
                        ];
                        $statusInfo = $statusMap[$asset->status] ?? ['label' => $asset->status, 'class' => 'badge-status-disposed', 'pulse' => false];
                    @endphp
                    <span class="badge {{ $statusInfo['class'] }}">
                        <span class="badge-dot {{ $statusInfo['pulse'] ? 'pulse' : '' }}"></span>
                        {{ $statusInfo['label'] }}
                    </span>
                </div>
            </div>

            {{-- Info Grid --}}
            <div class="info-grid">
                <div class="info-cell">
                    <div class="info-label">Unit / Departemen</div>
                    <div class="info-value">{{ $asset->unit->name }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Lokasi</div>
                    <div class="info-value">{{ $asset->location ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Penanggung Jawab</div>
                    <div class="info-value">{{ $asset->person_in_charge ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">No. Seri</div>
                    <div class="info-value mono">{{ $asset->serial_number ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Tanggal Perolehan</div>
                    <div class="info-value">{{ $asset->acquisition_date->isoFormat('D MMM YYYY') }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Masa Manfaat</div>
                    <div class="info-value">
                        {{ $asset->useful_life_months }} bulan
                        <span style="color:#475569;font-size:11px;">({{ round($asset->useful_life_months / 12, 1) }} thn)</span>
                    </div>
                </div>
            </div>

            {{-- Depreciation Progress Bar --}}
            @php
                $totalMonths = $asset->useful_life_months;
                $depreciatedMonths = $totalMonths > 0
                    ? min($totalMonths, (int) round($asset->accumulated_depreciation / max(1, $asset->monthly_depreciation_amount)))
                    : 0;
                $progressPct = $totalMonths > 0
                    ? min(100, round($depreciatedMonths / $totalMonths * 100))
                    : 0;
            @endphp
            @if($totalMonths > 0)
                <div class="depreciation-section">
                    <div class="progress-label">
                        <span class="progress-title">Progress Depresiasi</span>
                        <span class="progress-pct">{{ $progressPct }}%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $progressPct }}%"></div>
                    </div>
                    <div class="progress-meta">
                        <span>Mulai: {{ $asset->start_depreciation_date ? $asset->start_depreciation_date->isoFormat('MMM YYYY') : '—' }}</span>
                        <span>{{ $depreciatedMonths }} / {{ $totalMonths }} bulan</span>
                    </div>
                </div>
            @endif

            {{-- Footer --}}
            <div class="card-footer">
                <div class="footer-brand">
                    Dikelola oleh <span>ArtaLedger</span>
                </div>
                <div class="footer-scan-time" id="scan-time"></div>
            </div>
        </div>

        {{-- Catatan --}}
        <p style="margin-top:20px;font-size:11px;color:#334155;text-align:center;line-height:1.6;">
            Halaman ini bersifat publik dan hanya menampilkan<br>informasi dasar aset. Data finansial tidak tersedia di sini.
        </p>

    </div>

    <script>
        // Tampilkan waktu scan
        const el = document.getElementById('scan-time');
        if (el) {
            const now = new Date();
            el.textContent = now.toLocaleString('id-ID', {
                day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit',
            });
        }
    </script>
</body>
</html>
