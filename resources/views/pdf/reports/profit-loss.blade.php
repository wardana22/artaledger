@extends('pdf.reports.layout', ['title' => 'Laporan Laba Rugi'])

@section('report_title', 'LAPORAN LABA RUGI')
@section('report_subtitle')
    Periode: {{ $startDate }} s/d {{ $endDate }}
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 18%;" class="text-left">KODE AKUN</th>
                <th style="width: 48%;" class="text-left">NAMA AKUN / DESKRIPSI</th>
                <th style="width: 17%;" class="text-right">RINCIAN (IDR)</th>
                <th style="width: 17%;" class="text-right">TOTAL (IDR)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @php
                    $isHeader = $row['has_children'] || $row['level'] <= 2;
                    $indent = max(0, ($row['level'] - 1) * 12);
                @endphp
                <tr class="{{ $isHeader ? 'header-row' : '' }}">
                    <td class="font-mono text-left" style="padding-left: {{ 6 + $indent }}px;">
                        {{ $row['account']->code }}
                    </td>
                    <td class="text-left" style="padding-left: {{ 6 + $indent }}px; {{ $isHeader ? 'font-weight: bold;' : '' }}">
                        {{ $row['account']->name }}
                    </td>
                    <td class="font-mono text-right">
                        @if($row['rincian'] !== null)
                            {{ number_format($row['rincian'], 2, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="font-mono text-right {{ $isHeader ? 'font-bold' : '' }}">
                        @if($row['total'] !== null)
                            {{ number_format($row['total'], 2, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding: 20px; color: #64748b;">
                        Tidak ada transaksi laba rugi pada periode ini.
                    </td>
                </tr>
            @endforelse

            <!-- RINGKASAN RESMI AKUNTANSI -->
            <tr style="height: 10px; background-color: #ffffff;"><td colspan="4" style="border: none;"></td></tr>
            <tr class="total-row">
                <td colspan="2" class="text-left font-bold" style="padding-left: 10px;">TOTAL PENDAPATAN OPERASIONAL (REVENUE)</td>
                <td class="font-mono text-right font-bold">-</td>
                <td class="font-mono text-right font-bold">{{ number_format($totalRevenue, 2, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="2" class="text-left font-bold" style="padding-left: 10px;">TOTAL HARGA POKOK PENJUALAN (HPP)</td>
                <td class="font-mono text-right font-bold">-</td>
                <td class="font-mono text-right font-bold">({{ number_format($totalHpp, 2, ',', '.') }})</td>
            </tr>
            <tr class="grand-total-row">
                <td colspan="2" class="text-left font-bold" style="padding-left: 10px;">LABA KOTOR (GROSS PROFIT)</td>
                <td class="font-mono text-right font-bold">-</td>
                <td class="font-mono text-right font-bold">{{ number_format($grossProfit, 2, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="2" class="text-left font-bold" style="padding-left: 10px;">TOTAL BEBAN OPERASIONAL (OPERATING EXPENSES)</td>
                <td class="font-mono text-right font-bold">-</td>
                <td class="font-mono text-right font-bold">({{ number_format($totalOperatingExpenses, 2, ',', '.') }})</td>
            </tr>
            <tr class="grand-total-row">
                <td colspan="2" class="text-left font-bold" style="padding-left: 10px;">LABA OPERASIONAL (OPERATING PROFIT)</td>
                <td class="font-mono text-right font-bold">-</td>
                <td class="font-mono text-right font-bold">{{ number_format($operatingProfit, 2, ',', '.') }}</td>
            </tr>
            @if($otherRevenue > 0 || $otherExpense > 0)
                <tr class="total-row">
                    <td colspan="2" class="text-left font-bold" style="padding-left: 10px;">PENDAPATAN / (BEBAN) LAIN-LAIN NETTO</td>
                    <td class="font-mono text-right font-bold">-</td>
                    <td class="font-mono text-right font-bold">{{ number_format($otherRevenue - $otherExpense, 2, ',', '.') }}</td>
                </tr>
            @endif
            @if($taxExpense > 0)
                <tr class="total-row">
                    <td colspan="2" class="text-left font-bold" style="padding-left: 10px;">BEBAN PAJAK PENGHASILAN (TAX EXPENSE)</td>
                    <td class="font-mono text-right font-bold">-</td>
                    <td class="font-mono text-right font-bold">({{ number_format($taxExpense, 2, ',', '.') }})</td>
                </tr>
            @endif
            <tr class="grand-total-row" style="font-size: 10.5px;">
                <td colspan="2" class="text-left font-bold" style="padding-left: 10px;">LABA / (RUGI) SETELAH PAJAK (NET PROFIT AFTER TAX)</td>
                <td class="font-mono text-right font-bold">-</td>
                <td class="font-mono text-right font-bold">{{ number_format($netProfitAfterTax ?? $netProfit, 2, ',', '.') }}</td>
            </tr>
            @if(abs($comprehensiveIncome ?? 0) > 0.001)
                <tr class="total-row">
                    <td colspan="2" class="text-left font-bold" style="padding-left: 10px;">PENDAPATAN / (BEBAN) KOMPREHENSIF LAIN</td>
                    <td class="font-mono text-right font-bold">-</td>
                    <td class="font-mono text-right font-bold">{{ number_format($comprehensiveIncome, 2, ',', '.') }}</td>
                </tr>
                <tr class="grand-total-row" style="font-size: 11px; background-color: #e0e7ff;">
                    <td colspan="2" class="text-left font-bold" style="padding-left: 10px; color: #1e1b4b;">TOTAL LABA / (RUGI) KOMPREHENSIF PERIODE BERJALAN</td>
                    <td class="font-mono text-right font-bold">-</td>
                    <td class="font-mono text-right font-bold" style="color: #1e1b4b;">{{ number_format($totalComprehensiveIncome ?? ($netProfitAfterTax + $comprehensiveIncome), 2, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection
