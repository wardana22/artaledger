@extends('pdf.reports.layout', ['title' => 'Neraca Saldo'])

@section('report_title', 'NERACA SALDO (TRIAL BALANCE)')
@section('report_subtitle')
    Periode: {{ $startDate }} s/d {{ $endDate }}
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%;" class="text-left">KODE AKUN</th>
                <th style="width: 28%;" class="text-left">NAMA AKUN</th>
                <th style="width: 15%;" class="text-right">SALDO AWAL</th>
                <th style="width: 15%;" class="text-right">MUTASI DEBET</th>
                <th style="width: 15%;" class="text-right">MUTASI KREDIT</th>
                <th style="width: 15%;" class="text-right">SALDO AKHIR DEBET</th>
                <th style="width: 15%;" class="text-right">SALDO AKHIR KREDIT</th>
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
                    <td class="font-mono text-right {{ $isHeader ? 'font-bold' : '' }}">
                        {{ number_format($row['opening_balance'], 2, ',', '.') }}
                    </td>
                    <td class="font-mono text-right {{ $isHeader ? 'font-bold' : '' }}">
                        {{ number_format($row['debit_mutation'], 2, ',', '.') }}
                    </td>
                    <td class="font-mono text-right {{ $isHeader ? 'font-bold' : '' }}">
                        {{ number_format($row['credit_mutation'], 2, ',', '.') }}
                    </td>
                    <td class="font-mono text-right {{ $isHeader ? 'font-bold' : '' }}">
                        {{ $row['end_debit'] > 0 ? number_format($row['end_debit'], 2, ',', '.') : '-' }}
                    </td>
                    <td class="font-mono text-right {{ $isHeader ? 'font-bold' : '' }}">
                        {{ $row['end_credit'] > 0 ? number_format($row['end_credit'], 2, ',', '.') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #64748b;">
                        Tidak ada transaksi pada periode ini.
                    </td>
                </tr>
            @endforelse

            <tr class="grand-total-row" style="font-size: 10px;">
                <td colspan="5" class="text-left font-bold" style="padding-left: 10px;">TOTAL SALDO AKHIR (SUM OF BALANCES)</td>
                <td class="font-mono text-right font-bold">{{ number_format($totalDebit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($totalCredit, 2, ',', '.') }}</td>
            </tr>

            @if($isBalanced)
                <tr>
                    <td colspan="7" class="text-center" style="background-color: #ecfdf5; color: #047857; font-weight: bold; padding: 5px; border: 1px solid #10b981;">
                        ✓ TOTAL DEBET = TOTAL KREDIT (BALANCE 100% — SELISIH Rp 0,00)
                    </td>
                </tr>
            @else
                <tr>
                    <td colspan="7" class="text-center" style="background-color: #fef2f2; color: #b91c1c; font-weight: bold; padding: 5px; border: 1px solid #ef4444;">
                        ⚠ SELISIH (UNBALANCED): Rp {{ number_format(abs($totalDebit - $totalCredit), 2, ',', '.') }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection
