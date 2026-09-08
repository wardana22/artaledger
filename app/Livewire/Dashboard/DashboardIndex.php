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
#[Title('Dashboard Finansial Eksekutif')]
class DashboardIndex extends Component
{
    public ?Company $company = null;

    public ?DashboardSetting $setting = null;

    public ?int $selectedUnitId = null;

    public string $startDate = '';

    public string $endDate = '';

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

        // Set startDate/endDate dari periode aktif, atau default awal-akhir tahun ini
        $activePeriod = AccountingPeriod::where('company_id', $this->company->id)
            ->where('status', 'open')
            ->orderByDesc('start_date')
            ->first();

        $this->startDate = $activePeriod?->start_date?->format('Y-m-d')
            ?? now()->startOfYear()->format('Y-m-d');

        $this->endDate = $activePeriod?->end_date?->format('Y-m-d')
            ?? now()->format('Y-m-d');
    }

    public function refreshData(DashboardMetricService $metricService): void
    {
        $metricService->syncCustomAccountGroups($this->company->id);
    }

    public function render(DashboardMetricService $metricService)
    {
        // Derive year for monthly trend chart from startDate (Opsi A — 12 bulan penuh)
        $trendYear = (int) date('Y', strtotime($this->startDate ?: now()->format('Y-m-d')));

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
                $value = $metricService->calculateKpiValue($kpi, $this->startDate, $this->endDate, $this->selectedUnitId);
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
        $financialRatios = $metricService->calculateFinancialRatios($this->startDate, $this->endDate, $this->selectedUnitId, $this->company->id);

        // 4. Dynamic Multi-Series Charts (ApexCharts) — 12 bulan penuh tahun dari startDate
        $charts = DashboardChart::where('company_id', $this->company->id)
            ->where('is_visible', true)
            ->orderBy('order')
            ->get();

        $chartData = $metricService->getMonthlyTrend($charts, $trendYear, $this->selectedUnitId, $this->company->id);

        // 5. Top 10 High-Value Transactions
        $topTransactions = $metricService->getTopTransactions($this->startDate, $this->endDate, $this->selectedUnitId, $this->company->id, 10);

        // 6. Active Accounting Period — berdasarkan rentang endDate
        $activePeriod = AccountingPeriod::where('company_id', $this->company->id)
            ->where('start_date', '<=', $this->endDate)
            ->where('end_date', '>=', $this->endDate)
            ->first()
            ?? AccountingPeriod::where('company_id', $this->company->id)->where('status', 'open')->latest('start_date')->first();

        // 7. Recent Journal Entries (Audit Trail)
        $recentJournals = [];
        if ($this->setting->show_recent_journals) {
            $recentJournals = JournalEntry::with(['journalType', 'lines.unit'])
                ->where('entry_number', 'not like', 'SA%')
                ->whereBetween('entry_date', [$this->startDate, $this->endDate])
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
        ]);
    }
}
