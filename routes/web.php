<?php

use App\Livewire\Accounting\Accounts\AccountIndex;
use App\Livewire\Accounting\Journals\AdjustmentForm;
use App\Livewire\Accounting\Journals\AdjustmentIndex;
use App\Livewire\Accounting\Journals\JournalForm;
use App\Livewire\Accounting\Journals\JournalIndex;
use App\Livewire\Accounting\Periods\PeriodIndex;
use App\Livewire\Accounting\Reports\AgingReport;
use App\Livewire\Accounting\Reports\BalanceSheet;
use App\Livewire\Accounting\Reports\CashFlow;
use App\Livewire\Accounting\Reports\ChangesInEquity;
use App\Livewire\Accounting\Reports\GeneralLedger;
use App\Livewire\Accounting\Reports\ProfitLoss;
use App\Livewire\Accounting\Reports\SubsidiaryLedger;
use App\Livewire\Accounting\Reports\TrialBalance;
use App\Livewire\Accounting\Reports\Worksheet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('accounting.accounts.index');
    }

    return redirect()->route('login');
})->name('home');

// Public route: Asset QR Code scan landing page (no auth required)
use App\Http\Controllers\AssetScanController;

Route::get('/a/{code}', [AssetScanController::class, 'show'])->name('assets.scan.public');

use App\Http\Controllers\BankReconciliationPdfController;
use App\Http\Controllers\FinancialReportExcelController;
use App\Http\Controllers\FinancialReportPdfController;
use App\Livewire\Accounting\Accounts\AccountGroupIndex;
use App\Livewire\Accounting\Assets\DepreciationRun;
use App\Livewire\Accounting\Assets\FixedAssetIndex;
use App\Livewire\Accounting\Import\JournalImportWizard;
use App\Livewire\Accounting\Journals\JournalTemplateIndex;
use App\Livewire\Accounting\OpeningBalance\OpeningBalanceIndex;
use App\Livewire\Accounting\Reconciliation\BankReconciliationDetail;
use App\Livewire\Accounting\Reconciliation\BankReconciliationIndex;
use App\Livewire\Accounting\Settings\CompanySettingsIndex;
use App\Livewire\Accounting\Settings\InitialBalanceIndex;
use App\Livewire\Accounting\Settings\JournalTypeIndex;
use App\Livewire\Accounting\Settings\UnitIndex;
use App\Livewire\Admin\AuditLogIndex;
use App\Livewire\Admin\RoleIndex;
use App\Livewire\Admin\UserIndex;
use App\Livewire\Dashboard\DashboardIndex;
use App\Livewire\Dashboard\DashboardSettingsIndex;

Route::middleware(['web', 'auth'])->group(function () {
    // Dashboard & Settings
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');
    Route::get('/dashboard/settings', DashboardSettingsIndex::class)->name('dashboard.settings.index');

    // Admin & RBAC
    Route::get('/admin/roles', RoleIndex::class)->name('admin.roles.index');
    Route::get('/admin/users', UserIndex::class)->name('admin.users.index');
    Route::get('/admin/audit-logs', AuditLogIndex::class)->name('admin.audit-logs.index');

    // Operations
    Route::get('/accounting/accounts', AccountIndex::class)->name('accounting.accounts.index');
    Route::get('/accounting/account-groups', AccountGroupIndex::class)->name('accounting.account-groups.index');
    Route::get('/accounting/journal-types', JournalTypeIndex::class)->name('accounting.journal-types.index');
    Route::get('/accounting/units', UnitIndex::class)->name('accounting.units.index');
    Route::get('/accounting/settings/company', CompanySettingsIndex::class)->name('accounting.settings.company.index');
    Route::get('/accounting/initial-balance', InitialBalanceIndex::class)->name('accounting.initial-balance.index');
    Route::get('/accounting/periods', PeriodIndex::class)->name('accounting.periods.index');
    Route::get('/accounting/opening-balance', OpeningBalanceIndex::class)->name('accounting.opening-balance.index');
    Route::get('/accounting/import', JournalImportWizard::class)->name('accounting.import.index');
    Route::get('/accounting/journals/templates', JournalTemplateIndex::class)->name('accounting.journals.templates.index');
    Route::get('/accounting/journals', JournalIndex::class)->name('accounting.journals.index');
    Route::get('/accounting/journals/create', JournalForm::class)->name('accounting.journals.create');
    Route::get('/accounting/journals/{id}/edit', JournalForm::class)->name('accounting.journals.edit');
    Route::get('/accounting/adjustments', AdjustmentIndex::class)->name('accounting.adjustments.index');
    Route::get('/accounting/adjustments/create', AdjustmentForm::class)->name('accounting.adjustments.create');

    // Bank Reconciliation
    Route::get('/accounting/reconciliation', BankReconciliationIndex::class)->name('accounting.reconciliation.index');
    Route::get('/accounting/reconciliation/{statement}', BankReconciliationDetail::class)->name('accounting.reconciliation.detail');
    Route::get('/accounting/reconciliation/{statement}/pdf', [BankReconciliationPdfController::class, 'export'])->name('accounting.reconciliation.pdf');

    // Fixed Assets & Depreciation
    Route::get('/accounting/fixed-assets', FixedAssetIndex::class)->name('accounting.fixed-assets.index');
    Route::get('/accounting/fixed-assets/depreciation', DepreciationRun::class)->name('accounting.fixed-assets.depreciation');

    // Reports
    Route::get('/accounting/reports/general-ledger', GeneralLedger::class)->name('accounting.reports.general-ledger');
    Route::get('/accounting/reports/subsidiary-ledger', SubsidiaryLedger::class)->name('accounting.reports.subsidiary-ledger');
    Route::get('/accounting/reports/opening-balance', OpeningBalanceIndex::class)->name('accounting.reports.opening-balance');
    Route::get('/accounting/reports/worksheet', Worksheet::class)->name('accounting.reports.worksheet');
    Route::get('/accounting/reports/trial-balance', TrialBalance::class)->name('accounting.reports.trial-balance');
    Route::get('/accounting/reports/profit-loss', ProfitLoss::class)->name('accounting.reports.profit-loss');
    Route::get('/accounting/reports/balance-sheet', BalanceSheet::class)->name('accounting.reports.balance-sheet');
    Route::get('/accounting/reports/cash-flow', CashFlow::class)->name('accounting.reports.cash-flow');
    Route::get('/accounting/reports/changes-in-equity', ChangesInEquity::class)->name('accounting.reports.changes-in-equity');
    Route::get('/accounting/reports/aging', AgingReport::class)->name('accounting.reports.aging');
    Route::get('/accounting/reports/export/pdf/{type}', FinancialReportPdfController::class.'@export')->name('accounting.reports.export.pdf');
    Route::get('/accounting/reports/export/excel/{type}', FinancialReportExcelController::class.'@export')->name('accounting.reports.export.excel');
});

require __DIR__.'/settings.php';
