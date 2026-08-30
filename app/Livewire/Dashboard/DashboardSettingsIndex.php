<?php

namespace App\Livewire\Dashboard;

use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\Company;
use App\Models\DashboardKpi;
use App\Models\DashboardSetting;
use App\Services\AuditLogService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Pengaturan Dashboard - ArtaLedger')]
class DashboardSettingsIndex extends Component
{
    public ?Company $company = null;

    public ?DashboardSetting $setting = null;

    // Dashboard Settings Form State
    public bool $show_kpi_cards = true;

    public bool $show_revenue_expense_chart = true;

    public bool $show_recent_journals = true;

    public bool $show_quick_actions = true;

    public bool $show_period_status = true;

    public bool $show_cash_bank_summary = true;

    public string $chart_type = 'bar';

    public int $recent_journals_count = 5;

    // KPI Card CRUD Modal State
    public bool $showKpiModal = false;

    public ?int $editingKpiId = null;

    public string $kpi_title = '';

    public string $kpi_source_type = 'account';

    public ?int $kpi_account_id = null;

    public ?int $kpi_account_group_id = null;

    public string $kpi_account_type = 'asset';

    public string $kpi_calculation_type = 'ending_balance';

    public string $kpi_formula_expression = '';

    public string $kpi_display_format = 'currency';

    public int $kpi_decimal_places = 0;

    public string $kpi_color_theme = 'indigo';

    public string $kpi_icon = 'wallet';

    public int $kpi_order_index = 0;

    public bool $kpi_is_active = true;

    public function mount(): void
    {
        if (auth()->check() && ! auth()->user()->can('dashboard.settings') && ! auth()->user()->can('settings.manage') && ! auth()->user()->hasRole('Super Admin')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
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

        $this->loadSettingState();
        $this->ensureDefaultKpisExist();
    }

    private function loadSettingState(): void
    {
        $this->show_kpi_cards = $this->setting->show_kpi_cards;
        $this->show_revenue_expense_chart = $this->setting->show_revenue_expense_chart;
        $this->show_recent_journals = $this->setting->show_recent_journals;
        $this->show_quick_actions = $this->setting->show_quick_actions;
        $this->show_period_status = $this->setting->show_period_status;
        $this->show_cash_bank_summary = $this->setting->show_cash_bank_summary;
        $this->chart_type = $this->setting->chart_type;
        $this->recent_journals_count = $this->setting->recent_journals_count;
    }

    private function ensureDefaultKpisExist(): void
    {
        if (DashboardKpi::where('company_id', $this->company->id)->count() === 0) {
            DashboardKpi::create([
                'company_id' => $this->company->id,
                'title' => 'Total Pendapatan Usaha',
                'source_type' => 'account_type',
                'account_type' => 'revenue',
                'calculation_type' => 'ending_balance',
                'color_theme' => 'emerald',
                'icon' => 'trending-up',
                'order_index' => 1,
                'is_active' => true,
            ]);

            DashboardKpi::create([
                'company_id' => $this->company->id,
                'title' => 'Total Beban Operasional',
                'source_type' => 'account_type',
                'account_type' => 'expense',
                'calculation_type' => 'ending_balance',
                'color_theme' => 'rose',
                'icon' => 'credit-card',
                'order_index' => 2,
                'is_active' => true,
            ]);

            DashboardKpi::create([
                'company_id' => $this->company->id,
                'title' => 'Total Aktiva / Aset Perusahaan',
                'source_type' => 'account_type',
                'account_type' => 'asset',
                'calculation_type' => 'ending_balance',
                'color_theme' => 'indigo',
                'icon' => 'wallet',
                'order_index' => 3,
                'is_active' => true,
            ]);
        }
    }

    public function saveSettings()
    {
        if (auth()->check() && ! auth()->user()->can('dashboard.settings') && ! auth()->user()->can('settings.manage') && ! auth()->user()->hasRole('Super Admin')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        $this->setting->update([
            'show_kpi_cards' => $this->show_kpi_cards,
            'show_revenue_expense_chart' => $this->show_revenue_expense_chart,
            'show_recent_journals' => $this->show_recent_journals,
            'show_quick_actions' => $this->show_quick_actions,
            'show_period_status' => $this->show_period_status,
            'show_cash_bank_summary' => $this->show_cash_bank_summary,
            'chart_type' => $this->chart_type,
            'recent_journals_count' => $this->recent_journals_count,
        ]);

        AuditLogService::record(
            'dashboard.settings_updated',
            'Memperbarui Pengaturan Tampilan Dashboard',
            $this->setting
        );

        session()->flash('message', 'Pengaturan tampilan dashboard berhasil diperbarui.');

        return redirect()->route('dashboard.settings.index');
    }

    public function openCreateKpiModal(): void
    {
        $this->resetKpiForm();
        $this->showKpiModal = true;
    }

    public function openEditKpiModal(int $id): void
    {
        $kpi = DashboardKpi::findOrFail($id);
        $this->editingKpiId = $kpi->id;
        $this->kpi_title = $kpi->title;
        $this->kpi_source_type = $kpi->source_type;
        $this->kpi_account_id = $kpi->account_id;
        $this->kpi_account_group_id = $kpi->account_group_id;
        $this->kpi_account_type = $kpi->account_type ?? 'asset';
        $this->kpi_calculation_type = $kpi->calculation_type;
        $this->kpi_formula_expression = $kpi->formula_expression ?? '';
        $this->kpi_display_format = $kpi->display_format ?? 'currency';
        $this->kpi_decimal_places = $kpi->decimal_places ?? 0;
        $this->kpi_color_theme = $kpi->color_theme;
        $this->kpi_icon = $kpi->icon;
        $this->kpi_order_index = $kpi->order_index;
        $this->kpi_is_active = $kpi->is_active;

        $this->showKpiModal = true;
    }

    public function saveKpi(): void
    {
        if (auth()->check() && ! auth()->user()->can('dashboard.kpis.manage') && ! auth()->user()->can('dashboard.settings') && ! auth()->user()->hasRole('Super Admin')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        $this->validate([
            'kpi_title' => 'required|string|max:100',
            'kpi_source_type' => 'required|in:account,account_type,account_group,formula',
            'kpi_account_id' => 'nullable|required_if:kpi_source_type,account|exists:accounts,id',
            'kpi_account_group_id' => 'nullable|required_if:kpi_source_type,account_group|exists:account_groups,id',
            'kpi_account_type' => 'nullable|required_if:kpi_source_type,account_type|string',
            'kpi_formula_expression' => 'nullable|required_if:kpi_source_type,formula|string',
            'kpi_calculation_type' => 'required|in:ending_balance,period_mutation,debit_sum,credit_sum',
            'kpi_display_format' => 'required|in:currency,percentage,days,number,times',
            'kpi_decimal_places' => 'required|integer|min:0|max:4',
            'kpi_color_theme' => 'required|in:indigo,emerald,rose,amber,sky,violet',
            'kpi_icon' => 'required|string',
            'kpi_order_index' => 'required|integer|min:0',
        ]);

        DashboardKpi::updateOrCreate(
            ['id' => $this->editingKpiId],
            [
                'company_id' => $this->company->id,
                'title' => $this->kpi_title,
                'source_type' => $this->kpi_source_type,
                'account_id' => $this->kpi_source_type === 'account' ? $this->kpi_account_id : null,
                'account_group_id' => $this->kpi_source_type === 'account_group' ? $this->kpi_account_group_id : null,
                'account_type' => $this->kpi_source_type === 'account_type' ? $this->kpi_account_type : null,
                'formula_expression' => $this->kpi_source_type === 'formula' ? $this->kpi_formula_expression : null,
                'calculation_type' => $this->kpi_calculation_type,
                'display_format' => $this->kpi_display_format,
                'decimal_places' => $this->kpi_decimal_places,
                'color_theme' => $this->kpi_color_theme,
                'icon' => $this->kpi_icon,
                'order_index' => $this->kpi_order_index,
                'is_active' => $this->kpi_is_active,
            ]
        );

        AuditLogService::record(
            $this->editingKpiId ? 'kpi.updated' : 'kpi.created',
            ($this->editingKpiId ? 'Memperbarui' : 'Membuat').' Kartu KPI Finansial ('.$this->kpi_title.')'
        );

        session()->flash('message', $this->editingKpiId ? 'Kartu KPI berhasil diperbarui.' : 'Kartu KPI baru berhasil ditambahkan.');
        $this->showKpiModal = false;
        $this->resetKpiForm();
    }

    public function toggleKpiActive(int $id): void
    {
        $kpi = DashboardKpi::findOrFail($id);
        $kpi->update(['is_active' => ! $kpi->is_active]);

        session()->flash('message', 'Status Kartu KPI berhasil diubah.');
    }

    public function deleteKpi(int $id): void
    {
        if (auth()->check() && ! auth()->user()->can('dashboard.kpis.manage') && ! auth()->user()->can('dashboard.settings') && ! auth()->user()->hasRole('Super Admin')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        $kpi = DashboardKpi::findOrFail($id);
        $title = $kpi->title;
        $kpi->delete();

        AuditLogService::record('kpi.deleted', 'Menghapus Kartu KPI ('.$title.')');
        session()->flash('message', 'Kartu KPI berhasil dihapus.');
    }

    public function appendFormulaToken(string $token): void
    {
        $this->kpi_formula_expression = trim($this->kpi_formula_expression.' '.$token);
    }

    private function resetKpiForm(): void
    {
        $this->editingKpiId = null;
        $this->kpi_title = '';
        $this->kpi_source_type = 'account';
        $this->kpi_account_id = null;
        $this->kpi_account_group_id = null;
        $this->kpi_account_type = 'asset';
        $this->kpi_calculation_type = 'ending_balance';
        $this->kpi_formula_expression = '';
        $this->kpi_display_format = 'currency';
        $this->kpi_decimal_places = 0;
        $this->kpi_color_theme = 'indigo';
        $this->kpi_icon = 'wallet';
        $this->kpi_order_index = 0;
        $this->kpi_is_active = true;
    }

    public function render()
    {
        $kpis = DashboardKpi::where('company_id', $this->company->id)
            ->with(['account', 'accountGroup'])
            ->orderBy('order_index')
            ->orderBy('id')
            ->get();

        $accounts = Account::where('company_id', $this->company->id)
            ->orderBy('code')
            ->get();

        $accountGroups = AccountGroup::where('company_id', $this->company->id)
            ->orderBy('name')
            ->get();

        return view('livewire.dashboard.dashboard-settings-index', [
            'kpis' => $kpis,
            'accounts' => $accounts,
            'accountGroups' => $accountGroups,
        ]);
    }
}
