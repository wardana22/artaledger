@extends('pdf.reports.layout', ['title' => 'Laporan Saldo Awal'])

@section('report_title', 'LAPORAN SALDO AWAL (OPENING BALANCE)')
@section('report_subtitle')
    Periode Akuntansi: {{ $periodName }}
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;" class="text-left">KODE AKUN</th>
                <th style="width: 45%;" class="text-left">NAMA AKUN</th>
                <th style="width: 20%;" class="text-right">DEBET (IDR)</th>
                <th style="width: 20%;" class="text-right">KREDIT (IDR)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lines as $line)
                <tr>
                    <td class="font-mono text-left">{{ $line['account']->code }}</td>
                    <td class="text-left">{{ $line['account']->name }}</td>
                    <td class="font-mono text-right">
                        {{ $line['debit'] > 0 ? number_format($line['debit'], 2, ',', '.') : '-' }}
                    </td>
                    <td class="font-mono text-right">
                        {{ $line['credit'] > 0 ? number_format($line['credit'], 2, ',', '.') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding: 20px; color: #64748b;">
                        Tidak ada saldo awal yang dicatat pada periode ini.
                    </td>
                </tr>
            @endforelse

            <tr class="grand-total-row">
                <td colspan="2" class="text-left font-bold" style="padding-left: 10px;">
                    TOTAL SALDO AWAL
                    @if($isBalanced)
                        <span style="font-size: 8px; font-weight: normal; color: #047857; margin-left: 8px;">[SEIMBANG / BALANCED]</span>
                    @else
                        <span style="font-size: 8px; font-weight: bold; color: #b91c1c; margin-left: 8px;">[TIDAK SEIMBANG / OUT OF BALANCE]</span>
                    @endif
                </td>
                <td class="font-mono text-right font-bold">{{ number_format($totalDebit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($totalCredit, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
@endsection
