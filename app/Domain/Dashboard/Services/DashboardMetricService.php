<?php

namespace App\Domain\Dashboard\Services;

use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\DashboardChart;
use App\Models\DashboardKpi;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardMetricService
{
    /**
     * In-memory memoization cache for the request cycle.
     */
    protected array $memoizeCache = [];

    /**
     * Calculate financial value for a Dashboard KPI card within a specific date range and unit.
     */
    public function calculateKpiValue(DashboardKpi $kpi, string $startDate, string $endDate, ?int $unitId = null): float
    {
        $cacheKey = "kpi_{$kpi->id}_{$startDate}_{$endDate}_".($unitId ?? 'all');
        if (array_key_exists($cacheKey, $this->memoizeCache)) {
            return $this->memoizeCache[$cacheKey];
        }

        $title = strtolower(trim($kpi->title));

        // 1. Check calibrated standard 12 metrics
        if (str_contains($title, 'pendapatan') && ! str_contains($title, 'beban pokok')) {
            $val = $this->getRevenue($startDate, $endDate, $unitId, $kpi->company_id);
        } elseif (str_contains($title, 'beban pokok') || str_contains($title, 'cogs') && ! str_contains($title, 'to sales')) {
            $val = $this->getCogs($startDate, $endDate, $unitId, $kpi->company_id);
        } elseif (str_contains($title, 'laba bersih')) {
            $val = $this->getNetProfit($startDate, $endDate, $unitId, $kpi->company_id);
        } elseif (str_contains($title, 'ebitda')) {
            $val = $this->getEbitda($startDate, $endDate, $unitId, $kpi->company_id);
        } elseif (str_contains($title, 'sga to sales')) {
            $sga = $this->getSga($startDate, $endDate, $unitId, $kpi->company_id);
            $rev = $this->getRevenue($startDate, $endDate, $unitId, $kpi->company_id);
            $val = $rev > 0 ? ($sga / $rev) * 100 : 0.0;
        } elseif (str_contains($title, 'cogs to sales')) {
            $cogs = $this->getCogs($startDate, $endDate, $unitId, $kpi->company_id);
            $rev = $this->getRevenue($startDate, $endDate, $unitId, $kpi->company_id);
            $val = $rev > 0 ? ($cogs / $rev) * 100 : 0.0;
        } elseif (str_contains($title, 'laba operasional')) {
            $rev = $this->getRevenue($startDate, $endDate, $unitId, $kpi->company_id);
            $cogs = $this->getCogs($startDate, $endDate, $unitId, $kpi->company_id);
            $sga = $this->getSga($startDate, $endDate, $unitId, $kpi->company_id);
            $val = $rev - ($cogs + $sga);
        } elseif (str_contains($title, 'beban admin') || str_contains($title, 'sga')) {
            $val = $this->getSga($startDate, $endDate, $unitId, $kpi->company_id);
        } elseif (str_contains($title, 'sebelum pajak') || str_contains($title, 'ebt')) {
            $net = $this->getNetProfit($startDate, $endDate, $unitId, $kpi->company_id);
            $tax = $this->getTaxExpense($startDate, $endDate, $unitId, $kpi->company_id);
            $val = $net + $tax;
        } elseif (str_contains($title, 'net profit margin') || str_contains($title, 'npm')) {
            $net = $this->getNetProfit($startDate, $endDate, $unitId, $kpi->company_id);
            $rev = $this->getRevenue($startDate, $endDate, $unitId, $kpi->company_id);
            $val = $rev > 0 ? ($net / $rev) * 100 : 0.0;
        } elseif (str_contains($title, 'ito') || str_contains($title, 'inventory turnover')) {
            $invCogs = $this->getInventoryCogs($startDate, $endDate, $unitId, $kpi->company_id);
            $invBalance = $this->getInventoryBalance($endDate, $unitId, $kpi->company_id);
            $val = $invBalance > 0 ? ($invCogs / $invBalance) : 0.0;
        } elseif (str_contains($title, 'dsi') || str_contains($title, 'days sales of inventory')) {
            $invCogs = $this->getInventoryCogs($startDate, $endDate, $unitId, $kpi->company_id);
            $invBalance = $this->getInventoryBalance($endDate, $unitId, $kpi->company_id);
            $periodDays = max(1, (int) (Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1));
            $val = $invCogs > 0 ? ($invBalance / $invCogs) * $periodDays : 0.0;
        } elseif ($kpi->source_type === 'formula') {
            $val = $kpi->evaluateDateRangeFormula($startDate, $endDate, $unitId);
        } elseif ($kpi->source_type === 'account_group' && $kpi->account_group_id) {
            $val = $this->getGroupValue($kpi->account_group_id, $startDate, $endDate, $unitId);
        } else {
            $val = $kpi->calculateValue($unitId, (int) Carbon::parse($endDate)->format('m'), (int) Carbon::parse($endDate)->format('Y'));
        }

        return $this->memoizeCache[$cacheKey] = round($val, 2);
    }

    /**
     * Get Total Revenue (Akun Awalan 4).
     */
    public function getRevenue(string $startDate, string $endDate, ?int $unitId = null, ?int $companyId = null): float
    {
        $accounts = Account::where(function ($q) {
            $q->where('code', 'like', '4%')
                ->orWhereIn('type', ['PENDAPATAN', 'revenue']);
        })
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->pluck('id')->toArray();

        if (empty($accounts)) {
            return 0.0;
        }

        $credit = (float) $this->buildJournalLinesQuery($accounts, $startDate, $endDate, $unitId)->sum('journal_lines.credit');
        $debit = (float) $this->buildJournalLinesQuery($accounts, $startDate, $endDate, $unitId)->sum('journal_lines.debit');

        return max(0, $credit - $debit);
    }

    /**
     * Get Total COGS (Akun Awalan 5).
     */
    public function getCogs(string $startDate, string $endDate, ?int $unitId = null, ?int $companyId = null): float
    {
        $accounts = Account::where(function ($q) {
            $q->where('code', 'like', '5%')
                ->orWhereIn('type', ['HPP', 'BEBAN POKOK PENDAPATAN']);
        })
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->pluck('id')->toArray();

        if (empty($accounts)) {
            return 0.0;
        }

        $debit = (float) $this->buildJournalLinesQuery($accounts, $startDate, $endDate, $unitId)->sum('journal_lines.debit');
        $credit = (float) $this->buildJournalLinesQuery($accounts, $startDate, $endDate, $unitId)->sum('journal_lines.credit');

        return max(0, $debit - $credit);
    }

    /**
     * Get Total SGA / General Admin Expenses (Akun Awalan 6).
     */
    public function getSga(string $startDate, string $endDate, ?int $unitId = null, ?int $companyId = null): float
    {
        $accounts = Account::where(function ($q) {
            $q->where('code', 'like', '6%')
                ->orWhereIn('type', ['BEBAN', 'BEBAN ADMINISTRASI & UMUM']);
        })
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->pluck('id')->toArray();

        if (empty($accounts)) {
            return 0.0;
        }

        $debit = (float) $this->buildJournalLinesQuery($accounts, $startDate, $endDate, $unitId)->sum('journal_lines.debit');
        $credit = (float) $this->buildJournalLinesQuery($accounts, $startDate, $endDate, $unitId)->sum('journal_lines.credit');

        return max(0, $debit - $credit);
    }

    /**
     * Get Net Profit (Pendapatan - Seluruh Beban 5, 6, 7, 8, 9).
     */
    public function getNetProfit(string $startDate, string $endDate, ?int $unitId = null, ?int $companyId = null): float
    {
        $revenue = $this->getRevenue($startDate, $endDate, $unitId, $companyId);

        $expenseAccounts = Account::where(function ($q) {
            $q->where('code', 'like', '5%')
                ->orWhere('code', 'like', '6%')
                ->orWhere('code', 'like', '7%')
                ->orWhere('code', 'like', '8%')
                ->orWhere('code', 'like', '9%')
                ->orWhereIn('type', ['BEBAN', 'BEBAN LAIN-LAIN', 'HPP']);
        })
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->pluck('id')->toArray();

        $debit = (float) $this->buildJournalLinesQuery($expenseAccounts, $startDate, $endDate, $unitId)->sum('journal_lines.debit');
        $credit = (float) $this->buildJournalLinesQuery($expenseAccounts, $startDate, $endDate, $unitId)->sum('journal_lines.credit');
        $totalExpense = $debit - $credit;

        return $revenue - $totalExpense;
    }

    /**
     * Get EBITDA (Net Profit + Pajak + Bunga + Penyusutan).
     */
    public function getEbitda(string $startDate, string $endDate, ?int $unitId = null, ?int $companyId = null): float
    {
        $net = $this->getNetProfit($startDate, $endDate, $unitId, $companyId);

        // Adjustments: Pajak 9, Bunga 80, Penyusutan 12.10, 58, 68
        $adjAccounts = Account::where(function ($q) {
            $q->where('code', 'like', '9%')
                ->orWhere('code', 'like', '80%')
                ->orWhere('code', 'like', '12.10%')
                ->orWhere('code', 'like', '58%')
                ->orWhere('code', 'like', '68%')
                ->orWhere('code', 'like', '70%');
        })
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->pluck('id')->toArray();

        $debit = (float) $this->buildJournalLinesQuery($adjAccounts, $startDate, $endDate, $unitId)->sum('journal_lines.debit');
        $credit = (float) $this->buildJournalLinesQuery($adjAccounts, $startDate, $endDate, $unitId)->sum('journal_lines.credit');
        $adjustments = abs($debit - $credit);

        return $net + $adjustments;
    }

    /**
     * Get Tax Expense (Akun 9%).
     */
    public function getTaxExpense(string $startDate, string $endDate, ?int $unitId = null, ?int $companyId = null): float
    {
        $taxAccounts = Account::where('code', 'like', '9%')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->pluck('id')->toArray();

        if (empty($taxAccounts)) {
            return 0.0;
        }

        $debit = (float) $this->buildJournalLinesQuery($taxAccounts, $startDate, $endDate, $unitId)->sum('journal_lines.debit');
        $credit = (float) $this->buildJournalLinesQuery($taxAccounts, $startDate, $endDate, $unitId)->sum('journal_lines.credit');

        return abs($debit - $credit);
    }

    /**
     * Get Inventory COGS (Akun 52% atau pemakaian obat BHP).
     */
    public function getInventoryCogs(string $startDate, string $endDate, ?int $unitId = null, ?int $companyId = null): float
    {
        $accounts = Account::where(function ($q) {
            $q->where('code', 'like', '52%')
                ->orWhere('name', 'like', '%obat%')
                ->orWhere('name', 'like', '%bhp%');
        })
            ->where('code', 'like', '5%')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->pluck('id')->toArray();

        if (empty($accounts)) {
            return $this->getCogs($startDate, $endDate, $unitId, $companyId);
        }

        $debit = (float) $this->buildJournalLinesQuery($accounts, $startDate, $endDate, $unitId)->sum('journal_lines.debit');
        $credit = (float) $this->buildJournalLinesQuery($accounts, $startDate, $endDate, $unitId)->sum('journal_lines.credit');

        return max(0, $debit - $credit);
    }

    /**
     * Get Ending Balance of Inventory (Akun 11.08%).
     */
    public function getInventoryBalance(string $endDate, ?int $unitId = null, ?int $companyId = null): float
    {
        $accounts = Account::where('code', 'like', '11.08%')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->pluck('id')->toArray();

        if (empty($accounts)) {
            return 0.0;
        }

        $masterOpening = (float) Account::whereIn('id', $accounts)->sum('opening_balance');

        $query = DB::table('journal_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->whereIn('journal_lines.account_id', $accounts)
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.entry_date', '<=', $endDate);

        if ($unitId) {
            $query->where('journal_lines.unit_id', $unitId);
        }

        $mutation = (float) $query->sum(DB::raw('journal_lines.debit - journal_lines.credit'));

        return max(0, $masterOpening + $mutation);
    }

    /**
     * Calculate 3 Key Executive Financial Ratios.
     */
    public function calculateFinancialRatios(string $startDate, string $endDate, ?int $unitId = null, ?int $companyId = null): array
    {
        // 1. Current Ratio = Current Assets (11) / Current Liabilities (21)
        $caAccounts = Account::where('code', 'like', '11%')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->pluck('id')->toArray();

        $clAccounts = Account::where('code', 'like', '21%')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->pluck('id')->toArray();

        $caBalance = $this->calculateCumulativeBalance($caAccounts, $endDate, $unitId, true);
        $clBalance = $this->calculateCumulativeBalance($clAccounts, $endDate, $unitId, false);

        $currentRatio = $clBalance > 0 ? ($caBalance / $clBalance) : 0.0;

        // 2. Net Profit Margin = Net Profit / Revenue * 100
        $netProfit = $this->getNetProfit($startDate, $endDate, $unitId, $companyId);
        $revenue = $this->getRevenue($startDate, $endDate, $unitId, $companyId);
        $npm = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0.0;

        // 3. Debt to Equity Ratio = Total Liabilities (2) / Total Equity (3) * 100
        $liabAccounts = Account::where('code', 'like', '2%')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->pluck('id')->toArray();

        $equityAccounts = Account::where('code', 'like', '3%')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->pluck('id')->toArray();

        $totalLiab = $this->calculateCumulativeBalance($liabAccounts, $endDate, $unitId, false);
        $totalEquity = $this->calculateCumulativeBalance($equityAccounts, $endDate, $unitId, false);

        $der = $totalEquity > 0 ? ($totalLiab / $totalEquity) * 100 : 0.0;

        return [
            'current_ratio' => round($currentRatio, 2),
            'net_profit_margin' => round($npm, 2),
            'debt_to_equity' => round($der, 2),
            'current_assets' => $caBalance,
            'current_liabilities' => $clBalance,
            'total_liabilities' => $totalLiab,
            'total_equity' => $totalEquity,
        ];
    }

    /**
     * Get Top 10 High-Value Transactions in date range.
     */
    public function getTopTransactions(string $startDate, string $endDate, ?int $unitId = null, ?int $companyId = null, int $limit = 10): Collection
    {
        $query = JournalEntry::with(['journalType', 'lines.account', 'lines.unit'])
            ->where('status', 'posted')
            ->where('entry_number', 'not like', 'SA%')
            ->whereBetween('entry_date', [$startDate, $endDate]);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($unitId) {
            $query->whereHas('lines', fn ($q) => $q->where('unit_id', $unitId));
        }

        return $query->withSum('lines as total_amount', 'debit')
            ->orderByDesc('total_amount')
            ->limit($limit)
            ->get();
    }

    /**
     * Generate Monthly Multi-Series Trend Data for ApexCharts.
     */
    public function getMonthlyTrend(Collection $charts, int $year, ?int $unitId = null, ?int $companyId = null): array
    {
        $chartData = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $kpiMap = DashboardKpi::where('company_id', $companyId ?? 1)->get()->keyBy('id');

        foreach ($charts as $chart) {
            $series = [];
            $metricIds = $chart->metric_ids ?? [];

            if (empty($metricIds)) {
                // Default series if none configured
                $metricIds = $kpiMap->take(3)->pluck('id')->toArray();
            }

            foreach ($metricIds as $mid) {
                $kpi = $kpiMap->get($mid);
                if (! $kpi) {
                    continue;
                }

                $dataPoints = [];
                for ($m = 1; $m <= 12; $m++) {
                    $mStart = sprintf('%04d-%02d-01', $year, $m);
                    $mEnd = date('Y-m-t', strtotime($mStart));

                    $dataPoints[] = (float) $this->calculateKpiValue($kpi, $mStart, $mEnd, $unitId);
                }

                $color = match ($kpi->color_theme) {
                    'emerald' => '#10b981',
                    'cyan' => '#06b6d4',
                    'indigo' => '#6366f1',
                    'purple' => '#a855f7',
                    'rose' => '#f43f5e',
                    'amber' => '#f59e0b',
                    'orange' => '#f97316',
                    'teal' => '#14b8a6',
                    'yellow' => '#eab308',
                    default => '#38bdf8',
                };

                $series[] = [
                    'name' => $kpi->title,
                    'data' => $dataPoints,
                    'color' => $color,
                ];
            }

            $chartData[$chart->id] = [
                'categories' => $months,
                'series' => $series,
            ];
        }

        return $chartData;
    }

    /**
     * Synchronize and populate custom account groups for dashboard.
     */
    public function syncCustomAccountGroups(int $companyId = 1): void
    {
        $groupsDef = [
            [
                'code' => 'DASH_COGS',
                'name' => 'Beban Pokok Pendapatan (COGS)',
                'description' => 'Grup akun beban pokok dan biaya operasional langsung layanan medis',
                'color_theme' => 'amber',
                'prefixes' => ['5'],
            ],
            [
                'code' => 'DASH_SGA',
                'name' => 'Beban Administrasi Umum (SGA)',
                'description' => 'Grup akun beban kantor pusat, operasional umum, dan manajemen',
                'color_theme' => 'rose',
                'prefixes' => ['6'],
            ],
            [
                'code' => 'DASH_EBITDA_ADJ',
                'name' => 'Penyesuaian EBITDA',
                'description' => 'Akun non-cash dan finansial (Pajak, Bunga, Amortisasi, Penyusutan)',
                'color_theme' => 'indigo',
                'prefixes' => ['9', '80', '12.10', '58', '68', '70'],
            ],
            [
                'code' => 'DASH_TAX',
                'name' => 'Beban Pajak Penghasilan',
                'description' => 'Seluruh akun beban pajak PPh pasal 29 dan pajak tangguhan',
                'color_theme' => 'orange',
                'prefixes' => ['9'],
            ],
            [
                'code' => 'DASH_INVENTORY',
                'name' => 'Persediaan Obat & BHP',
                'description' => 'Akun persediaan farmasi, obat-obatan, dan barang habis pakai',
                'color_theme' => 'emerald',
                'prefixes' => ['11.08'],
            ],
            [
                'code' => 'DASH_COGS_INV',
                'name' => 'Beban Pemakaian Obat & BHP',
                'description' => 'Beban pokok pemakaian obat dan BHP medis',
                'color_theme' => 'purple',
                'prefixes' => ['52', '51.02'],
            ],
        ];

        foreach ($groupsDef as $def) {
            $group = AccountGroup::firstOrCreate(
                ['company_id' => $companyId, 'code' => $def['code']],
                [
                    'name' => $def['name'],
                    'description' => $def['description'],
                    'color_theme' => $def['color_theme'],
                    'is_system' => true,
                ]
            );

            // Populate members
            foreach ($def['prefixes'] as $prefix) {
                $accounts = Account::where('company_id', $companyId)
                    ->where('code', 'like', $prefix.'%')
                    ->where('is_group', false)
                    ->get();

                foreach ($accounts as $acc) {
                    DB::table('account_group_members')->updateOrInsert(
                        [
                            'account_group_id' => $group->id,
                            'account_id' => $acc->id,
                        ],
                        [
                            'account_prefix' => $prefix,
                            'account_type' => $acc->type,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }
        }
    }

    /**
     * Seed the official 12 Dashboard KPI cards (Pendapatan to DSI) and default charts.
     */
    public function seedDefaultKpisAndCharts(int $companyId = 1): void
    {
        $this->syncCustomAccountGroups($companyId);

        $defaultKpis = [
            [
                'title' => 'Pendapatan',
                'source_type' => 'account_type',
                'account_type' => 'revenue',
                'calculation_type' => 'period_mutation',
                'color_theme' => 'emerald',
                'display_format' => 'currency',
                'icon' => 'trending-up',
                'order_index' => 1,
            ],
            [
                'title' => 'Beban Pokok Pendapatan (COGS)',
                'source_type' => 'account_type',
                'account_type' => 'cogs',
                'calculation_type' => 'period_mutation',
                'color_theme' => 'amber',
                'display_format' => 'currency',
                'icon' => 'shopping-cart',
                'order_index' => 2,
            ],
            [
                'title' => 'Laba Bersih',
                'source_type' => 'formula',
                'calculation_type' => 'period_mutation',
                'color_theme' => 'cyan',
                'display_format' => 'currency',
                'icon' => 'dollar-sign',
                'order_index' => 3,
            ],
            [
                'title' => 'EBITDA',
                'source_type' => 'formula',
                'calculation_type' => 'period_mutation',
                'color_theme' => 'indigo',
                'display_format' => 'currency',
                'icon' => 'activity',
                'order_index' => 4,
            ],
            [
                'title' => 'SGA to Sales',
                'source_type' => 'formula',
                'calculation_type' => 'period_mutation',
                'color_theme' => 'purple',
                'display_format' => 'percentage',
                'decimal_places' => 2,
                'icon' => 'users',
                'order_index' => 5,
            ],
            [
                'title' => 'COGS to Sales',
                'source_type' => 'formula',
                'calculation_type' => 'period_mutation',
                'color_theme' => 'orange',
                'display_format' => 'percentage',
                'decimal_places' => 2,
                'icon' => 'percent',
                'order_index' => 6,
            ],
            [
                'title' => 'Laba Operasional',
                'source_type' => 'formula',
                'calculation_type' => 'period_mutation',
                'color_theme' => 'teal',
                'display_format' => 'currency',
                'icon' => 'briefcase',
                'order_index' => 7,
            ],
            [
                'title' => 'Beban Admin & Umum (SGA)',
                'source_type' => 'account_type',
                'account_type' => 'expense',
                'calculation_type' => 'period_mutation',
                'color_theme' => 'rose',
                'display_format' => 'currency',
                'icon' => 'building-2',
                'order_index' => 8,
            ],
            [
                'title' => 'Laba Sebelum Pajak (EBT)',
                'source_type' => 'formula',
                'calculation_type' => 'period_mutation',
                'color_theme' => 'blue',
                'display_format' => 'currency',
                'icon' => 'scale',
                'order_index' => 9,
            ],
            [
                'title' => 'Net Profit Margin (NPM)',
                'source_type' => 'formula',
                'calculation_type' => 'period_mutation',
                'color_theme' => 'emerald',
                'display_format' => 'percentage',
                'decimal_places' => 2,
                'icon' => 'pie-chart',
                'order_index' => 10,
            ],
            [
                'title' => 'Inventory Turnover (ITO)',
                'source_type' => 'formula',
                'calculation_type' => 'period_mutation',
                'color_theme' => 'sky',
                'display_format' => 'times',
                'decimal_places' => 2,
                'icon' => 'refresh-cw',
                'order_index' => 11,
            ],
            [
                'title' => 'Days Sales of Inventory (DSI)',
                'source_type' => 'formula',
                'calculation_type' => 'period_mutation',
                'color_theme' => 'yellow',
                'display_format' => 'days',
                'decimal_places' => 1,
                'icon' => 'calendar',
                'order_index' => 12,
            ],
        ];

        $existingKpis = DashboardKpi::where('company_id', $companyId)->get();

        if ($existingKpis->count() < 12) {
            // Re-seed cleanly for standard 12 sequence
            DashboardKpi::where('company_id', $companyId)->delete();
            foreach ($defaultKpis as $item) {
                DashboardKpi::create(array_merge($item, [
                    'company_id' => $companyId,
                    'is_active' => true,
                ]));
            }
        }

        // Seed 2 Default Charts if none exist
        if (DashboardChart::where('company_id', $companyId)->count() === 0) {
            $allKpis = DashboardKpi::where('company_id', $companyId)->orderBy('order_index')->get();
            $revId = $allKpis->firstWhere('title', 'Pendapatan')?->id;
            $cogsId = $allKpis->firstWhere('title', 'Beban Pokok Pendapatan (COGS)')?->id;
            $netId = $allKpis->firstWhere('title', 'Laba Bersih')?->id;
            $opId = $allKpis->firstWhere('title', 'Laba Operasional')?->id;
            $npmId = $allKpis->firstWhere('title', 'Net Profit Margin (NPM)')?->id;
            $sgaSalesId = $allKpis->firstWhere('title', 'SGA to Sales')?->id;
            $cogsSalesId = $allKpis->firstWhere('title', 'COGS to Sales')?->id;

            DashboardChart::create([
                'company_id' => $companyId,
                'name' => 'Tren Kinerja Finansial (12 Bulan)',
                'type' => 'area',
                'width' => 'half',
                'months' => 12,
                'is_visible' => true,
                'order' => 1,
                'metric_ids' => array_values(array_filter([$revId, $cogsId, $opId, $netId])),
            ]);

            DashboardChart::create([
                'company_id' => $companyId,
                'name' => 'Rasio Efisiensi & Margin (%)',
                'type' => 'bar',
                'width' => 'half',
                'months' => 12,
                'is_visible' => true,
                'order' => 2,
                'metric_ids' => array_values(array_filter([$npmId, $sgaSalesId, $cogsSalesId])),
            ]);
        }
    }

    /**
     * Helper to build filtered journal lines query.
     */
    protected function buildJournalLinesQuery(array $accountIds, string $startDate, string $endDate, ?int $unitId = null)
    {
        $query = DB::table('journal_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->whereIn('journal_lines.account_id', $accountIds)
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_number', 'not like', 'SA%')
            ->whereBetween('journal_entries.entry_date', [$startDate, $endDate]);

        if ($unitId) {
            $query->where('journal_lines.unit_id', $unitId);
        }

        return $query;
    }

    /**
     * Helper to calculate cumulative ending balance.
     */
    protected function calculateCumulativeBalance(array $accountIds, string $asOfDate, ?int $unitId = null, bool $isDebitNormal = true): float
    {
        if (empty($accountIds)) {
            return 0.0;
        }

        $masterOpening = (float) Account::whereIn('id', $accountIds)->sum('opening_balance');

        $query = DB::table('journal_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->whereIn('journal_lines.account_id', $accountIds)
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.entry_date', '<=', $asOfDate);

        if ($unitId) {
            $query->where('journal_lines.unit_id', $unitId);
        }

        $debit = (float) (clone $query)->sum('journal_lines.debit');
        $credit = (float) (clone $query)->sum('journal_lines.credit');

        $mutation = $isDebitNormal ? ($debit - $credit) : ($credit - $debit);

        return max(0, $masterOpening + $mutation);
    }

    /**
     * Helper to get group value.
     */
    protected function getGroupValue(int $groupId, string $startDate, string $endDate, ?int $unitId = null): float
    {
        $accountIds = DB::table('account_group_members')
            ->where('account_group_id', $groupId)
            ->pluck('account_id')
            ->filter()
            ->toArray();

        if (empty($accountIds)) {
            return 0.0;
        }

        $debit = (float) $this->buildJournalLinesQuery($accountIds, $startDate, $endDate, $unitId)->sum('journal_lines.debit');
        $credit = (float) $this->buildJournalLinesQuery($accountIds, $startDate, $endDate, $unitId)->sum('journal_lines.credit');

        $firstAcc = Account::find($accountIds[0]);
        $isDebit = $firstAcc ? ($firstAcc->normal_balance === 'debit') : true;

        return $isDebit ? ($debit - $credit) : ($credit - $debit);
    }
}
