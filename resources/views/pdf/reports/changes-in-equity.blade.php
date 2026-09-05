@extends('pdf.reports.layout', ['title' => 'Laporan Perubahan Ekuitas'])

@section('report_title', 'LAPORAN PERUBAHAN EKUITAS')
@section('report_subtitle')
    Periode: {{ $startDate }} s/d {{ $endDate }}
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 70%;" class="text-left">KOMPONEN EKUITAS / MODAL</th>
                <th style="width: 30%;" class="text-right">JUMLAH (IDR)</th>
            </tr>
        </thead>
        <tbody>
            <!-- 1. EKUITAS AWAL -->
            <tr class="header-row">
                <td class="text-left font-bold" style="padding-left: 10px;">1. SALDO EKUITAS / MODAL AWAL PERIODE</td>
                <td class="font-mono text-right font-bold" style="color: #1e3a8a;">
                    {{ number_format($initialEquity, 2, ',', '.') }}
                </td>
            </tr>

            @if(isset($equityAccounts) && count($equityAccounts) > 0)
                @foreach($equityAccounts as $acc)
                    @if((float)$acc->opening_balance != 0)
                        <tr>
                            <td class="text-left" style="padding-left: 24px; color: #475569;">
                                {{ $acc->code }} - {{ $acc->name }}
                            </td>
                            <td class="font-mono text-right" style="color: #475569;">
                                {{ number_format((float)$acc->opening_balance, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                @endforeach
            @endif

            <!-- 2. PERGERAKAN LABA BERSIH PERIODE BERJALAN -->
            <tr class="header-row" style="background-color: #f8fafc;">
                <td colspan="2" class="text-left font-bold" style="padding-left: 10px; text-transform: uppercase;">
                    2. PERUBAHAN & PERGERAKAN PERIODE BERJALAN
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 24px;">
                    Total Pendapatan Operasional & Non-Operasional
                </td>
                <td class="font-mono text-right">
                    {{ number_format($revenue, 2, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 24px;">
                    Total Beban Pokok, Operasional & Pajak
                </td>
                <td class="font-mono text-right">
                    ({{ number_format($expenses, 2, ',', '.') }})
                </td>
            </tr>
            <tr class="total-row">
                <td class="text-left font-bold" style="padding-left: 24px;">
                    LABA / (RUGI) BERSIH PERIODE BERJALAN
                </td>
                <td class="font-mono text-right font-bold {{ $netProfit >= 0 ? '' : 'text-danger' }}">
                    {{ number_format($netProfit, 2, ',', '.') }}
                </td>
            </tr>

            <!-- 3. EKUITAS AKHIR -->
            <tr style="height: 12px; background-color: #ffffff;"><td colspan="2" style="border: none;"></td></tr>
            <tr class="grand-total-row" style="font-size: 10.5px;">
                <td class="text-left font-bold" style="padding-left: 10px;">
                    SALDO EKUITAS / MODAL AKHIR PERIODE (1 + 2)
                </td>
                <td class="font-mono text-right font-bold">
                    {{ number_format($endingEquity, 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>
@endsection
