<?php

use App\Livewire\Accounting\Accounts\AccountIndex;
use App\Livewire\Accounting\Journals\AdjustmentForm;
use App\Livewire\Accounting\Journals\AdjustmentIndex;
use App\Livewire\Accounting\Journals\JournalForm;
use App\Livewire\Accounting\Journals\JournalIndex;
use App\Livewire\Accounting\Periods\PeriodIndex;
use App\Livewire\Accounting\Reports\BalanceSheet;
use App\Livewire\Accounting\Reports\CashFlow;
use App\Livewire\Accounting\Reports\ChangesInEquity;
use App\Livewire\Accounting\Reports\GeneralLedger;
use App\Livewire\Accounting\Reports\ProfitLoss;
use App\Livewire\Accounting\Reports\SubsidiaryLedger;
use App\Livewire\Accounting\Reports\TrialBalance;
use App\Livewire\Accounting\Reports\Worksheet;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Auto-login & redirect to Master COA directly for easy development access
Route::get('/', function () {
    if (! Auth::check()) {
        $user = User::where('email', 'admin@artaledger.com')->first()
            ?? User::whereHas('roles', fn ($q) => $q->where('name', 'Super Admin'))->first()
            ?? User::first();

        if ($user) {
            Auth::login($user);
        }
    }

    return redirect()->route('accounting.accounts.index');
})->name('home');

use App\Livewire\Accounting\Import\JournalImportWizard;
use App\Livewire\Accounting\Journals\JournalTemplateIndex;
use App\Livewire\Accounting\OpeningBalance\OpeningBalanceIndex;
use App\Livewire\Accounting\Settings\JournalTypeIndex;
use App\Livewire\Accounting\Settings\UnitIndex;
use App\Livewire\Admin\AuditLogIndex;
use App\Livewire\Admin\RoleIndex;
use App\Livewire\Admin\UserIndex;

Route::middleware(['web'])->group(function () {
    // Admin & RBAC
    Route::get('/admin/roles', RoleIndex::class)->name('admin.roles.index');
    Route::get('/admin/users', UserIndex::class)->name('admin.users.index');
    Route::get('/admin/audit-logs', AuditLogIndex::class)->name('admin.audit-logs.index');

    // Operations
    Route::get('/accounting/accounts', AccountIndex::class)->name('accounting.accounts.index');
    Route::get('/accounting/journal-types', JournalTypeIndex::class)->name('accounting.journal-types.index');
    Route::get('/accounting/units', UnitIndex::class)->name('accounting.units.index');
    Route::get('/accounting/periods', PeriodIndex::class)->name('accounting.periods.index');
    Route::get('/accounting/opening-balance', OpeningBalanceIndex::class)->name('accounting.opening-balance.index');
    Route::get('/accounting/import', JournalImportWizard::class)->name('accounting.import.index');
    Route::get('/accounting/journals/templates', JournalTemplateIndex::class)->name('accounting.journals.templates.index');
    Route::get('/accounting/journals', JournalIndex::class)->name('accounting.journals.index');
    Route::get('/accounting/journals/create', JournalForm::class)->name('accounting.journals.create');
    Route::get('/accounting/journals/{id}/edit', JournalForm::class)->name('accounting.journals.edit');
    Route::get('/accounting/adjustments', AdjustmentIndex::class)->name('accounting.adjustments.index');
    Route::get('/accounting/adjustments/create', AdjustmentForm::class)->name('accounting.adjustments.create');

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

    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
