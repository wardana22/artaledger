<?php

namespace App\Livewire\Accounting\Periods;

use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Periode Akuntansi - ArtaLedger')]
class PeriodIndex extends Component
{
    public int $selectedYear;

    public bool $showFormModal = false;

    public int $year;

    public int $month = 1;

    public bool $showReopenModal = false;

    public ?int $reopenPeriodId = null;

    public string $inputLockKey = '';

    public string $reopenReason = '';

    public ?int $revealedKeyPeriodId = null;

    public function mount(): void
    {
        $this->selectedYear = (int) date('Y');
        $this->year = (int) date('Y');
        $this->month = (int) date('n');
    }

    public function generateYearPeriods(): void
    {
        $company = Company::first();
        if (! $company) {
            return;
        }

        for ($m = 1; $m <= 12; $m++) {
            $startDate = Carbon::create($this->selectedYear, $m, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            AccountingPeriod::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'year' => $this->selectedYear,
                    'month' => $m,
                ],
                [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'status' => 'open',
                ]
            );
        }

        session()->flash('message', "Seluruh periode 12 bulan untuk tahun {$this->selectedYear} berhasil dibuat.");
    }

    public function closePeriod(int $periodId): void
    {
        $period = AccountingPeriod::findOrFail($periodId);

        if ($period->status !== 'open') {
            return;
        }

        $lockKey = sprintf('LOCK-%d%02d-%s', $period->year, $period->month, strtoupper(Str::random(6)));

        $period->update([
            'status' => 'closed',
            'lock_key' => $lockKey,
            'closed_at' => now(),
            'closed_by' => auth()->id(),
        ]);

        AuditLogService::record(
            'period.closed',
            "Menutup Periode Akuntansi {$period->name} (Lock Key Di-generate)",
            $period,
            ['status' => 'open'],
            ['status' => 'closed', 'lock_key' => $lockKey]
        );

        session()->flash('message', "Periode {$period->name} berhasil ditutup. Kunci Keamanan Rahasia (Lock Key) telah dihasilkan secara otomatis.");
    }

    public function lockPeriod(int $periodId): void
    {
        $period = AccountingPeriod::findOrFail($periodId);

        if ($period->status !== 'closed') {
            return;
        }

        $period->update([
            'status' => 'locked',
        ]);

        AuditLogService::record(
            'period.locked',
            "Mengunci Total (LOCKED) Periode Akuntansi {$period->name}",
            $period,
            ['status' => 'closed'],
            ['status' => 'locked']
        );

        session()->flash('message', "Periode {$period->name} diubah menjadi Terkunci Total (LOCKED).");
    }

    public function openReopenModal(int $periodId): void
    {
        $this->resetReopenForm();
        $this->reopenPeriodId = $periodId;
        $this->showReopenModal = true;
    }

    public function confirmReopenPeriod(): void
    {
        $this->validate([
            'inputLockKey' => 'required|string',
            'reopenReason' => 'required|string|min:5',
        ], [
            'inputLockKey.required' => 'Kunci Keamanan Rahasia wajib diisi.',
            'reopenReason.required' => 'Alasan pembukaan kembali wajib diisi.',
            'reopenReason.min' => 'Alasan minimal 5 karakter.',
        ]);

        $period = AccountingPeriod::findOrFail($this->reopenPeriodId);

        if (trim($this->inputLockKey) !== $period->lock_key) {
            $this->addError('inputLockKey', 'Akses Ditolak! Kunci Keamanan Rahasia (Lock Key) tidak cocok / salah.');

            return;
        }

        $oldStatus = $period->status;
        $period->update([
            'status' => 'open',
            'opened_at' => now(),
            'opened_by' => auth()->id(),
            'reopen_reason' => trim($this->reopenReason),
        ]);

        AuditLogService::record(
            'period.reopened',
            "Membuka Kembali Periode Akuntansi {$period->name} dengan Lock Key Valid. Alasan: ".trim($this->reopenReason),
            $period,
            ['status' => $oldStatus],
            ['status' => 'open', 'reopen_reason' => trim($this->reopenReason)]
        );

        session()->flash('message', "Periode {$period->name} berhasil dibuka kembali. Jejak audit pembukaan telah dicatat.");
        $this->showReopenModal = false;
        $this->resetReopenForm();
    }

    public function toggleRevealKey(int $periodId): void
    {
        if ($this->revealedKeyPeriodId === $periodId) {
            $this->revealedKeyPeriodId = null;
        } else {
            $this->revealedKeyPeriodId = $periodId;
        }
    }

    public function resetReopenForm(): void
    {
        $this->reopenPeriodId = null;
        $this->inputLockKey = '';
        $this->reopenReason = '';
        $this->resetValidation();
    }

    public function render()
    {
        $company = Company::first();

        $periods = AccountingPeriod::with(['closedBy', 'openedBy'])
            ->where('company_id', $company?->id ?? 1)
            ->where('year', $this->selectedYear)
            ->orderBy('month')
            ->get();

        $isSuperAdmin = auth()->check() && (
            auth()->user()->hasRole('Super Admin') ||
            (auth()->user()->can('periods.manage_keys'))
        );

        return view('livewire.accounting.periods.index', [
            'periods' => $periods,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }
}
