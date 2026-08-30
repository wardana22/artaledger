<?php

namespace App\Livewire\Accounting\Accounts;

use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\Company;
use App\Services\AuditLogService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Grup Akun COA Kustom - ArtaLedger')]
class AccountGroupIndex extends Component
{
    public ?Company $company = null;

    public string $search = '';

    public bool $showGroupModal = false;

    public ?int $editingGroupId = null;

    public string $code = '';

    public string $name = '';

    public string $description = '';

    public string $color_theme = 'indigo';

    public string $member_mode = 'prefix'; // 'prefix', 'type', 'specific'

    public string $account_prefix = '4';

    public string $account_type = 'PENDAPATAN';

    public array $selectedAccountIds = [];

    public function mount(): void
    {
        if (auth()->check() && ! auth()->user()->can('accounts.view') && ! auth()->user()->can('settings.manage') && ! auth()->user()->hasRole('Super Admin')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        $this->company = Company::firstOrCreate([], [
            'code' => 'ALT',
            'name' => 'PT Arta Ledger',
            'app_name' => 'ArtaLedger',
        ]);

        $this->ensureSystemGroupsExist();
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
        $this->code = $group->code;
        $this->name = $group->name;
        $this->description = $group->description ?? '';
        $this->color_theme = $group->color_theme;

        $firstMember = $group->members->first();
        if ($firstMember) {
            if ($firstMember->account_prefix) {
                $this->member_mode = 'prefix';
                $this->account_prefix = $firstMember->account_prefix;
            } elseif ($firstMember->account_type) {
                $this->member_mode = 'type';
                $this->account_type = $firstMember->account_type;
            } else {
                $this->member_mode = 'specific';
                $this->selectedAccountIds = $group->members->pluck('account_id')->filter()->toArray();
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
            'code' => 'required|string|max:30|alpha_dash',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'color_theme' => 'required|in:indigo,emerald,rose,amber,sky,violet',
            'member_mode' => 'required|in:prefix,type,specific',
        ]);

        $group = AccountGroup::updateOrCreate(
            ['id' => $this->editingGroupId],
            [
                'company_id' => $this->company->id,
                'code' => strtoupper(trim($this->code)),
                'name' => $this->name,
                'description' => $this->description,
                'color_theme' => $this->color_theme,
            ]
        );

        $group->members()->delete();

        if ($this->member_mode === 'prefix') {
            $group->members()->create(['account_prefix' => $this->account_prefix]);
        } elseif ($this->member_mode === 'type') {
            $group->members()->create(['account_type' => $this->account_type]);
        } else {
            foreach ($this->selectedAccountIds as $accId) {
                $group->members()->create(['account_id' => $accId]);
            }
        }

        AuditLogService::record(
            $this->editingGroupId ? 'account_group.updated' : 'account_group.created',
            ($this->editingGroupId ? 'Memperbarui' : 'Membuat').' Grup Akun COA ('.$this->name.')',
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
        $this->code = '';
        $this->name = '';
        $this->description = '';
        $this->color_theme = 'indigo';
        $this->member_mode = 'prefix';
        $this->account_prefix = '4';
        $this->account_type = 'PENDAPATAN';
        $this->selectedAccountIds = [];
    }

    public function render()
    {
        $groups = AccountGroup::where('company_id', $this->company->id)
            ->with(['members.account'])
            ->when(! empty($this->search), fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')->orWhere('code', 'like', '%'.$this->search.'%'))
            ->orderBy('id')
            ->get();

        $accounts = Account::where('company_id', $this->company->id)
            ->orderBy('code')
            ->get();

        return view('livewire.accounting.accounts.account-group-index', [
            'groups' => $groups,
            'accounts' => $accounts,
        ]);
    }
}
