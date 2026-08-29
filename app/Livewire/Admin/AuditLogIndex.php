<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Audit Log Aktivitas - ArtaLedger')]
class AuditLogIndex extends Component
{
    use WithPagination;

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'user')]
    public string $userFilter = 'all';

    #[Url(as: 'event')]
    public string $eventFilter = 'all';

    #[Url(as: 'start')]
    public string $startDate = '';

    #[Url(as: 'end')]
    public string $endDate = '';

    public int $perPage = 25;

    public bool $showDetailModal = false;

    public ?AuditLog $selectedAuditLog = null;

    public function viewAuditLog(int $id): void
    {
        $this->selectedAuditLog = AuditLog::with(['user', 'company', 'auditable'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedAuditLog = null;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = AuditLog::with(['user', 'company']);

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('description', 'like', "%{$this->search}%")
                    ->orWhere('event_type', 'like', "%{$this->search}%")
                    ->orWhere('ip_address', 'like', "%{$this->search}%")
                    ->orWhereHas('user', function ($uq) {
                        $uq->where('name', 'like', "%{$this->search}%")
                            ->orWhere('email', 'like', "%{$this->search}%");
                    });
            });
        }

        if ($this->userFilter !== 'all') {
            $query->where('user_id', $this->userFilter);
        }

        if ($this->eventFilter !== 'all') {
            if ($this->eventFilter === 'auth') {
                $query->where('event_type', 'like', 'auth.%');
            } elseif ($this->eventFilter === 'journal') {
                $query->where('event_type', 'like', 'journal.%');
            } elseif ($this->eventFilter === 'period') {
                $query->where('event_type', 'like', 'period.%');
            } else {
                $query->where('event_type', $this->eventFilter);
            }
        }

        if (! empty($this->startDate)) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if (! empty($this->endDate)) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        $logs = $query->orderByDesc('id')->paginate($this->perPage);
        $users = User::orderBy('name')->get();

        return view('livewire.admin.audit-log-index', [
            'logs' => $logs,
            'users' => $users,
        ]);
    }
}
