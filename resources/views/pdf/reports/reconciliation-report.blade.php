@extends('pdf.reports.layout', ['title' => 'Laporan Rekonsiliasi Bank'])

@section('report_title', 'LAPORAN REKONSILIASI BANK')
@section('report_subtitle')
    Bank: {{ $statement->bank_name }} | No. Rekening: {{ $statement->account_number }} ({{ $statement->account_holder }})<br>
    Periode: {{ \Carbon\Carbon::parse($statement->period_start)->isoFormat('D MMMM Y') }} s/d {{ \Carbon\Carbon::parse($statement->period_end)->isoFormat('D MMMM Y') }}
@endsection

@section('content')
    <!-- RINGKASAN REKONSILIASI -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 70%;" class="text-left">KOMPONEN REKONSILIASI</th>
                <th style="width: 30%;" class="text-right">JUMLAH (IDR)</th>
            </tr>
        </thead>
        <tbody>
            <!-- BAGIAN 1: REKONSILIASI BUKU BESAR PERUSAHAAN -->
            <tr class="header-row">
                <td colspan="2" class="text-left font-bold" style="padding-left: 10px;">
                    A. SALDO MENURUT BUKU BESAR PERUSAHAAN
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 20px;">
                    Saldo Buku Kas/Bank Sebelum Penyesuaian
                </td>
                <td class="font-mono text-right font-bold">
                    {{ number_format($summary['book_balance'], 2, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 20px;">
                    (+) Penerimaan di Bank belum dicatat di Buku (Jasa Giro / Transfer Masuk)
                </td>
                <td class="font-mono text-right">
                    {{ number_format($summary['unrecorded_bank_credits'], 2, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 20px;">
                    (-) Pengeluaran di Bank belum dicatat di Buku (Beban Admin Bank / Pajak)
                </td>
                <td class="font-mono text-right">
                    ({{ number_format($summary['unrecorded_bank_debits'], 2, ',', '.') }})
                </td>
            </tr>
            <tr class="total-row">
                <td class="text-left font-bold" style="padding-left: 20px;">
                    SALDO BUKU BESAR YANG DISESUAIKAN (ADJUSTED BOOK BALANCE)
                </td>
                <td class="font-mono text-right font-bold">
                    {{ number_format($summary['adjusted_book_balance'], 2, ',', '.') }}
                </td>
            </tr>

            <!-- BAGIAN 2: REKONSILIASI REKENING KORAN BANK -->
            <tr style="height: 10px; background-color: #ffffff;"><td colspan="2" style="border: none;"></td></tr>
            <tr class="header-row">
                <td colspan="2" class="text-left font-bold" style="padding-left: 10px;">
                    B. SALDO MENURUT REKENING KORAN BANK
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 20px;">
                    Saldo Rekening Koran Akhir Periode (e-Statement)
                </td>
                <td class="font-mono text-right font-bold">
                    {{ number_format($summary['bank_balance'], 2, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 20px;">
                    (+) Setoran dalam Perjalanan (Deposits in Transit)
                </td>
                <td class="font-mono text-right">
                    {{ number_format($summary['deposits_in_transit'], 2, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 20px;">
                    (-) Cek / Kliring yang Masih Beredar (Outstanding Checks)
                </td>
                <td class="font-mono text-right">
                    ({{ number_format($summary['outstanding_checks'], 2, ',', '.') }})
                </td>
            </tr>
            <tr class="total-row">
                <td class="text-left font-bold" style="padding-left: 20px;">
                    SALDO REKENING BANK YANG DISESUAIKAN (ADJUSTED BANK BALANCE)
                </td>
                <td class="font-mono text-right font-bold">
                    {{ number_format($summary['adjusted_bank_balance'], 2, ',', '.') }}
                </td>
            </tr>

            <!-- SELISIH -->
            <tr style="height: 10px; background-color: #ffffff;"><td colspan="2" style="border: none;"></td></tr>
            <tr class="grand-total-row" style="font-size: 10px;">
                <td class="text-left font-bold" style="padding-left: 10px;">
                    SELISIH REKONSILIASI (A - B)
                    @if($summary['difference'] < 0.01)
                        <span style="font-size: 8.5px; font-weight: bold; color: #047857; margin-left: 8px;">[KLOP / BALANCED]</span>
                    @else
                        <span style="font-size: 8.5px; font-weight: bold; color: #b91c1c; margin-left: 8px;">[TERDAPAT SELISIH]</span>
                    @endif
                </td>
                <td class="font-mono text-right font-bold">
                    Rp {{ number_format($summary['difference'], 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- STATUS PENCOCOKAN TRANSAKSI -->
    <div style="margin-top: 15px; margin-bottom: 5px; font-size: 9px; font-weight: bold; text-transform: uppercase; color: #475569;">
        Statistik Pencocokan Mutasi: {{ $summary['total_matched_count'] }} transaksi cocok ({{ $summary['reconciled_percentage'] }}%), {{ $summary['total_unmatched_count'] }} transaksi belum cocok.
    </div>
@endsection
