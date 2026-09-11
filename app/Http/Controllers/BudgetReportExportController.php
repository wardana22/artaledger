<?php

namespace App\Http\Controllers;

use App\Domain\Budget\Services\BudgetCalculationService;
use App\Models\Budget;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BudgetReportExportController extends Controller
{
    public function exportPdf(Request $request, BudgetCalculationService $service)
    {
        $budgetId = (int) $request->query('budgetId');
        $month = $request->filled('month') ? (int) $request->query('month') : null;
        $unitId = $request->filled('unitId') ? (int) $request->query('unitId') : null;

        $budget = Budget::findOrFail($budgetId);
        $company = Company::first();

        $reportData = $service->calculateBudgetComparison($budget, $month, $unitId);

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $pdf = Pdf::loadView('pdf.budget-variance-report', [
            'budget' => $budget,
            'company' => $company,
            'reportData' => $reportData,
            'monthNames' => $monthNames,
        ]);

        $periodLabel = $month ? "Bulan-{$month}" : 'Tahunan';
        $filename = "Laporan-Varian-Anggaran-{$budget->fiscal_year}-{$periodLabel}.pdf";

        return $pdf->stream($filename);
    }

    public function exportExcel(Request $request, BudgetCalculationService $service): StreamedResponse
    {
        $budgetId = (int) $request->query('budgetId');
        $month = $request->filled('month') ? (int) $request->query('month') : null;
        $unitId = $request->filled('unitId') ? (int) $request->query('unitId') : null;

        $budget = Budget::findOrFail($budgetId);
        $reportData = $service->calculateBudgetComparison($budget, $month, $unitId);

        $periodLabel = $month ? "Bulan-{$month}" : 'Tahunan';
        $filename = "Laporan-Varian-Anggaran-{$budget->fiscal_year}-{$periodLabel}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function () use ($reportData) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header info
            fputcsv($handle, ['LAPORAN ANALISIS VARIAN ANGGARAN (BUDGET VS ACTUAL)']);
            fputcsv($handle, ['Tahun Fiskal', $reportData['year']]);
            fputcsv($handle, ['Periode', $reportData['month'] ? "Bulan {$reportData['month']}" : 'Setahun Penuh']);
            fputcsv($handle, []);

            // Summary
            fputcsv($handle, ['Total Pagu Anggaran', $reportData['summary']['total_budget']]);
            fputcsv($handle, ['Total Realisasi Aktual', $reportData['summary']['total_actual']]);
            fputcsv($handle, ['Sisa Pagu / Varian', $reportData['summary']['total_variance']]);
            fputcsv($handle, ['Tingkat Serapan Agregat (%)', $reportData['summary']['aggregate_absorption'].'%']);
            fputcsv($handle, []);

            // Table headers
            fputcsv($handle, [
                'Kode Akun',
                'Nama Akun',
                'Unit Bisnis',
                'Plafon Anggaran (Rp)',
                'Realisasi Aktual (Rp)',
                'Sisa / Varian (Rp)',
                'Serapan (%)',
                'Status',
            ]);

            // Table rows
            foreach ($reportData['items'] as $item) {
                fputcsv($handle, [
                    $item['account_code'],
                    $item['account_name'],
                    $item['unit_name'],
                    $item['budget_amount'],
                    $item['actual_amount'],
                    $item['variance_amount'],
                    $item['absorption_rate'].'%',
                    strtoupper($item['status']),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
