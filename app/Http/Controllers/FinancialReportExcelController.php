<?php

namespace App\Http\Controllers;

use App\Domain\Accounting\Services\FinancialReportExcelService;
use App\Models\Account;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialReportExcelController extends Controller
{
    public function __construct(
        protected FinancialReportExcelService $excelService
    ) {}

    public function export(Request $request, string $type): StreamedResponse
    {
        $user = $request->user();

        $unitFilter = $request->query('unit', 'all');
        $startDate = $request->query('start_date', date('Y-01-01'));
        $endDate = $request->query('end_date', date('Y-12-31'));
        $asOfDate = $request->query('as_of_date', date('Y-12-31'));

        switch ($type) {
            case 'profit-loss':
                if ($user && ! $user->can('reports.profit_loss') && ! $user->can('reports.view')) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Laporan Laba Rugi.');
                }
                $spreadsheet = $this->excelService->exportProfitLoss($startDate, $endDate, $unitFilter, $user);
                $filename = "Laporan-Laba-Rugi-{$startDate}-sd-{$endDate}.xlsx";
                break;

            case 'balance-sheet':
                if ($user && ! $user->can('reports.balance_sheet') && ! $user->can('reports.view')) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Laporan Neraca.');
                }
                $spreadsheet = $this->excelService->exportBalanceSheet($asOfDate, $unitFilter, $user);
                $filename = "Laporan-Neraca-per-{$asOfDate}.xlsx";
                break;

            case 'trial-balance':
                if ($user && ! $user->can('reports.trial_balance') && ! $user->can('reports.view')) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Neraca Saldo.');
                }
                $spreadsheet = $this->excelService->exportTrialBalance($startDate, $endDate, $unitFilter, $user);
                $filename = "Neraca-Saldo-{$startDate}-sd-{$endDate}.xlsx";
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
                $spreadsheet = $this->excelService->exportGeneralLedger($accountId, $startDate, $endDate, $unitFilter, $user);
                $filename = "Buku-Besar-Akun-{$accountId}-{$startDate}-sd-{$endDate}.xlsx";
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
                $spreadsheet = $this->excelService->exportSubsidiaryLedger($accountId, $startDate, $endDate, $unitFilter, $user);
                $filename = "Buku-Besar-Pembantu-Akun-{$accountId}-{$startDate}-sd-{$endDate}.xlsx";
                break;

            case 'cash-flow':
                if ($user && ! $user->can('reports.cash_flow') && ! $user->can('reports.view')) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Laporan Arus Kas.');
                }
                $spreadsheet = $this->excelService->exportCashFlow($startDate, $endDate, $unitFilter, $user);
                $filename = "Laporan-Arus-Kas-{$startDate}-sd-{$endDate}.xlsx";
                break;

            case 'changes-in-equity':
                if ($user && ! $user->can('reports.changes_in_equity') && ! $user->can('reports.view')) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Laporan Perubahan Ekuitas.');
                }
                $spreadsheet = $this->excelService->exportChangesInEquity($startDate, $endDate, $unitFilter, $user);
                $filename = "Laporan-Perubahan-Ekuitas-{$startDate}-sd-{$endDate}.xlsx";
                break;

            case 'worksheet':
                if ($user && ! $user->can('reports.worksheet') && ! $user->can('reports.view')) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Neraca Lajur (Worksheet).');
                }
                $spreadsheet = $this->excelService->exportWorksheet($startDate, $endDate, $unitFilter, $user);
                $filename = "Neraca-Lajur-Worksheet-{$startDate}-sd-{$endDate}.xlsx";
                break;

            case 'opening-balance':
                if ($user && ! $user->can('reports.opening_balance') && ! $user->can('reports.view')) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Saldo Awal.');
                }
                $periodId = $request->has('period_id') ? (int) $request->query('period_id') : null;
                $mode = $request->query('mode', 'balance_sheet');
                $spreadsheet = $this->excelService->exportOpeningBalance($periodId, $unitFilter, $user, $mode);
                $filename = 'Laporan-Saldo-Awal'.($periodId ? "-Periode-{$periodId}" : '')."-{$mode}.xlsx";
                break;

            case 'aging-receivable':
            case 'aging-payable':
                if ($user && ! $user->can('reports.view')) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Laporan Aging.');
                }
                $agingType = $type === 'aging-receivable' ? 'receivable' : 'payable';
                $spreadsheet = $this->excelService->exportAging($agingType, $asOfDate, $unitFilter, $user);
                $label = $agingType === 'receivable' ? 'Piutang' : 'Hutang';
                $filename = "Laporan-Aging-{$label}-per-{$asOfDate}.xlsx";
                break;

            default:
                abort(404, "Jenis laporan '{$type}' tidak ditemukan.");
        }

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
