@extends('pdf.reports.layout', ['title' => 'Laporan Arus Kas'])

@section('report_title', 'LAPORAN ARUS KAS')
@section('report_subtitle')
    Periode: {{ $startDateFormatted ?? $startDate }} s/d {{ $endDateFormatted ?? $endDate }}
@endsection

@php
    function formatCfAmount($amount) {
        if (abs($amount) < 0.005) {
            return '-';
        }
        if ($amount < 0) {
            return '(' . number_format(abs($amount), 2, ',', '.') . ')';
        }
        return number_format($amount, 2, ',', '.');
    }
@endphp

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 72%;" class="text-left">URAIAN / KETERANGAN</th>
                <th style="width: 28%;" class="text-right">REALISASI (RP)</th>
            </tr>
        </thead>
        <tbody>
            <!-- SECTION A: KEGIATAN OPERASI -->
            <tr class="header-row" style="background-color: #f1f5f9;">
                <td colspan="2" class="text-left font-bold" style="padding-left: 8px; text-transform: uppercase;">
                    A. ARUS KAS DARI KEGIATAN OPERASI
                </td>
            </tr>
            @forelse ($sections['operating']['rows'] as $row)
                <tr>
                    <td class="text-left" style="padding-left: 20px;">
                        {{ $row['label'] }}
                    </td>
                    <td class="font-mono text-right" style="{{ $row['value'] < 0 ? 'color: #be123c;' : '' }}">
                        {{ formatCfAmount($row['value']) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="text-left italic" style="padding-left: 20px; color: #64748b;">(Tidak ada rincian)</td>
                    <td class="font-mono text-right">-</td>
                </tr>
            @endforelse
            <tr class="total-row" style="background-color: #f8fafc; font-weight: bold;">
                <td class="text-left" style="padding-left: 8px; text-transform: uppercase;">
                    Jumlah Arus Kas dari Kegiatan Operasi
                </td>
                <td class="font-mono text-right" style="{{ $totalOperating < 0 ? 'color: #be123c;' : '' }}">
                    {{ formatCfAmount($totalOperating) }}
                </td>
            </tr>

            <!-- SECTION B: KEGIATAN INVESTASI -->
            <tr class="header-row" style="background-color: #f1f5f9;">
                <td colspan="2" class="text-left font-bold" style="padding-left: 8px; text-transform: uppercase;">
                    B. ARUS KAS UNTUK KEGIATAN INVESTASI
                </td>
            </tr>
            @forelse ($sections['investing']['rows'] as $row)
                <tr>
                    <td class="text-left" style="padding-left: 20px;">
                        {{ $row['label'] }}
                    </td>
                    <td class="font-mono text-right" style="{{ $row['value'] < 0 ? 'color: #be123c;' : '' }}">
                        {{ formatCfAmount($row['value']) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="text-left italic" style="padding-left: 20px; color: #64748b;">(Tidak ada rincian)</td>
                    <td class="font-mono text-right">-</td>
                </tr>
            @endforelse
            <tr class="total-row" style="background-color: #f8fafc; font-weight: bold;">
                <td class="text-left" style="padding-left: 8px; text-transform: uppercase;">
                    Jumlah Arus Kas untuk Kegiatan Investasi
                </td>
                <td class="font-mono text-right" style="{{ $totalInvesting < 0 ? 'color: #be123c;' : '' }}">
                    {{ formatCfAmount($totalInvesting) }}
                </td>
            </tr>

            <!-- SECTION C: KEGIATAN PEMBIAYAAN -->
            <tr class="header-row" style="background-color: #f1f5f9;">
                <td colspan="2" class="text-left font-bold" style="padding-left: 8px; text-transform: uppercase;">
                    C. ARUS KAS PEMBIAYAAN
                </td>
            </tr>
            @forelse ($sections['financing']['rows'] as $row)
                <tr>
                    <td class="text-left" style="padding-left: 20px;">
                        {{ $row['label'] }}
                    </td>
                    <td class="font-mono text-right" style="{{ $row['value'] < 0 ? 'color: #be123c;' : '' }}">
                        {{ formatCfAmount($row['value']) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="text-left italic" style="padding-left: 20px; color: #64748b;">(Tidak ada rincian)</td>
                    <td class="font-mono text-right">-</td>
                </tr>
            @endforelse
            <tr class="total-row" style="background-color: #f8fafc; font-weight: bold;">
                <td class="text-left" style="padding-left: 8px; text-transform: uppercase;">
                    Jumlah Arus Kas Pembiayaan
                </td>
                <td class="font-mono text-right" style="{{ $totalFinancing < 0 ? 'color: #be123c;' : '' }}">
                    {{ formatCfAmount($totalFinancing) }}
                </td>
            </tr>

            <!-- SUMMARY RECONCILIATION -->
            <tr style="height: 10px; background-color: #ffffff;"><td colspan="2" style="border: none;"></td></tr>

            <tr class="total-row" style="background-color: #e2e8f0; font-weight: bold;">
                <td class="text-left" style="padding-left: 8px; text-transform: uppercase;">
                    KENAIKAN BERSIH KAS (A + B + C)
                </td>
                <td class="font-mono text-right" style="{{ $netCashFlow < 0 ? 'color: #be123c;' : '' }}">
                    {{ formatCfAmount($netCashFlow) }}
                </td>
            </tr>
            <tr>
                <td class="text-left font-bold" style="padding-left: 8px;">
                    Saldo Kas Awal Periode
                </td>
                <td class="font-mono text-right font-bold">
                    {{ formatCfAmount($openingCash) }}
                </td>
            </tr>
            <tr class="grand-total-row" style="font-size: 10.5px; background-color: #0f172a; color: #ffffff;">
                <td class="text-left font-bold" style="padding-left: 8px; color: #ffffff;">
                    SALDO KAS, AKHIR PERIODE
                </td>
                <td class="font-mono text-right font-bold" style="color: #ffffff;">
                    {{ formatCfAmount($endingCash) }}
                </td>
            </tr>
        </tbody>
    </table>
@endsection
