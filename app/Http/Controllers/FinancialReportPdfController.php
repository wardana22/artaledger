<?php

namespace App\Http\Controllers;

use App\Domain\Accounting\Services\FinancialReportPdfService;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FinancialReportPdfController extends Controller
{
    public function __construct(
        protected FinancialReportPdfService $pdfService
    ) {}

    public function export(Request $request, string $type): Response
    {
        $user = $request->user();

        $unitFilter = $request->query('unit', 'all');
        $startDate = $request->query('start_date', date('Y-01-01'));
        $endDate = $request->query('end_date', date('Y-12-31'));
        $asOfDate = $request->query('as_of_date', date('Y-12-31'));
        $isDownload = $request->boolean('download', false);

        switch ($type) {
            case 'profit-loss':
                if ($user && ! $user->can('reports.profit_loss') && ! $user->can('reports.view')) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Laporan Laba Rugi.');
                }
                $pdf = $this->pdfService->renderProfitLossPdf($startDate, $endDate, $unitFilter, $user);
                $filename = "Laporan-Laba-Rugi-{$startDate}-sd-{$endDate}.pdf";
                break;

            case 'balance-sheet':
                if ($user && ! $user->can('reports.balance_sheet') && ! $user->can('reports.view')) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Laporan Neraca.');
                }
                $pdf = $this->pdfService->renderBalanceSheetPdf($asOfDate, $unitFilter, $user);
                $filename = "Laporan-Neraca-per-{$asOfDate}.pdf";
                break;

            case 'trial-balance':
                if ($user && ! $user->can('reports.trial_balance') && ! $user->can('reports.view')) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Neraca Saldo.');
                }
                $pdf = $this->pdfService->renderTrialBalancePdf($startDate, $endDate, $unitFilter, $user);
                $filename = "Neraca-Saldo-{$startDate}-sd-{$endDate}.pdf";
                break;

            case 'general-ledger':
                if ($user && ! $user->can('reports.general_ledger') && ! $user->can('reports.view')) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Buku Besar.');
                }
                $accountId = (int) $request->query('account_id', 0);
                if ($accountId <= 0) {
                    $firstAccount = Account::group()->active()->orderBy('code', 'asc')->first();
                    $accountId = $firstAccount ? (int) $firstAccount->id : 1;
                }
                $pdf = $this->pdfService->renderGeneralLedgerPdf($accountId, $startDate, $endDate, $unitFilter, $user);
                $filename = "Buku-Besar-Akun-{$accountId}-{$startDate}-sd-{$endDate}.pdf";
                break;

            case 'subsidiary-ledger':
                if ($user && ! $user->can('reports.subsidiary_ledger') && ! $user->can('reports.view')) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Buku Besar Pembantu.');
                }
                $accountId = (int) $request->query('account_id', 0);
                if ($accountId <= 0) {
                    $firstPosting = Account::posting()->active()->orderBy('code', 'asc')->first();
                    $accountId = $firstPosting ? (int) $firstPosting->id : 1;
                }
                $pdf = $this->pdfService->renderSubsidiaryLedgerPdf($accountId, $startDate, $endDate, $unitFilter, $user);
                $filename = "Buku-Besar-Pembantu-Akun-{$accountId}-{$startDate}-sd-{$endDate}.pdf";
                break;

            case 'cash-flow':
                if ($user && ! $user->can('reports.cash_flow') && ! $user->can('reports.view')) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Laporan Arus Kas.');
                }
                $pdf = $this->pdfService->renderCashFlowPdf($startDate, $endDate, $unitFilter, $user);
                $filename = "Laporan-Arus-Kas-{$startDate}-sd-{$endDate}.pdf";
                break;

            case 'changes-in-equity':
                if ($user && ! $user->can('reports.changes_in_equity') && ! $user->can('reports.view')) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Laporan Perubahan Ekuitas.');
                }
                $pdf = $this->pdfService->renderChangesInEquityPdf($startDate, $endDate, $unitFilter, $user);
                $filename = "Laporan-Perubahan-Ekuitas-{$startDate}-sd-{$endDate}.pdf";
                break;

            case 'worksheet':
                if ($user && ! $user->can('reports.worksheet') && ! $user->can('reports.view')) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Neraca Lajur (Worksheet).');
                }
                $pdf = $this->pdfService->renderWorksheetPdf($startDate, $endDate, $unitFilter, $user);
                $filename = "Neraca-Lajur-Worksheet-{$startDate}-sd-{$endDate}.pdf";
                break;

            case 'opening-balance':
                if ($user && ! $user->can('reports.opening_balance') && ! $user->can('reports.view')) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Saldo Awal.');
                }
                $periodId = $request->has('period_id') ? (int) $request->query('period_id') : null;
                $mode = $request->query('mode', 'balance_sheet');
                $pdf = $this->pdfService->renderOpeningBalancePdf($periodId, $unitFilter, $user, $mode);
                $filename = 'Laporan-Saldo-Awal'.($periodId ? "-Periode-{$periodId}" : '')."-{$mode}.pdf";
                break;

            case 'aging-receivable':
            case 'aging-payable':
                if ($user && ! $user->can('reports.view')) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Laporan Aging.');
                }
                $agingType = $type === 'aging-receivable' ? 'receivable' : 'payable';
                $pdf = $this->pdfService->renderAgingPdf($agingType, $asOfDate, $unitFilter, $user);
                $label = $agingType === 'receivable' ? 'Piutang' : 'Hutang';
                $filename = "Laporan-Aging-{$label}-per-{$asOfDate}.pdf";
                break;

            default:
                abort(404, "Jenis laporan '{$type}' tidak ditemukan.");
        }

        if ($isDownload) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
