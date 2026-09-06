@php
    $company = \App\Models\Company::first();
    $appName = $company?->app_name ?? config('app.name', 'E-counting');
    $companyName = $company?->name ?? 'PT ArtaLedger Enterprise';
    $logoUrl = $company?->logo_url;
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $asset->asset_code }} — {{ $asset->name }} | {{ $appName }} - {{ $companyName }}</title>
    <meta name="description" content="Informasi aset: {{ $asset->name }}, kategori {{ $asset->category->name }}.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            background: #f1f5f9;
            color: #0f172a;
            font-family: 'Inter', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 0 48px;
        }

        /* ---- Brand Bar ---- */
        .brand-bar {
            width: 100%;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            margin-bottom: 20px;
        }
        .brand-link {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
        }
        .brand-link:hover {
            opacity: 0.85;
            transform: translateY(-0.5px);
        }
        .brand-logo-img {
            max-height: 38px;
            width: auto;
            max-width: 180px;
            object-fit: contain;
            display: block;
            border-radius: 6px;
        }
        .brand-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #6366f1, #818cf8);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(99, 102, 241, 0.25);
        }
        .brand-icon svg { width: 20px; height: 20px; }
        .brand-text-col {
            display: flex;
            flex-direction: column;
            line-height: 1.25;
            text-align: left;
        }
        .brand-name {
            font-size: 15px;
            font-weight: 800;
            color: #1e1b4b;
            letter-spacing: -0.01em;
        }
        .brand-sub {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
        }
        .brand-tag {
            font-size: 10px; font-weight: 700; letter-spacing: 0.06em;
            color: #6366f1; background: #eef2ff;
            border: 1px solid #e0e7ff;
            padding: 4px 10px;
            border-radius: 9999px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        /* ---- Card ---- */
        .card {
            width: 100%; max-width: 440px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
            overflow: hidden;
        }

        /* ---- Asset Photo ---- */
        .asset-photo {
            width: 100%;
            height: 220px;
            object-fit: cover;
            display: block;
            background: #f8fafc;
        }
        .asset-photo-placeholder {
            width: 100%;
            height: 140px;
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #cbd5e1;
        }
        .asset-photo-placeholder svg { width: 36px; height: 36px; }
        .asset-photo-placeholder span { font-size: 12px; }

        /* ---- Card Header ---- */
        .card-header {
            padding: 18px 20px 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .asset-code {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 6px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px; font-weight: 700;
            color: #4f46e5;
            letter-spacing: 0.04em;
            margin-bottom: 8px;
        }
        .asset-name {
            font-size: 19px; font-weight: 800;
            color: #0f172a; line-height: 1.25;
            margin-bottom: 8px;
        }
        .asset-badges {
            display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
        }
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: 100px;
            font-size: 11px; font-weight: 600;
        }
        .badge-category {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
        }
        .badge-status-active {
            background: #ecfdf5;
            border: 1px solid #6ee7b7;
            color: #059669;
        }
        .badge-status-fully_depreciated {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            color: #d97706;
        }
        .badge-status-disposed {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            color: #64748b;
        }
        .badge-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: currentColor; flex-shrink: 0;
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
            background: #f1f5f9;
        }
        .info-cell {
            padding: 13px 18px;
            background: white;
        }
        .info-label {
            font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.08em;
            color: #94a3b8; margin-bottom: 3px;
        }
        .info-value {
            font-size: 13px; font-weight: 600; color: #1e293b;
            line-height: 1.4;
        }
        .info-value.mono {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
        }

        /* ---- Depreciation Progress ---- */
        .depreciation-section {
            padding: 14px 20px;
            border-top: 1px solid #f1f5f9;
            background: #fafafa;
        }
        .progress-label {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 7px;
        }
        .progress-title {
            font-size: 10px; font-weight: 700;
            color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em;
        }
        .progress-pct {
            font-size: 12px; font-weight: 700;
            color: #d97706; font-family: 'JetBrains Mono', monospace;
        }
        .progress-bar {
            height: 6px; border-radius: 100px;
            background: #f1f5f9; overflow: hidden;
        }
        .progress-fill {
            height: 100%; border-radius: 100px;
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
            transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .progress-meta {
            display: flex; justify-content: space-between;
            margin-top: 5px;
            font-size: 10px; color: #94a3b8;
        }

        /* ---- Footer ---- */
        .card-footer {
            padding: 12px 20px;
            border-top: 1px solid #f1f5f9;
            background: white;
            display: flex; align-items: center; justify-content: space-between;
        }
        .footer-brand { font-size: 11px; color: #94a3b8; }
        .footer-brand span { color: #6366f1; font-weight: 700; }
        .footer-scan-time {
            font-size: 10px; color: #cbd5e1;
            font-family: 'JetBrains Mono', monospace;
        }

        .note {
            margin-top: 16px;
            font-size: 11px; color: #94a3b8;
            text-align: center; line-height: 1.6;
            padding: 0 20px;
        }

        @media (max-width: 380px) {
            .info-grid { grid-template-columns: 1fr; }
            .asset-name { font-size: 17px; }
        }
    </style>
</head>
<body>
    <div class="page">

        {{-- Brand Bar --}}
        <div class="brand-bar">
            <a href="{{ route('dashboard') }}" class="brand-link" title="Buka Dashboard {{ $appName }}">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $appName }} - {{ $companyName }}" class="brand-logo-img">
                @else
                    <div class="brand-icon">
                        <svg fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                @endif
                <div class="brand-text-col">
                    <span class="brand-name">{{ $appName }}</span>
                    <span class="brand-sub">{{ $companyName }}</span>
                </div>
            </a>
            <div class="brand-tag">Inventaris Aset Tetap</div>
        </div>

        {{-- Card --}}
        <div class="card">

            {{-- Foto Aset --}}
            @if($asset->photo_path)
                <img src="{{ Storage::url($asset->photo_path) }}"
                     alt="Foto {{ $asset->name }}"
                     class="asset-photo">
            @else
                <div class="asset-photo-placeholder">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Belum ada foto aset</span>
                </div>
            @endif

            {{-- Header --}}
            <div class="card-header">
                <div class="asset-code">
                    <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 3.5a.5.5 0 11-1 0 .5.5 0 011 0zM6 20h4"/>
                    </svg>
                    {{ $asset->asset_code }}
                </div>
                <div class="asset-name">{{ $asset->name }}</div>
                <div class="asset-badges">
                    <span class="badge badge-category">{{ $asset->category->name }}</span>
                    @php
                        $statusMap = [
                            'active'            => ['label' => 'Aktif', 'class' => 'badge-status-active', 'pulse' => true],
                            'fully_depreciated' => ['label' => 'Habis Disusutkan', 'class' => 'badge-status-fully_depreciated', 'pulse' => false],
                            'disposed'          => ['label' => 'Dilepas', 'class' => 'badge-status-disposed', 'pulse' => false],
                        ];
                        $s = $statusMap[$asset->status] ?? ['label' => $asset->status, 'class' => 'badge-status-disposed', 'pulse' => false];
                    @endphp
                    <span class="badge {{ $s['class'] }}">
                        <span class="badge-dot {{ $s['pulse'] ? 'pulse' : '' }}"></span>
                        {{ $s['label'] }}
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
                        {{ $asset->useful_life_months }} bln
                        <span style="color:#94a3b8;font-size:11px;">({{ round($asset->useful_life_months / 12, 1) }} thn)</span>
                    </div>
                </div>
            </div>

            {{-- Progress Depresiasi --}}
            @php
                $totalMonths = $asset->useful_life_months;
                $depMonths   = $totalMonths > 0
                    ? min($totalMonths, (int) round($asset->accumulated_depreciation / max(1, $asset->monthly_depreciation_amount)))
                    : 0;
                $pct = $totalMonths > 0 ? min(100, round($depMonths / $totalMonths * 100)) : 0;
            @endphp
            @if($totalMonths > 0)
                <div class="depreciation-section">
                    <div class="progress-label">
                        <span class="progress-title">Progress Depresiasi</span>
                        <span class="progress-pct">{{ $pct }}%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $pct }}%"></div>
                    </div>
                    <div class="progress-meta">
                        <span>Mulai: {{ $asset->start_depreciation_date?->isoFormat('MMM YYYY') ?? '—' }}</span>
                        <span>{{ $depMonths }} / {{ $totalMonths }} bulan</span>
                    </div>
                </div>
            @endif

            {{-- Footer --}}
            <div class="card-footer">
                <div class="footer-brand">Dikelola oleh <span>{{ $companyName }}</span></div>
                <div class="footer-scan-time" id="scan-time"></div>
            </div>
        </div>

        <p class="note">
            Halaman ini bersifat publik — hanya informasi dasar aset.<br>
            Data finansial tidak tersedia di sini.
        </p>
    </div>

    <script>
        const el = document.getElementById('scan-time');
        if (el) {
            el.textContent = new Date().toLocaleString('id-ID', {
                day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit',
            });
        }
    </script>
</body>
</html>
