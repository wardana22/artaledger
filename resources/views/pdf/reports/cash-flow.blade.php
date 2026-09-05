@extends('pdf.reports.layout', ['title' => 'Laporan Arus Kas'])

@section('report_title', 'LAPORAN ARUS KAS')
@section('report_subtitle')
    Periode: {{ $startDate }} s/d {{ $endDate }}
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 70%;" class="text-left">DESKRIPSI ARUS KAS</th>
                <th style="width: 30%;" class="text-right">JUMLAH (IDR)</th>
            </tr>
        </thead>
        <tbody>
            <!-- 1. SALDO AWAL -->
            <tr class="header-row">
                <td class="text-left font-bold" style="padding-left: 10px;">1. SALDO KAS & BANK AWAL PERIODE</td>
                <td class="font-mono text-right font-bold" style="color: #1e3a8a;">
                    {{ number_format($openingCash, 2, ',', '.') }}
                </td>
            </tr>

            <!-- 2. ARUS KAS OPERASIONAL -->
            <tr class="header-row" style="background-color: #f8fafc;">
                <td colspan="2" class="text-left font-bold" style="padding-left: 10px; text-transform: uppercase;">
                    2. ARUS KAS DARI AKTIVITAS OPERASIONAL
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 24px;">
                    Penerimaan Kas & Bank (Mutasi Masuk / Penerimaan Operasional)
                </td>
                <td class="font-mono text-right">
                    {{ number_format($operatingIn, 2, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 24px;">
                    Pengeluaran Kas & Bank (Mutasi Keluar / Pembayaran Beban & Operasional)
                </td>
                <td class="font-mono text-right">
                    ({{ number_format($operatingOut, 2, ',', '.') }})
                </td>
            </tr>
            <tr class="total-row">
                <td class="text-left font-bold" style="padding-left: 24px;">
                    KENAIKAN / (PENURUNAN) KAS BERSIH DARI AKTIVITAS OPERASIONAL
                </td>
                <td class="font-mono text-right font-bold">
                    {{ number_format($netOperatingCash, 2, ',', '.') }}
                </td>
            </tr>

            <!-- 3. SALDO AKHIR -->
            <tr style="height: 12px; background-color: #ffffff;"><td colspan="2" style="border: none;"></td></tr>
            <tr class="grand-total-row" style="font-size: 10.5px;">
                <td class="text-left font-bold" style="padding-left: 10px;">
                    SALDO KAS & BANK AKHIR PERIODE (1 + 2)
                </td>
                <td class="font-mono text-right font-bold">
                    {{ number_format($endingCash, 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>
@endsection
