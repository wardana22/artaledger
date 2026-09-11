<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Varian Anggaran - {{ $budget->name }}</title>
    <style>
        @page {
            margin: 24px 30px;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 12px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0;
            color: #111827;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 12px;
            margin: 4px 0 0 0;
            color: #4f46e5;
        }
        .header p {
            font-size: 9px;
            margin: 2px 0 0 0;
            color: #6b7280;
        }
        .summary-box {
            width: 100%;
            margin-bottom: 15px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 6px 10px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            font-size: 9px;
        }
        .summary-table strong {
            font-size: 11px;
            color: #111827;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #d1d5db;
            font-size: 9px;
            text-transform: uppercase;
        }
        table.data-table td {
            padding: 5px 8px;
            border: 1px solid #e5e7eb;
            font-size: 9px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .status-safe {
            color: #065f46;
            font-weight: bold;
        }
        .status-warning {
            color: #92400e;
            font-weight: bold;
        }
        .status-exceeded {
            color: #991b1b;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 8px;
            color: #9ca3af;
            text-align: right;
            border-top: 1px solid #e5e7eb;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $company->name ?? 'ARTALEDGER' }}</h1>
        <h2>LAPORAN ANALISIS VARIAN ANGGARAN (BUDGET VS ACTUAL)</h2>
        <p>
            Tahun Fiskal: {{ $reportData['year'] }} 
            @if ($reportData['month'])
                | Bulan: {{ $monthNames[$reportData['month']] ?? $reportData['month'] }}
            @else
                | Periode: Setahun Penuh
            @endif
            | Dicetak: {{ now()->format('d/m/Y H:i') }}
        </p>
    </div>

    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td width="25%">
                    Total Plafon Anggaran<br>
                    <strong>Rp {{ number_format($reportData['summary']['total_budget'], 0, ',', '.') }}</strong>
                </td>
                <td width="25%">
                    Total Realisasi Aktual<br>
                    <strong style="color: #059669;">Rp {{ number_format($reportData['summary']['total_actual'], 0, ',', '.') }}</strong>
                </td>
                <td width="25%">
                    Sisa Pagu / Varian<br>
                    <strong>Rp {{ number_format($reportData['summary']['total_variance'], 0, ',', '.') }}</strong>
                </td>
                <td width="25%">
                    Tingkat Serapan Agregat<br>
                    <strong>{{ number_format($reportData['summary']['aggregate_absorption'], 1, ',', '.') }}%</strong>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="10%">Kode</th>
                <th width="28%">Nama Akun Biaya</th>
                <th width="14%">Unit</th>
                <th width="14%" class="text-right">Anggaran (Rp)</th>
                <th width="14%" class="text-right">Aktual (Rp)</th>
                <th width="12%" class="text-right">Varian (Rp)</th>
                <th width="8%" class="text-center">Serapan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reportData['items'] as $row)
                <tr>
                    <td>{{ $row['account_code'] }}</td>
                    <td>{{ $row['account_name'] }}</td>
                    <td>{{ $row['unit_name'] }}</td>
                    <td class="text-right">{{ number_format($row['budget_amount'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['actual_amount'], 0, ',', '.') }}</td>
                    <td class="text-right" style="{{ $row['variance_amount'] < 0 ? 'color: #dc2626; font-weight: bold;' : '' }}">
                        {{ number_format($row['variance_amount'], 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        @php
                            $badgeLabel = match($row['status']) {
                                'terkendali' => 'Terkendali',
                                'mendekati' => 'Mendekati',
                                'melampaui' => 'Melampaui',
                                default => ucfirst($row['status'])
                            };
                        @endphp
                        <span class="status-{{ $row['status'] }}">{{ $row['absorption_rate'] }}% ({{ $badgeLabel }})</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data akun pada anggaran ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak secara otomatis oleh Sistem Finansial ArtaLedger | Halaman 1
    </div>
</body>
</html>
