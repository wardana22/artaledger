<?php

namespace App\Http\Controllers;

use App\Domain\Banking\Services\BankReconciliationService;
use App\Models\BankStatement;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;

class BankReconciliationPdfController extends Controller
{
    public function export(BankStatement $statement, BankReconciliationService $service): Response
    {
        $user = auth()->user();
        if ($user && ! $user->can('reconciliation.view') && ! $user->can('reports.view')) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Laporan Rekonsiliasi Bank.');
        }

        $summary = $service->calculateSummary($statement);
        $company = Company::first();

        $data = [
            'company' => $company,
            'unitName' => 'Konsolidasi',
            'statement' => $statement,
            'summary' => $summary,
            'printedAt' => Carbon::now()->isoFormat('D MMMM Y HH:mm'),
            'printedBy' => $user ? $user->name : 'Administrator',
        ];

        $pdf = Pdf::loadView('pdf.reports.reconciliation-report', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        $filename = "Laporan-Rekonsiliasi-Bank-{$statement->account_number}-".Carbon::parse($statement->period_end)->format('Y-m').'.pdf';

        return $pdf->stream($filename);
    }
}
