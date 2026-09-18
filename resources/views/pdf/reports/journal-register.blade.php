@extends('pdf.reports.layout', ['title' => 'Daftar Jurnal Transaksi'])

@section('report_title', 'DAFTAR JURNAL TRANSAKSI (JOURNAL REGISTER)')
@section('report_subtitle')
    Periode: {{ $startDate }} s/d {{ $endDate }} | Status: {{ strtoupper($statusLabel) }}
@endsection

@section('custom_styles')
<style>
    .data-table {
        table-layout: fixed;
    }
    .data-table tr {
        page-break-inside: auto;
    }
</style>
@endsection

@section('content')
    @if(!empty($isTruncated))
        <div style="background-color: #fffbeb; border: 1px solid #fef3c7; border-left: 3px solid #f59e0b; padding: 5px 8px; margin-bottom: 8px; font-size: 8px; color: #92400e; border-radius: 2px;">
            <strong>Pemberitahuan Sistem:</strong> Menampilkan <strong>{{ count($journals) }}</strong> entri jurnal pertama dari total <strong>{{ $totalCount }}</strong> transaksi untuk menjaga keandalan rendering PDF. Untuk analisis lengkap seluruh data mutasi tanpa batas, silakan gunakan fitur <strong>Ekspor Excel (.xlsx)</strong>.
        </div>
    @endif

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">TGL</th>
                <th style="width: 14%;" class="text-left">NO. JURNAL / BUKTI</th>
                <th style="width: 12%;" class="text-left">KODE AKUN</th>
                <th style="width: 32%;" class="text-left">NAMA AKUN & KETERANGAN TRANSAKSI</th>
                <th style="width: 10%;" class="text-center">UNIT</th>
                <th style="width: 12%;" class="text-right">DEBET (IDR)</th>
                <th style="width: 12%;" class="text-right">KREDIT (IDR)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($journals as $journal)
                <!-- Header Baris Entri Jurnal -->
                <tr style="background-color: #f8fafc; border-top: 1px solid #cbd5e1;">
                    <td class="text-center font-mono" style="font-weight: bold; color: #0f172a; vertical-align: top;">
                        {{ $journal->entry_date->format('d/m/Y') }}
                    </td>
                    <td class="text-left font-mono" style="vertical-align: top;">
                        <span style="font-weight: bold; color: #1e3a8a;">{{ $journal->entry_number }}</span>
                        @if($journal->document_number)
                            <div style="font-size: 7.5px; color: #64748b;">Doc: {{ $journal->document_number }}</div>
                        @endif
                    </td>
                    <td colspan="3" class="text-left" style="vertical-align: top;">
                        <span style="font-weight: bold; color: #0f172a;">{{ $journal->description ?: 'Transaksi Jurnal Umum' }}</span>
                        <span style="font-size: 7.5px; margin-left: 6px; padding: 1px 4px; border-radius: 3px; font-weight: bold; {{ $journal->status === 'posted' ? 'background-color: #ecfdf5; color: #059669;' : ($journal->status === 'draft' ? 'background-color: #fffbeb; color: #d97706;' : 'background-color: #fef2f2; color: #dc2626;') }}">
                            {{ strtoupper($journal->status) }}
                        </span>
                    </td>
                    <td class="font-mono text-right" style="font-size: 8px; color: #64748b; vertical-align: top;">
                        (Subtotal)
                    </td>
                    <td class="font-mono text-right" style="font-size: 8px; color: #64748b; vertical-align: top;">
                        (Subtotal)
                    </td>
                </tr>

                <!-- Baris Akun (Lines) -->
                @foreach($journal->lines as $line)
                    <tr>
                        <td style="border-top: none;"></td>
                        <td style="border-top: none;"></td>
                        <td class="font-mono text-left" style="font-weight: bold; color: #1e3a8a; padding-left: 8px;">
                            {{ $line->account?->code }}
                        </td>
                        <td class="text-left" style="padding-left: 8px;">
                            <div style="font-weight: 500; color: #1e293b;">{{ $line->account?->name }}</div>
                            @if($line->description && $line->description !== $journal->description)
                                <div style="font-size: 7.5px; color: #64748b; font-style: italic;">
                                    {{ $line->description }}
                                </div>
                            @endif
                        </td>
                        <td class="text-center font-mono" style="font-size: 8px; color: #475569;">
                            {{ $line->unit ? $line->unit->code : 'Global' }}
                        </td>
                        <td class="font-mono text-right" style="font-weight: 600;">
                            {{ $line->debit > 0 ? number_format($line->debit, 2, ',', '.') : '-' }}
                        </td>
                        <td class="font-mono text-right" style="font-weight: 600;">
                            {{ $line->credit > 0 ? number_format($line->credit, 2, ',', '.') : '-' }}
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #64748b;">
                        Tidak ada data transaksi jurnal yang sesuai dengan kriteria filter.
                    </td>
                </tr>
            @endforelse

            <!-- GRAND TOTAL -->
            <tr class="grand-total-row">
                <td colspan="5" class="text-left font-bold" style="padding-left: 10px;">
                    TOTAL MUTASI TRANSAKSI JURNAL
                </td>
                <td class="font-mono text-right font-bold" style="color: #1e3a8a; font-size: 9.5px;">
                    Rp {{ number_format($totalDebit, 2, ',', '.') }}
                </td>
                <td class="font-mono text-right font-bold" style="color: #1e3a8a; font-size: 9.5px;">
                    Rp {{ number_format($totalCredit, 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>
@endsection
