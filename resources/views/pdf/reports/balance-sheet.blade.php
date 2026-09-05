@extends('pdf.reports.layout', ['title' => 'Laporan Neraca'])

@section('report_title', 'LAPORAN POSISI KEUANGAN (NERACA)')
@section('report_subtitle')
    Per Tanggal: {{ $asOfDate }}
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 18%;" class="text-left">KODE AKUN</th>
                <th style="width: 58%;" class="text-left">NAMA AKUN / KLASIFIKASI</th>
                <th style="width: 24%;" class="text-right">SALDO (IDR)</th>
            </tr>
        </thead>
        <tbody>
            <!-- SECTION 1: ASET / AKTIVA -->
            <tr style="background-color: #1e3a8a; color: #ffffff;">
                <td colspan="3" class="font-bold" style="padding: 6px 8px; font-size: 10px; text-transform: uppercase;">
                    I. ASET (ASSETS)
                </td>
            </tr>
            @forelse($assetRows as $row)
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
                    <td class="font-mono text-right {{ $isHeader ? 'font-bold' : '' }}">
                        {{ number_format($row['amount'], 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center" style="padding: 10px; color: #64748b;">Tidak ada data aset.</td></tr>
            @endforelse
            <tr class="grand-total-row">
                <td colspan="2" class="text-left font-bold" style="padding-left: 10px;">TOTAL ASET (TOTAL ASSETS)</td>
                <td class="font-mono text-right font-bold">{{ number_format($totalAssets, 2, ',', '.') }}</td>
            </tr>

            <!-- SECTION 2: KEWAJIBAN / LIABILITAS -->
            <tr style="height: 10px; background-color: #ffffff;"><td colspan="3" style="border: none;"></td></tr>
            <tr style="background-color: #1e3a8a; color: #ffffff;">
                <td colspan="3" class="font-bold" style="padding: 6px 8px; font-size: 10px; text-transform: uppercase;">
                    II. LIABILITAS (LIABILITIES)
                </td>
            </tr>
            @forelse($liabilityRows as $row)
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
                    <td class="font-mono text-right {{ $isHeader ? 'font-bold' : '' }}">
                        {{ number_format($row['amount'], 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center" style="padding: 10px; color: #64748b;">Tidak ada data liabilitas.</td></tr>
            @endforelse
            <tr class="total-row">
                <td colspan="2" class="text-left font-bold" style="padding-left: 10px;">TOTAL LIABILITAS (TOTAL LIABILITIES)</td>
                <td class="font-mono text-right font-bold">{{ number_format($totalLiabilities, 2, ',', '.') }}</td>
            </tr>

            <!-- SECTION 3: EKUITAS -->
            <tr style="height: 10px; background-color: #ffffff;"><td colspan="3" style="border: none;"></td></tr>
            <tr style="background-color: #1e3a8a; color: #ffffff;">
                <td colspan="3" class="font-bold" style="padding: 6px 8px; font-size: 10px; text-transform: uppercase;">
                    III. EKUITAS (EQUITY)
                </td>
            </tr>
            @forelse($equityRows as $row)
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
                    <td class="font-mono text-right {{ $isHeader ? 'font-bold' : '' }}">
                        {{ number_format($row['amount'], 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center" style="padding: 10px; color: #64748b;">Tidak ada data ekuitas.</td></tr>
            @endforelse
            <tr class="total-row">
                <td colspan="2" class="text-left font-bold" style="padding-left: 10px;">TOTAL EKUITAS (TOTAL EQUITY)</td>
                <td class="font-mono text-right font-bold">{{ number_format($totalEquity, 2, ',', '.') }}</td>
            </tr>

            <!-- TOTAL LIABILITAS + EKUITAS -->
            <tr class="grand-total-row" style="font-size: 10px;">
                <td colspan="2" class="text-left font-bold" style="padding-left: 10px;">TOTAL LIABILITAS & EKUITAS</td>
                <td class="font-mono text-right font-bold">{{ number_format($totalLiabilitiesAndEquity, 2, ',', '.') }}</td>
            </tr>

            @if($isBalanced)
                <tr>
                    <td colspan="3" class="text-center" style="background-color: #ecfdf5; color: #047857; font-weight: bold; padding: 5px; border: 1px solid #10b981;">
                        ✓ POSISI KEUANGAN SEIMBANG (BALANCE): TOTAL ASET = TOTAL LIABILITAS & EKUITAS (SELISIH Rp 0,00)
                    </td>
                </tr>
            @else
                <tr>
                    <td colspan="3" class="text-center" style="background-color: #fef2f2; color: #b91c1c; font-weight: bold; padding: 5px; border: 1px solid #ef4444;">
                        ⚠ SELISIH (UNBALANCED): Rp {{ number_format(abs($totalAssets - $totalLiabilitiesAndEquity), 2, ',', '.') }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection
