@extends('pdf.reports.layout', ['title' => 'Bukti Jurnal - ' . $entry->entry_number])

@section('report_title', 'BUKTI MEMORIAL / VOUCHER JURNAL')
@section('report_subtitle')
    No. Jurnal: {{ $entry->entry_number }} | No. Bukti: {{ $entry->document_number ?: '-' }} | Tgl: {{ $entry->entry_date->isoFormat('D MMMM Y') }}
@endsection

@section('content')
    <!-- SUMMARY & STATUS PANEL -->
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px 12px; margin-bottom: 12px; font-size: 9.5px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 70%; vertical-align: top;">
                    <div style="font-size: 8px; font-weight: bold; text-transform: uppercase; color: #64748b; margin-bottom: 2px;">
                        KETERANGAN UTAMA TRANSAKSI
                    </div>
                    <div style="font-size: 11px; font-weight: bold; color: #0f172a; line-height: 1.4;">
                        {{ $entry->description ?: 'Transaksi Jurnal Umum' }}
                    </div>
                </td>
                <td style="width: 30%; vertical-align: top; text-align: right;">
                    <div style="font-size: 8px; font-weight: bold; text-transform: uppercase; color: #64748b; margin-bottom: 2px;">
                        STATUS TRANSAKSI
                    </div>
                    <div>
                        @if ($entry->status === 'posted')
                            <span style="display: inline-block; padding: 2px 8px; background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; border-radius: 4px; font-weight: bold; font-size: 9px;">
                                POSTED (DIBUKUKAN)
                            </span>
                        @elseif ($entry->status === 'draft')
                            <span style="display: inline-block; padding: 2px 8px; background-color: #fffbeb; color: #d97706; border: 1px solid #fde68a; border-radius: 4px; font-weight: bold; font-size: 9px;">
                                DRAFT (MENUNGGU PERSETUJUAN)
                            </span>
                        @elseif ($entry->status === 'reversed')
                            <span style="display: inline-block; padding: 2px 8px; background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 4px; font-weight: bold; font-size: 9px;">
                                REVERSED (DIBALIKKAN)
                            </span>
                        @endif
                    </div>
                    <div style="margin-top: 4px; font-size: 8.5px; color: #64748b; font-family: monospace;">
                        Tipe: {{ strtoupper($entry->source_type ?? 'MANUAL') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- JOURNAL LINES TABLE -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 14%;" class="text-left">KODE AKUN</th>
                <th style="width: 35%;" class="text-left">NAMA AKUN & KETERANGAN BARIS</th>
                <th style="width: 16%;" class="text-left">UNIT USAHA</th>
                <th style="width: 15%;" class="text-right">DEBET (IDR)</th>
                <th style="width: 15%;" class="text-right">KREDIT (IDR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entry->lines as $index => $line)
                <tr>
                    <td class="text-center font-mono" style="color: #64748b;">
                        {{ $index + 1 }}
                    </td>
                    <td class="text-left font-mono font-bold" style="color: #1e3a8a;">
                        {{ $line->account?->code }}
                    </td>
                    <td class="text-left">
                        <div style="font-weight: bold; color: #0f172a;">{{ $line->account?->name }}</div>
                        @if(!empty($line->description))
                            <div style="font-size: 8px; color: #475569; font-style: italic; margin-top: 2px;">
                                {{ $line->description }}
                            </div>
                        @endif
                    </td>
                    <td class="text-left" style="font-size: 8.5px; color: #334155;">
                        {{ $line->unit ? $line->unit->code . ' - ' . $line->unit->name : 'Kantor Pusat / Global' }}
                    </td>
                    <td class="font-mono text-right" style="font-weight: bold; color: #0f172a;">
                        {{ $line->debit > 0 ? number_format($line->debit, 2, ',', '.') : '-' }}
                    </td>
                    <td class="font-mono text-right" style="font-weight: bold; color: #0f172a;">
                        {{ $line->credit > 0 ? number_format($line->credit, 2, ',', '.') : '-' }}
                    </td>
                </tr>
            @endforeach

            <!-- GRAND TOTAL -->
            <tr class="grand-total-row">
                <td colspan="4" class="text-right font-bold" style="padding-right: 12px;">
                    TOTAL MUTASI JURNAL
                </td>
                <td class="font-mono text-right font-bold" style="color: #1e3a8a; font-size: 10px;">
                    Rp {{ number_format($entry->total_debit, 2, ',', '.') }}
                </td>
                <td class="font-mono text-right font-bold" style="color: #1e3a8a; font-size: 10px;">
                    Rp {{ number_format($entry->total_credit, 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- TERBILANG / SPELLOUT & AUDIT TRAIL BADGE -->
    <div style="margin-top: 10px; margin-bottom: 15px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 60%; vertical-align: top; padding-right: 10px;">
                    <div style="background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; padding: 6px 10px; font-size: 8.5px;">
                        <span style="font-weight: bold; text-transform: uppercase; color: #475569; display: block; margin-bottom: 2px;">Catatan & Keseimbangan Jurnal:</span>
                        <span style="font-weight: 600; color: {{ abs($entry->total_debit - $entry->total_credit) < 0.01 ? '#059669' : '#dc2626' }};">
                            @if (abs($entry->total_debit - $entry->total_credit) < 0.01)
                                &check; Posisi Jurnal Seimbang (Balance: Debet = Kredit)
                            @else
                                &times; Perhatian: Posisi Jurnal Tidak Seimbang! Selisih: Rp {{ number_format(abs($entry->total_debit - $entry->total_credit), 2, ',', '.') }}
                            @endif
                        </span>
                    </div>
                </td>
                <td style="width: 40%; vertical-align: top;">
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 10px; font-size: 8px; color: #475569;">
                        <strong style="color: #0f172a; display: block; margin-bottom: 2px;">JEJAK AUDIT (AUDIT TRAIL):</strong>
                        Diposting Oleh: <strong>{{ $entry->postedBy?->name ?? 'Sistem Otomatis' }}</strong><br>
                        Waktu Posting: <span class="font-mono">{{ $entry->posted_at ? $entry->posted_at->isoFormat('D MMMM Y HH:mm:ss') : '-' }}</span><br>
                        ID Transaksi: <span class="font-mono" style="font-size: 7.5px;">{{ $entry->uuid ?? ('JE-' . $entry->id) }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
@endsection
