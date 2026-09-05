@extends('pdf.reports.layout', ['title' => 'Buku Besar Pembantu'])

@section('report_title', 'BUKU BESAR PEMBANTU')
@section('report_subtitle')
    Akun: {{ $account->code }} - {{ $account->name }} | Periode: {{ $startDate }} s/d {{ $endDate }}
@endsection

@section('content')
    <div style="background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; padding: 6px 10px; margin-bottom: 10px; font-size: 9.5px;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 60%;">
                    <strong>Akun:</strong> <span class="font-mono font-bold">{{ $account->code }}</span> - {{ $account->name }}<br>
                    <strong>Posisi Normal:</strong> {{ strtoupper($account->normal_balance) }} | <strong>Tipe:</strong> {{ ucfirst($account->report_type) }}
                </td>
                <td style="width: 40%; text-align: right;">
                    <strong>Saldo Awal (Opening):</strong><br>
                    <span class="font-mono font-bold" style="font-size: 11px; color: #1e3a8a;">
                        Rp {{ number_format($openingBalance, 2, ',', '.') }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">TGL</th>
                <th style="width: 12%;" class="text-left">NO. JURNAL</th>
                <th style="width: 12%;" class="text-left">NO. BUKTI</th>
                <th style="width: 6%;" class="text-center">UNIT</th>
                <th style="width: 26%;" class="text-left">KETERANGAN TRANSAKSI</th>
                <th style="width: 12%;" class="text-right">DEBET (IDR)</th>
                <th style="width: 12%;" class="text-right">KREDIT (IDR)</th>
                <th style="width: 12%;" class="text-right">SALDO (IDR)</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="7" class="text-left" style="padding-left: 10px;">SALDO AWAL PERIODE</td>
                <td class="font-mono text-right" style="color: #1e3a8a;">
                    {{ number_format($openingBalance, 2, ',', '.') }}
                </td>
            </tr>
            @forelse($reportLines as $line)
                <tr>
                    <td class="text-center font-mono">{{ $line['date'] }}</td>
                    <td class="text-left font-mono font-bold">{{ $line['entry_number'] }}</td>
                    <td class="text-left font-mono">{{ $line['document_number'] }}</td>
                    <td class="text-center font-mono">{{ $line['unit_code'] }}</td>
                    <td class="text-left">
                        {{ $line['description'] }}
                    </td>
                    <td class="font-mono text-right">
                        {{ $line['debit'] > 0 ? number_format($line['debit'], 2, ',', '.') : '-' }}
                    </td>
                    <td class="font-mono text-right">
                        {{ $line['credit'] > 0 ? number_format($line['credit'], 2, ',', '.') : '-' }}
                    </td>
                    <td class="font-mono text-right font-bold">
                        {{ number_format($line['balance'], 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px; color: #64748b;">
                        Tidak ada mutasi jurnal untuk akun ini pada periode yang dipilih.
                    </td>
                </tr>
            @endforelse

            <tr class="total-row">
                <td colspan="5" class="text-left font-bold" style="padding-left: 10px;">TOTAL MUTASI PERIODE</td>
                <td class="font-mono text-right font-bold">{{ number_format($totalDebit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">{{ number_format($totalCredit, 2, ',', '.') }}</td>
                <td class="font-mono text-right font-bold">-</td>
            </tr>

            <tr class="grand-total-row" style="font-size: 10px;">
                <td colspan="7" class="text-left font-bold" style="padding-left: 10px;">SALDO AKHIR (CLOSING BALANCE)</td>
                <td class="font-mono text-right font-bold">{{ number_format($closingBalance, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
@endsection
