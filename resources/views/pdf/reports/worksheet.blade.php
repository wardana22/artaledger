@extends('pdf.reports.layout', ['title' => 'Neraca Lajur (Worksheet)'])

@section('report_title', 'NERACA LAJUR 10 KOLOM (WORKSHEET)')
@section('report_subtitle')
    Periode: {{ $startDate }} s/d {{ $endDate }} | Kertas Kerja Akuntansi Komprehensif
@endsection

@section('custom_styles')
<style>
    @page {
        margin: 20px 25px 30px 25px;
    }
    body {
        font-size: 8px;
    }
    .data-table th {
        font-size: 7.5px;
        padding: 4px 3px;
    }
    .data-table td {
        font-size: 7.5px;
        padding: 3px 4px;
    }
    .th-group {
        text-align: center;
        border-bottom: 1px solid #334155;
    }
</style>
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 7%;" class="text-left">KODE</th>
                <th rowspan="2" style="width: 17%;" class="text-left">NAMA AKUN</th>
                <th colspan="2" class="th-group" style="width: 15%;">NERACA SALDO</th>
                <th colspan="2" class="th-group" style="width: 15%;">PENYESUAIAN</th>
                <th colspan="2" class="th-group" style="width: 15%;">NS DISESUAIKAN</th>
                <th colspan="2" class="th-group" style="width: 15%;">LABA RUGI</th>
                <th colspan="2" class="th-group" style="width: 16%;">NERACA (POSISI KEUANGAN)</th>
            </tr>
            <tr>
                <!-- Neraca Saldo -->
                <th style="width: 7.5%;" class="text-right">DEBET</th>
                <th style="width: 7.5%;" class="text-right">KREDIT</th>
                <!-- Penyesuaian -->
                <th style="width: 7.5%;" class="text-right">DEBET</th>
                <th style="width: 7.5%;" class="text-right">KREDIT</th>
                <!-- NS Disesuaikan -->
                <th style="width: 7.5%;" class="text-right">DEBET</th>
                <th style="width: 7.5%;" class="text-right">KREDIT</th>
                <!-- Laba Rugi -->
                <th style="width: 7.5%;" class="text-right">DEBET</th>
                <th style="width: 7.5%;" class="text-right">KREDIT</th>
                <!-- Neraca -->
                <th style="width: 8%;" class="text-right">DEBET</th>
                <th style="width: 8%;" class="text-right">KREDIT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @php
                    $isHeader = $row['has_children'] || $row['level'] <= 2;
                    $indent = max(0, ($row['level'] - 1) * 8);
                @endphp
                <tr class="{{ $isHeader ? 'header-row' : '' }}">
                    <td class="font-mono text-left" style="padding-left: {{ 4 + $indent }}px;">
                        {{ $row['account']->code }}
                    </td>
                    <td class="text-left" style="padding-left: {{ 4 + $indent }}px; {{ $isHeader ? 'font-weight: bold;' : '' }}">
                        {{ $row['account']->name }}
                    </td>
                    <!-- TB -->
                    <td class="font-mono text-right">{{ $row['tb_debit'] > 0 ? number_format($row['tb_debit'], 2, ',', '.') : '-' }}</td>
                    <td class="font-mono text-right">{{ $row['tb_credit'] > 0 ? number_format($row['tb_credit'], 2, ',', '.') : '-' }}</td>
                    <!-- ADJ -->
                    <td class="font-mono text-right">{{ $row['adj_debit'] > 0 ? number_format($row['adj_debit'], 2, ',', '.') : '-' }}</td>
                    <td class="font-mono text-right">{{ $row['adj_credit'] > 0 ? number_format($row['adj_credit'], 2, ',', '.') : '-' }}</td>
                    <!-- ATB -->
                    <td class="font-mono text-right">{{ $row['atb_debit'] > 0 ? number_format($row['atb_debit'], 2, ',', '.') : '-' }}</td>
                    <td class="font-mono text-right">{{ $row['atb_credit'] > 0 ? number_format($row['atb_credit'], 2, ',', '.') : '-' }}</td>
                    <!-- IS -->
                    <td class="font-mono text-right">{{ $row['is_debit'] > 0 ? number_format($row['is_debit'], 2, ',', '.') : '-' }}</td>
                    <td class="font-mono text-right">{{ $row['is_credit'] > 0 ? number_format($row['is_credit'], 2, ',', '.') : '-' }}</td>
                    <!-- BS -->
                    <td class="font-mono text-right">{{ $row['bs_debit'] > 0 ? number_format($row['bs_debit'], 2, ',', '.') : '-' }}</td>
                    <td class="font-mono text-right">{{ $row['bs_credit'] > 0 ? number_format($row['bs_credit'], 2, ',', '.') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 20px; color: #64748b;">
                        Tidak ada data transaksi buku besar pada periode yang dipilih.
                    </td>
                </tr>
            @endforelse

            <!-- SUB-TOTAL BARIS -->
            <tr class="total-row">
                <td colspan="2" class="text-left font-bold" style="padding-left: 6px;">JUMLAH MUTASI SEBELUM LABA/RUGI</td>
                <td class="font-mono text-right font-bold">{{ number_format($totTbDebit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($totTbCredit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($totAdjDebit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($totAdjCredit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($totAtbDebit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($totAtbCredit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($totIsDebit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($totIsCredit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($totBsDebit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($totBsCredit, 2, ',', '.') }}</td>
            </tr>

            <!-- LABA / RUGI BERSIH -->
            @php
                $isNetProfit = $netProfitLoss >= 0;
            @endphp
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="2" class="text-left font-bold" style="padding-left: 6px; color: #1e3a8a;">
                    LABA / (RUGI) BERSIH PERIODE BERJALAN
                </td>
                <td colspan="6" class="text-center font-mono">-</td>
                <!-- Pada Laba Rugi: Jika Laba (Kredit > Debit), selisih ditaruh di Debit agar seimbang -->
                <td class="font-mono text-right font-bold" style="color: #1e3a8a;">
                    {{ $isNetProfit ? number_format($netProfitLoss, 2, ',', '.') : '-' }}
                </td>
                <td class="font-mono text-right font-bold" style="color: #e11d48;">
                    {{ ! $isNetProfit ? number_format(abs($netProfitLoss), 2, ',', '.') : '-' }}
                </td>
                <!-- Pada Neraca: Jika Laba (Modal bertambah di Kredit), selisih ditaruh di Kredit agar seimbang -->
                <td class="font-mono text-right font-bold" style="color: #e11d48;">
                    {{ ! $isNetProfit ? number_format(abs($netProfitLoss), 2, ',', '.') : '-' }}
                </td>
                <td class="font-mono text-right font-bold" style="color: #1e3a8a;">
                    {{ $isNetProfit ? number_format($netProfitLoss, 2, ',', '.') : '-' }}
                </td>
            </tr>

            <!-- GRAND TOTAL (BALANCED) -->
            @php
                $finalIsDebit = $totIsDebit + ($isNetProfit ? $netProfitLoss : 0);
                $finalIsCredit = $totIsCredit + (! $isNetProfit ? abs($netProfitLoss) : 0);
                $finalBsDebit = $totBsDebit + (! $isNetProfit ? abs($netProfitLoss) : 0);
                $finalBsCredit = $totBsCredit + ($isNetProfit ? $netProfitLoss : 0);
            @endphp
            <tr class="grand-total-row">
                <td colspan="2" class="text-left font-bold" style="padding-left: 6px;">TOTAL SEIMBANG (BALANCED)</td>
                <td class="font-mono text-right font-bold">{{ number_format($totTbDebit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($totTbCredit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($totAdjDebit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($totAdjCredit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($totAtbDebit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($totAtbCredit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($finalIsDebit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($finalIsCredit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($finalBsDebit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($finalBsCredit, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
@endsection
