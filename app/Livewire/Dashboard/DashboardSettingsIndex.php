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
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Pengaturan Dashboard - ArtaLedger')]
class DashboardSettingsIndex extends Component
{
    public ?Company $company = null;

    #[Url]
    public string $activeTab = 'general'; // 'general', 'account_groups'

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

    // Account Group CRUD Modal State
    public bool $showGroupModal = false;

    public ?int $editingGroupId = null;

    public string $group_code = '';

    public string $group_name = '';

    public string $group_description = '';

    public string $group_color_theme = 'indigo';

    public string $group_member_mode = 'prefix';

    public string $group_account_prefix = '4';

    public string $group_account_type = 'PENDAPATAN';

    public array $group_selected_account_ids = [];

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
        $this->ensureSystemGroupsExist();
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

    private function ensureSystemGroupsExist(): void
    {
        if (AccountGroup::where('company_id', $this->company->id)->count() === 0) {
            $g1 = AccountGroup::create([
                'company_id' => $this->company->id,
                'code' => 'REVENUE',
                'name' => 'Pendapatan Usaha (Sales / Revenue)',
                'description' => 'Seluruh akun pendapatan usaha (Kepala 4)',
                'color_theme' => 'emerald',
                'is_system' => true,
            ]);
            $g1->members()->create(['account_prefix' => '4']);

            $g2 = AccountGroup::create([
                'company_id' => $this->company->id,
                'code' => 'COGS',
                'name' => 'Beban Pokok Pendapatan (HPP / COGS)',
                'description' => 'Seluruh akun beban pokok penjualan (Kepala 5)',
                'color_theme' => 'rose',
                'is_system' => true,
            ]);
            $g2->members()->create(['account_prefix' => '5']);

            $g3 = AccountGroup::create([
                'company_id' => $this->company->id,
                'code' => 'OPEX',
                'name' => 'Beban Operasional & Umum',
                'description' => 'Seluruh beban operasional (Kepala 6 & 7)',
                'color_theme' => 'amber',
                'is_system' => true,
            ]);
            $g3->members()->create(['account_prefix' => '6']);
            $g3->members()->create(['account_prefix' => '7']);
        }
    }

    public function openCreateGroupModal(): void
    {
        $this->resetGroupForm();
        $this->showGroupModal = true;
    }

    public function openEditGroupModal(int $id): void
    {
        $group = AccountGroup::with('members')->findOrFail($id);
        $this->editingGroupId = $group->id;
        $this->group_code = $group->code;
        $this->group_name = $group->name;
        $this->group_description = $group->description ?? '';
        $this->group_color_theme = $group->color_theme;

        $firstMember = $group->members->first();
        if ($firstMember) {
            if ($firstMember->account_prefix) {
                $this->group_member_mode = 'prefix';
                $this->group_account_prefix = $firstMember->account_prefix;
            } elseif ($firstMember->account_type) {
                $this->group_member_mode = 'type';
                $this->group_account_type = $firstMember->account_type;
            } else {
                $this->group_member_mode = 'specific';
                $this->group_selected_account_ids = $group->members->pluck('account_id')->filter()->toArray();
            }
        }

        $this->showGroupModal = true;
    }

    public function saveGroup(): void
    {
        if (auth()->check() && ! auth()->user()->can('accounts.edit') && ! auth()->user()->can('settings.manage') && ! auth()->user()->hasRole('Super Admin')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        $this->validate([
            'group_code' => 'required|string|max:30|alpha_dash',
            'group_name' => 'required|string|max:150',
            'group_description' => 'nullable|string|max:500',
            'group_color_theme' => 'required|in:indigo,emerald,rose,amber,sky,violet',
            'group_member_mode' => 'required|in:prefix,type,specific',
        ]);

        $group = AccountGroup::updateOrCreate(
            ['id' => $this->editingGroupId],
            [
                'company_id' => $this->company->id,
                'code' => strtoupper(trim($this->group_code)),
                'name' => $this->group_name,
                'description' => $this->group_description,
                'color_theme' => $this->group_color_theme,
            ]
        );

        $group->members()->delete();

        if ($this->group_member_mode === 'prefix') {
            $group->members()->create(['account_prefix' => $this->group_account_prefix]);
        } elseif ($this->group_member_mode === 'type') {
            $group->members()->create(['account_type' => $this->group_account_type]);
        } else {
            foreach ($this->group_selected_account_ids as $accId) {
                $group->members()->create(['account_id' => $accId]);
            }
        }

        AuditLogService::record(
            $this->editingGroupId ? 'account_group.updated' : 'account_group.created',
            ($this->editingGroupId ? 'Memperbarui' : 'Membuat').' Grup Akun COA ('.$this->group_name.')',
            $group
        );

        session()->flash('message', $this->editingGroupId ? 'Grup Akun berhasil diperbarui.' : 'Grup Akun baru berhasil ditambahkan.');
        $this->showGroupModal = false;
        $this->resetGroupForm();
    }

    public function deleteGroup(int $id): void
    {
        if (auth()->check() && ! auth()->user()->can('accounts.delete') && ! auth()->user()->can('settings.manage') && ! auth()->user()->hasRole('Super Admin')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        $group = AccountGroup::findOrFail($id);
        if ($group->is_system) {
            session()->flash('error', 'Grup Akun bawaan sistem tidak dapat dihapus.');

            return;
        }

        $name = $group->name;
        $group->delete();

        AuditLogService::record('account_group.deleted', 'Menghapus Grup Akun ('.$name.')');
        session()->flash('message', 'Grup Akun berhasil dihapus.');
    }

    private function resetGroupForm(): void
    {
        $this->editingGroupId = null;
        $this->group_code = '';
        $this->group_name = '';
        $this->group_description = '';
        $this->group_color_theme = 'indigo';
        $this->group_member_mode = 'prefix';
        $this->group_account_prefix = '4';
        $this->group_account_type = 'PENDAPATAN';
        $this->group_selected_account_ids = [];
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

        $groups = AccountGroup::where('company_id', $this->company->id)
            ->with(['members.account'])
            ->orderBy('id')
            ->get();

        return view('livewire.dashboard.dashboard-settings-index', [
            'kpis' => $kpis,
            'accounts' => $accounts,
            'accountGroups' => $accountGroups,
            'groups' => $groups,
        ]);
    }
}
