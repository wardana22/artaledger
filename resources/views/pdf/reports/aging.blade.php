@extends('pdf.reports.layout', ['title' => 'Laporan Aging ' . ($type === 'receivable' ? 'Piutang' : 'Hutang')])

@section('report_title', 'LAPORAN UMUR ' . ($type === 'receivable' ? 'PIUTANG USAHA (AR AGING)' : 'HUTANG USAHA (AP AGING)'))
@section('report_subtitle')
    Tanggal Acuan (Cut-Off): {{ $asOfDate }} | Unit: {{ $unitName }}
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 18%;" class="text-left">NO. INVOICE / DOKUMEN</th>
                <th style="width: 20%;" class="text-left">REKANAN / DESKRIPSI</th>
                <th style="width: 10%;" class="text-center">JATUH TEMPO</th>
                <th style="width: 13%;" class="text-right">SALDO TERBUKA</th>
                <th style="width: 10%;" class="text-right">LANCAR</th>
                <th style="width: 10%;" class="text-right">1-30 HR</th>
                <th style="width: 10%;" class="text-right">31-60 HR</th>
                <th style="width: 10%;" class="text-right">61-90 HR</th>
                <th style="width: 10%;" class="text-right">&gt; 90 HR</th>
            </tr>
        </thead>
        <tbody>
            @forelse($accounts as $accItem)
                <tr class="header-row" style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="3" class="text-left" style="padding-left: 8px;">
                        {{ $accItem['account']['code'] }} - {{ $accItem['account']['name'] }}
                    </td>
                    <td class="font-mono text-right">
                        {{ number_format($accItem['subtotal']['total_outstanding'], 2, ',', '.') }}
                    </td>
                    <td class="font-mono text-right">
                        {{ $accItem['subtotal']['current'] > 0 ? number_format($accItem['subtotal']['current'], 2, ',', '.') : '-' }}
                    </td>
                    <td class="font-mono text-right">
                        {{ $accItem['subtotal']['overdue_1_30'] > 0 ? number_format($accItem['subtotal']['overdue_1_30'], 2, ',', '.') : '-' }}
                    </td>
                    <td class="font-mono text-right">
                        {{ $accItem['subtotal']['overdue_31_60'] > 0 ? number_format($accItem['subtotal']['overdue_31_60'], 2, ',', '.') : '-' }}
                    </td>
                    <td class="font-mono text-right">
                        {{ $accItem['subtotal']['overdue_61_90'] > 0 ? number_format($accItem['subtotal']['overdue_61_90'], 2, ',', '.') : '-' }}
                    </td>
                    <td class="font-mono text-right">
                        {{ $accItem['subtotal']['overdue_over_90'] > 0 ? number_format($accItem['subtotal']['overdue_over_90'], 2, ',', '.') : '-' }}
                    </td>
                </tr>

                @foreach($accItem['invoices'] as $inv)
                    <tr>
                        <td class="text-left font-mono" style="padding-left: 16px;">
                            {{ $inv['invoice_number'] }}
                        </td>
                        <td class="text-left">
                            {{ $inv['partner_name'] }}
                        </td>
                        <td class="text-center font-mono">
                            {{ $inv['due_date'] }}
                        </td>
                        <td class="font-mono text-right font-bold">
                            {{ number_format($inv['remaining_amount'], 2, ',', '.') }}
                        </td>
                        <td class="font-mono text-right">
                            {{ $inv['buckets']['current'] > 0 ? number_format($inv['buckets']['current'], 2, ',', '.') : '-' }}
                        </td>
                        <td class="font-mono text-right">
                            {{ $inv['buckets']['overdue_1_30'] > 0 ? number_format($inv['buckets']['overdue_1_30'], 2, ',', '.') : '-' }}
                        </td>
                        <td class="font-mono text-right">
                            {{ $inv['buckets']['overdue_31_60'] > 0 ? number_format($inv['buckets']['overdue_31_60'], 2, ',', '.') : '-' }}
                        </td>
                        <td class="font-mono text-right">
                            {{ $inv['buckets']['overdue_61_90'] > 0 ? number_format($inv['buckets']['overdue_61_90'], 2, ',', '.') : '-' }}
                        </td>
                        <td class="font-mono text-right">
                            {{ $inv['buckets']['overdue_over_90'] > 0 ? number_format($inv['buckets']['overdue_over_90'], 2, ',', '.') : '-' }}
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px; color: #64748b;">
                        Tidak ada tagihan terbuka per tanggal acuan ini.
                    </td>
                </tr>
            @endforelse

            <tr class="grand-total-row" style="font-size: 10px;">
                <td colspan="3" class="text-left font-bold" style="padding-left: 8px;">TOTAL KESELURUHAN (GRAND TOTAL)</td>
                <td class="font-mono text-right font-bold">{{ number_format($kpi['total_outstanding'], 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($kpi['current'], 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($kpi['overdue_1_30'], 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($kpi['overdue_31_60'], 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($kpi['overdue_61_90'], 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($kpi['overdue_over_90'], 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
@endsection
