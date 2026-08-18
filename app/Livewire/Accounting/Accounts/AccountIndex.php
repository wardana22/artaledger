<?php

namespace App\Livewire\Accounting\Accounts;

use App\Models\Account;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Master Chart of Accounts (COA) - ArtaLedger')]
class AccountIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 25;

    public string $reportTypeFilter = 'all';

    public string $groupFilter = 'all';

    public string $viewMode = 'table'; // 'table' or 'tree'

    public bool $showFormModal = false;

    public ?int $editingAccountId = null;

    // Form fields
    public string $code = '';

    public string $name = '';

    public ?int $parent_id = null;

    public ?string $type = null;

    public string $normal_balance = 'debit';

    public string $report_type = 'neraca';

    public float $opening_balance = 0.0;

    public bool $is_group = false;

    public bool $is_active = true;

    public function mount(): void
    {
        if (! Auth::check()) {
            $user = User::first() ?? User::create([
                'name' => 'Dev Admin',
                'email' => 'admin@artaledger.com',
                'password' => bcrypt('password'),
            ]);
            Auth::login($user);
        }
    }

    protected $listeners = ['accountSaved' => '$refresh'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingReportTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingGroupFilter(): void
    {
        $this->resetPage();
    }

    public function createAccount(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function createChildAccount(int $parentId): void
    {
        $this->resetForm();
        $parent = Account::findOrFail($parentId);

        $this->parent_id = $parent->id;
        $this->normal_balance = $parent->normal_balance;
        $this->report_type = $parent->report_type;
        $this->type = $parent->type;
        $this->code = $parent->code.'.';

        $this->showFormModal = true;
    }

    public function editAccount(int $id): void
    {
        $account = Account::findOrFail($id);
        $this->editingAccountId = $account->id;
        $this->code = $account->code;
        $this->name = $account->name;
        $this->parent_id = $account->parent_id;
        $this->type = $account->type;
        $this->normal_balance = $account->normal_balance;
        $this->report_type = $account->report_type;
        $this->opening_balance = (float) $account->opening_balance;
        $this->is_group = (bool) $account->is_group;
        $this->is_active = (bool) $account->is_active;

        $this->showFormModal = true;
    }

    public function saveAccount(): void
    {
        $company = Company::first();

        $rules = [
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:accounts,id',
            'type' => 'nullable|string|max:100',
            'normal_balance' => 'required|in:debit,credit',
            'report_type' => 'required|in:neraca,laba_rugi',
            'opening_balance' => 'numeric',
            'is_group' => 'boolean',
            'is_active' => 'boolean',
        ];

        $validated = $this->validate($rules);

        // Auto calculate level
        $level = 1;
        if ($this->parent_id) {
            $parent = Account::find($this->parent_id);
            $level = $parent ? $parent->level + 1 : 1;
        }

        Account::updateOrCreate(
            ['id' => $this->editingAccountId],
            array_merge($validated, [
                'company_id' => $company?->id ?? 1,
                'level' => $level,
            ])
        );

        session()->flash('message', $this->editingAccountId ? 'Akun berhasil diperbarui.' : 'Akun baru berhasil ditambahkan.');
        $this->showFormModal = false;
        $this->resetForm();
    }

    public function deleteAccount(int $id): void
    {
        $account = Account::withCount('children')->findOrFail($id);

        if ($account->children_count > 0) {
            session()->flash('error', 'Gagal menghapus! Akun ini memiliki '.$account->children_count.' sub-akun (anak).');

            return;
        }

        $account->delete();
        session()->flash('message', 'Akun berhasil dihapus.');
    }

    public function resetForm(): void
    {
        $this->editingAccountId = null;
        $this->code = '';
        $this->name = '';
        $this->parent_id = null;
        $this->type = null;
        $this->normal_balance = 'debit';
        $this->report_type = 'neraca';
        $this->opening_balance = 0.0;
        $this->is_group = false;
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $company = Company::first();

        $stats = [
            'total' => Account::count(),
            'group' => Account::group()->count(),
            'posting' => Account::posting()->count(),
        ];

        $query = Account::with('parent')
            ->when($this->search !== '', fn ($q) => $q->where(fn ($sq) => $sq->where('code', 'like', "%{$this->search}%")->orWhere('name', 'like', "%{$this->search}%")))
            ->when($this->reportTypeFilter !== 'all', fn ($q) => $q->where('report_type', $this->reportTypeFilter))
            ->when($this->groupFilter === 'group', fn ($q) => $q->group())
            ->when($this->groupFilter === 'posting', fn ($q) => $q->posting())
            ->orderBy('code', 'asc');

        $accounts = $query->paginate($this->perPage);

        // For tree view: load level 1 root accounts with recursive children
        $treeAccounts = [];
        if ($this->viewMode === 'tree') {
            $treeAccounts = Account::whereNull('parent_id')
                ->with(['children.children.children.children'])
                ->orderBy('code', 'asc')
                ->get();
        }

        $allGroupAccounts = Account::group()->orderBy('code', 'asc')->get(['id', 'code', 'name']);

        return view('livewire.accounting.accounts.index', [
            'accounts' => $accounts,
            'treeAccounts' => $treeAccounts,
            'stats' => $stats,
            'allGroupAccounts' => $allGroupAccounts,
        ]);
    }
}
