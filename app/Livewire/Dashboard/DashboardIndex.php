<?php

namespace App\Livewire\Dashboard;

use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\DashboardKpi;
use App\Models\DashboardSetting;
use App\Models\JournalEntry;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard Finansial Eksekutif - ArtaLedger')]
class DashboardIndex extends Component
{
    public ?Company $company = null;

    public ?DashboardSetting $setting = null;

    public ?int $selectedUnitId = null;

    public int $selectedMonth = 0;

    public int $selectedYear = 2026;

    public function mount(): void
    {
        if (auth()->check() && ! auth()->user()->can('dashboard.view') && ! auth()->user()->can('reports.view') && ! auth()->user()->hasRole('Super Admin')) {
            // Fallback for authorized users
        }

        $this->company = Company::firstOrCreate([], [
            'code' => 'ALT',
            'name' => 'PT Arta Ledger',
            'app_name' => 'ArtaLedger',
        ]);

        $this->setting = DashboardSetting::firstOrCreate(
            ['company_id' => $this->company->id],
            [
                'show_kpi_cards' => true,
                'show_revenue_expense_chart' => true,
                'show_recent_journals' => true,
                'show_quick_actions' => true,
                'show_period_status' => true,
                'show_cash_bank_summary' => true,
                'chart_type' => 'bar',
                'recent_journals_count' => 5,
            ]
        );

        $this->selectedYear = (int) date('Y');

        // Multi-Tenant Isolation unit assignment
        if (auth()->check() && ! auth()->user()->hasGlobalUnitAccess()) {
            $userUnitIds = auth()->user()->units->pluck('id')->toArray();
            if (! empty($userUnitIds)) {
                $this->selectedUnitId = $userUnitIds[0];
            }
        }
    }

    public function render()
    {
        $kpiCards = [];
        if ($this->setting->show_kpi_cards) {
            $kpis = DashboardKpi::where('company_id', $this->company->id)
                ->where('is_active', true)
                ->orderBy('order_index')
                ->get();

            foreach ($kpis as $kpi) {
                $value = $kpi->calculateValue($this->selectedUnitId, $this->selectedMonth, $this->selectedYear);
                $kpiCards[] = [
                    'title' => $kpi->title,
                    'value' => $value,
                    'color_theme' => $kpi->color_theme,
                    'icon' => $kpi->icon,
                    'calculation_type' => $kpi->calculation_type,
                ];
            }
        }

        // Active Accounting Period
        $activePeriod = AccountingPeriod::where('company_id', $this->company->id)
            ->where('status', 'open')
            ->first();

        // Cash & Bank Accounts
        $cashBankAccounts = [];
        if ($this->setting->show_cash_bank_summary) {
            $cashAccounts = Account::where('company_id', $this->company->id)
                ->where('type', 'asset')
                ->where(function ($q) {
                    $q->where('code', 'like', '11%')
                        ->orWhere('name', 'like', '%kas%')
                        ->orWhere('name', 'like', '%bank%');
                })
                ->where('is_group', false)
                ->get();

            foreach ($cashAccounts as $acc) {
                $totalDebit = (float) DB::table('journal_lines')
                    ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
                    ->where('journal_lines.account_id', $acc->id)
                    ->where('journal_entries.status', 'posted')
                    ->when($this->selectedUnitId, fn ($q) => $q->where('journal_entries.unit_id', $this->selectedUnitId))
                    ->sum('journal_lines.debit');

                $totalCredit = (float) DB::table('journal_lines')
                    ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
                    ->where('journal_lines.account_id', $acc->id)
                    ->where('journal_entries.status', 'posted')
                    ->when($this->selectedUnitId, fn ($q) => $q->where('journal_entries.unit_id', $this->selectedUnitId))
                    ->sum('journal_lines.credit');

                $cashBankAccounts[] = [
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'balance' => $totalDebit - $totalCredit,
                ];
            }
        }

        // Monthly Revenue vs Expense Data
        $chartMonths = [];
        if ($this->setting->show_revenue_expense_chart) {
            for ($m = 1; $m <= 12; $m++) {
                $monthName = date('M', mktime(0, 0, 0, $m, 1));
                $startDate = sprintf('%04d-%02d-01', $this->selectedYear, $m);
                $endDate = date('Y-m-t', strtotime($startDate));

                $revenue = (float) DB::table('journal_lines')
                    ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
                    ->join('accounts', 'journal_lines.account_id', '=', 'accounts.id')
                    ->where('accounts.company_id', $this->company->id)
                    ->where('accounts.type', 'revenue')
                    ->where('journal_entries.status', 'posted')
                    ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
                    ->when($this->selectedUnitId, fn ($q) => $q->where('journal_lines.unit_id', $this->selectedUnitId))
                    ->sum(DB::raw('journal_lines.credit - journal_lines.debit'));

                $expense = (float) DB::table('journal_lines')
                    ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
                    ->join('accounts', 'journal_lines.account_id', '=', 'accounts.id')
                    ->where('accounts.company_id', $this->company->id)
                    ->where('accounts.type', 'expense')
                    ->where('journal_entries.status', 'posted')
                    ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
                    ->when($this->selectedUnitId, fn ($q) => $q->where('journal_lines.unit_id', $this->selectedUnitId))
                    ->sum(DB::raw('journal_lines.debit - journal_lines.credit'));

                $chartMonths[] = [
                    'month' => $monthName,
                    'revenue' => max(0, $revenue),
                    'expense' => max(0, $expense),
                    'profit' => $revenue - $expense,
                ];
            }
        }

        // Recent Journal Entries
        $recentJournals = [];
        if ($this->setting->show_recent_journals) {
            $query = JournalEntry::with(['journalType', 'lines.unit'])
                ->latest('entry_date')
                ->latest('id');

            if ($this->selectedUnitId) {
                $query->whereHas('lines', fn ($q) => $q->where('unit_id', $this->selectedUnitId));
            }

            $recentJournals = $query->take($this->setting->recent_journals_count)->get();
        }

        $units = Unit::all();

        return view('livewire.dashboard.dashboard-index', [
            'kpiCards' => $kpiCards,
            'activePeriod' => $activePeriod,
            'cashBankAccounts' => $cashBankAccounts,
            'chartMonths' => $chartMonths,
            'recentJournals' => $recentJournals,
            'units' => $units,
        ]);
    }
}
