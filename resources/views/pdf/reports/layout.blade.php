<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title ?? 'Laporan Keuangan' }} - {{ $company->name ?? 'ArtaLedger' }}</title>
    <style>
        @page {
            margin: 30px 35px 40px 35px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.4;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .company-meta {
            font-size: 9px;
            color: #475569;
            margin-top: 2px;
        }
        .report-title-box {
            text-align: right;
        }
        .report-title {
            font-size: 14px;
            font-weight: 800;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-subtitle {
            font-size: 9.5px;
            font-weight: 600;
            color: #64748b;
            margin-top: 3px;
        }
        .meta-bar {
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 12px;
            font-size: 9px;
        }
        .meta-bar table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-bar td {
            padding: 2px 4px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 8px;
            border: 1px solid #0f172a;
        }
        .data-table td {
            padding: 4.5px 8px;
            font-size: 9px;
            border-bottom: 1px solid #e2e8f0;
            border-left: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
        }
        .data-table tr.header-row {
            background-color: #f1f5f9;
            font-weight: bold;
        }
        .data-table tr.total-row {
            background-color: #e2e8f0;
            font-weight: 800;
            border-top: 1.5px solid #0f172a;
            border-bottom: 2px solid #0f172a;
        }
        .data-table tr.grand-total-row {
            background-color: #dbeafe;
            font-weight: 900;
            border-top: 2px solid #1e3a8a;
            border-bottom: 3px double #1e3a8a;
            color: #1e3a8a;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .font-bold { font-weight: bold; }
        
        .signature-section {
            margin-top: 25px;
            width: 100%;
            page-break-inside: avoid;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }
        .sig-title {
            font-size: 9px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 45px;
            text-transform: uppercase;
        }
        .sig-name {
            font-size: 9.5px;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1px solid #0f172a;
            padding-bottom: 2px;
            display: inline-block;
            min-width: 140px;
        }
        .sig-role {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 2px;
        }

        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 20px;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
        .footer-table {
            width: 100%;
        }
        .pagenum:before {
            content: counter(page);
        }
    </style>
    @yield('custom_styles')
</head>
<body>
    <!-- FOOTER -->
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td class="text-left">
                    ArtaLedger ERP System | Dicetak pada: {{ $printedAt }} oleh: {{ $printedBy }}
                </td>
                <td class="text-right">
                    Halaman <span class="pagenum"></span>
                </td>
            </tr>
        </table>
    </div>

    <!-- LETTERHEAD HEADER -->
    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                <div class="company-name">{{ $company->name ?? 'PT ARTA LEDGER INDONESIA' }}</div>
                <div class="company-meta">
                    {{ $company->address ?? 'Komp. Perkantoran Graha Arta, Pekanbaru - Riau' }}<br>
                    Telp: {{ $company->phone ?? '(0761) 555-888' }} | Email: {{ $company->email ?? 'finance@artaledger.com' }}
                    @if(!empty($company->tax_number)) | NPWP: {{ $company->tax_number }} @endif
                </div>
            </td>
            <td class="report-title-box" style="width: 45%;">
                <div class="report-title">@yield('report_title', 'LAPORAN KEUANGAN')</div>
                <div class="report-subtitle">@yield('report_subtitle')</div>
            </td>
        </tr>
    </table>

    <!-- META BAR -->
    <div class="meta-bar">
        <table>
            <tr>
                <td style="width: 50%;">
                    <strong>Unit Usaha:</strong> {{ $unitName }}
                </td>
                <td style="width: 50%; text-align: right;">
                    <strong>Mata Uang:</strong> IDR (Rupiah Indonesia)
                </td>
            </tr>
        </table>
    </div>

    <!-- REPORT CONTENT -->
    @yield('content')

    <!-- SIGNATURE BLOCK -->
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <div class="sig-title">Disusun Oleh,</div>
                    <div class="sig-name">{{ $printedBy ?? 'Staff Akuntansi' }}</div>
                    <div class="sig-role">Bagian Keuangan & Akuntansi</div>
                </td>
                <td>
                    <div class="sig-title">Diperiksa Oleh,</div>
                    <div class="sig-name">Manager Akuntansi</div>
                    <div class="sig-role">Accounting & Tax Lead</div>
                </td>
                <td>
                    <div class="sig-title">Disetujui Oleh,</div>
                    <div class="sig-name">Direktur Keuangan</div>
                    <div class="sig-role">Chief Financial Officer (CFO)</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
