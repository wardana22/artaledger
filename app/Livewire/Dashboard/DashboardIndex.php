<?php

namespace App\Livewire\Dashboard;

use App\Domain\Dashboard\Services\DashboardMetricService;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\DashboardChart;
use App\Models\DashboardKpi;
use App\Models\DashboardSetting;
use App\Models\JournalEntry;
use App\Models\Unit;
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

    public int $start_month = 1;

    public int $end_month = 2;

    public int $selectedYear = 2025;

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

        // Multi-Tenant Isolation unit assignment
        if (auth()->check() && ! auth()->user()->hasGlobalUnitAccess()) {
            $userUnitIds = auth()->user()->units->pluck('id')->toArray();
            if (! empty($userUnitIds)) {
                $this->selectedUnitId = $userUnitIds[0];
            }
        }
    }

    public function updatedStartMonth(): void
    {
        if ($this->start_month > $this->end_month) {
            $this->end_month = $this->start_month;
        }
    }

    public function updatedEndMonth(): void
    {
        if ($this->end_month < $this->start_month) {
            $this->start_month = $this->end_month;
        }
    }

    public function refreshData(DashboardMetricService $metricService): void
    {
        $metricService->syncCustomAccountGroups($this->company->id);
    }

    public function render(DashboardMetricService $metricService)
    {
        $startDate = sprintf('%04d-%02d-01', $this->selectedYear, $this->start_month);
        $endDate = date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $this->selectedYear, $this->end_month)));

        // 1. Ensure KPIs and default charts are seeded for company
        $existingKpisCount = DashboardKpi::where('company_id', $this->company->id)->count();
        if ($existingKpisCount < 12) {
            $metricService->seedDefaultKpisAndCharts($this->company->id);
        }

        // 2. Fetch 12 active KPI Cards
        $kpiCards = [];
        if ($this->setting->show_kpi_cards) {
            $kpis = DashboardKpi::where('company_id', $this->company->id)
                ->where('is_active', true)
                ->orderBy('order_index')
                ->get();

            foreach ($kpis as $kpi) {
                $value = $metricService->calculateKpiValue($kpi, $startDate, $endDate, $this->selectedUnitId);
                $kpiCards[] = [
                    'id' => $kpi->id,
                    'title' => $kpi->title,
                    'value' => $value,
                    'formatted_value' => $kpi->formatDisplayValue($value),
                    'color_theme' => $kpi->color_theme,
                    'icon' => $kpi->icon,
                    'display_format' => $kpi->display_format,
                ];
            }
        }

        // 3. Financial Ratios
        $financialRatios = $metricService->calculateFinancialRatios($startDate, $endDate, $this->selectedUnitId, $this->company->id);

        // 4. Dynamic Multi-Series Charts (ApexCharts)
        $charts = DashboardChart::where('company_id', $this->company->id)
            ->where('is_visible', true)
            ->orderBy('order')
            ->get();

        $chartData = $metricService->getMonthlyTrend($charts, $this->selectedYear, $this->selectedUnitId, $this->company->id);

        // 5. Top 10 High-Value Transactions
        $topTransactions = $metricService->getTopTransactions($startDate, $endDate, $this->selectedUnitId, $this->company->id, 10);

        // 6. Active Accounting Period
        $activePeriod = AccountingPeriod::where('company_id', $this->company->id)
            ->where('year', $this->selectedYear)
            ->where('month', $this->end_month)
            ->first() ?? AccountingPeriod::where('company_id', $this->company->id)->where('status', 'open')->latest('start_date')->first();

        // 7. Recent Journal Entries (Audit Trail)
        $recentJournals = [];
        if ($this->setting->show_recent_journals) {
            $recentJournals = JournalEntry::with(['journalType', 'lines.unit'])
                ->where('entry_number', 'not like', 'SA%')
                ->whereBetween('entry_date', [$startDate, $endDate])
                ->when($this->selectedUnitId, fn ($q) => $q->whereHas('lines', fn ($lq) => $lq->where('unit_id', $this->selectedUnitId)))
                ->latest('entry_date')
                ->latest('id')
                ->take($this->setting->recent_journals_count)
                ->get();
        }

        $units = Unit::all();

        return view('livewire.dashboard.dashboard-index', [
            'kpiCards' => $kpiCards,
            'financialRatios' => $financialRatios,
            'charts' => $charts,
            'chartData' => $chartData,
            'topTransactions' => $topTransactions,
            'activePeriod' => $activePeriod,
            'recentJournals' => $recentJournals,
            'units' => $units,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}
