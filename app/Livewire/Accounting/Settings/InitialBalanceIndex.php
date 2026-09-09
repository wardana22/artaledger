<?php

namespace App\Livewire\Accounting\Settings;

use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Unit;
use App\Services\AuditLogService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Saldo Awal Perdana - Master Akuntansi')]
class InitialBalanceIndex extends Component
{
    // Mode status: apakah saldo awal terkunci
    public bool $isLocked = true;

    public ?int $existingEntryId = null;

    public ?string $existingEntryNumber = null;

    public ?string $existingEntryDate = null;

    public int $totalAccountsConfigured = 0;

    // Form parameter header
    public string $entryDate = '2025-01-01';

    public string $documentNumber = 'SALDO-AWAL-PERDANA';

    public string $notes = 'Posting Saldo Awal Perdana Akuntansi';

    public ?int $retainedEarningsAccountId = null;

    public ?int $unitId = null;

    // Filter & UI state
    public string $search = '';

    public string $selectedCategory = 'all';

    // Data baris saldo akun [account_id => ['id' => x, 'code' => y, 'name' => z, 'type' => t, 'normal_balance' => debit/credit, 'amount' => float]]
    public array $balances = [];

    // Modal konfirmasi simpan
    public bool $showConfirmModal = false;

    // Modal Reopen / Buka Kunci Darurat (Wajib Password Super Admin)
    public bool $showUnlockModal = false;

    public string $unlockPassword = '';

    public string $unlockReason = '';

    public function mount(): void
    {
        // Otorisasi: Hak akses master / super admin
        if (auth()->check() && ! auth()->user()->can('settings.manage') && ! auth()->user()->can('accounts.view') && ! auth()->user()->hasRole('Super Admin')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki wewenang mengelola Master Saldo Awal.');
        }

        $defaultUnit = Unit::first();
        $this->unitId = $defaultUnit?->id ?? 1;

        // Cari akun Laba Ditahan default (kode 32.01 atau nama Laba Ditahan)
        $retainedAcc = Account::where('is_group', false)
            ->where(function ($q) {
                $q->where('code', 'like', '32%')
                    ->orWhere('code', '32.01')
                    ->orWhere('name', 'like', '%Laba Ditahan%')
                    ->orWhere('name', 'like', '%Saldo Laba%');
            })
            ->first();

        if ($retainedAcc) {
            $this->retainedEarningsAccountId = $retainedAcc->id;
        }

        $this->loadData();
    }

    public function loadData(): void
    {
        // 1. Periksa apakah sudah ada jurnal saldo awal (SA-... atau opening_balance)
        $companyId = Company::first()?->id ?? 1;
        $existingEntry = JournalEntry::with('lines')
            ->where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('entry_number', 'like', 'SA-%')
                    ->orWhere('entry_type', 'opening_balance')
                    ->orWhere('source_type', 'opening_balance');
            })
            ->latest('id')
            ->first();

        // 2. Ambil seluruh akun COA non-group yang aktif
        $accounts = Account::where('is_group', false)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $existingLinesMap = [];
        if ($existingEntry) {
            $this->existingEntryId = $existingEntry->id;
            $this->existingEntryNumber = $existingEntry->entry_number;
            $this->existingEntryDate = $existingEntry->entry_date?->format('d/m/Y') ?? '01/01/2025';
            $this->entryDate = $existingEntry->entry_date?->format('Y-m-d') ?? '2025-01-01';
            $this->documentNumber = $existingEntry->document_number ?: 'SALDO-AWAL-PERDANA';
            $this->notes = $existingEntry->description ?: 'Posting Saldo Awal Perdana Akuntansi';

            // Baca status kunci langsung dari database (default true jika bernilai null)
            $this->isLocked = $existingEntry->is_locked !== false;

            foreach ($existingEntry->lines as $line) {
                $amt = $line->account?->normal_balance === 'credit'
                    ? ((float) $line->credit - (float) $line->debit)
                    : ((float) $line->debit - (float) $line->credit);

                $existingLinesMap[$line->account_id] = $amt;
            }
        } else {
            $this->isLocked = false;
        }

        $balances = [];
        $activeConfigured = 0;

        foreach ($accounts as $acc) {
            $amount = 0.0;
            if (isset($existingLinesMap[$acc->id])) {
                $amount = (float) $existingLinesMap[$acc->id];
            } elseif ((float) $acc->opening_balance > 0.0) {
                $amount = (float) $acc->opening_balance;
            }

            if (abs($amount) > 0.001) {
                $activeConfigured++;
            }

            $balances[$acc->id] = [
                'id' => $acc->id,
                'code' => $acc->code,
                'name' => $acc->name,
                'type' => $acc->type,
                'normal_balance' => $acc->normal_balance,
                'amount' => $amount,
            ];
        }

        $this->balances = $balances;
        $this->totalAccountsConfigured = $activeConfigured;
    }

    public function setCategory(string $category): void
    {
        $this->selectedCategory = $category;
    }

    /**
     * Hitung total debit, total kredit, dan selisih penyeimbang ke Laba Ditahan.
     */
    public function getCalculationProperty(): array
    {
        $totDebit = 0.0;
        $totCredit = 0.0;

        foreach ($this->balances as $accId => $item) {
            // Jangan hitung akun Laba Ditahan terpilih karena akan dihitung otomatis sebagai penyeimbang
            if ($this->retainedEarningsAccountId && (int) $accId === (int) $this->retainedEarningsAccountId) {
                continue;
            }

            $val = (float) ($item['amount'] ?? 0.0);
            if (abs($val) < 0.0001) {
                continue;
            }

            if ($item['normal_balance'] === 'debit') {
                if ($val >= 0) {
                    $totDebit += $val;
                } else {
                    $totCredit += abs($val);
                }
            } else {
                if ($val >= 0) {
                    $totCredit += $val;
                } else {
                    $totDebit += abs($val);
                }
            }
        }

        // Selisih matematis: Jika Debit > Kredit => Perlu Kredit ke Laba Ditahan (Laba Akumulasi)
        // Jika Kredit > Debit => Perlu Debit ke Laba Ditahan (Rugi Akumulasi)
        $diff = $totDebit - $totCredit;
        $retainedAmount = $diff; // Nilai normal kredit laba ditahan

        $finalDebit = $totDebit;
        $finalCredit = $totCredit;

        if ($retainedAmount > 0) {
            $finalCredit += $retainedAmount;
        } elseif ($retainedAmount < 0) {
            $finalDebit += abs($retainedAmount);
        }

        return [
            'total_debit_raw' => $totDebit,
            'total_credit_raw' => $totCredit,
            'difference' => $diff,
            'retained_earnings_amount' => $retainedAmount,
            'is_balanced' => abs($finalDebit - $finalCredit) < 0.01,
            'final_debit' => $finalDebit,
            'final_credit' => $finalCredit,
        ];
    }

    public function openUnlockModal(): void
    {
        if (! auth()->user()->hasRole('Super Admin')) {
            session()->flash('error', 'Hanya pengguna dengan peran Super Admin yang memiliki otorisasi membuka kunci Saldo Awal Perdana.');

            return;
        }

        $this->unlockPassword = '';
        $this->unlockReason = '';
        $this->resetErrorBag();
        $this->showUnlockModal = true;
    }

    public function closeUnlockModal(): void
    {
        $this->showUnlockModal = false;
        $this->unlockPassword = '';
        $this->unlockReason = '';
    }

    /**
     * Reopen / Buka Kunci Darurat Saldo Awal dengan Password Super Admin.
     */
    public function confirmUnlock(): void
    {
        $this->validate([
            'unlockPassword' => 'required|string',
            'unlockReason' => 'required|string|min:8',
        ], [
            'unlockPassword.required' => 'Password Super Admin wajib diisi.',
            'unlockReason.required' => 'Alasan audit pembukaan kunci wajib diisi.',
            'unlockReason.min' => 'Alasan audit minimal 8 karakter.',
        ]);

        $user = auth()->user();

        // 1. Verifikasi Password Super Admin
        if (! Hash::check($this->unlockPassword, $user->password)) {
            $this->addError('unlockPassword', 'Password Super Admin salah / tidak cocok.');

            return;
        }

        // 2. Buka Kunci dan simpan status ke database
        $this->isLocked = false;
        $this->showUnlockModal = false;

        if ($this->existingEntryId) {
            JournalEntry::where('id', $this->existingEntryId)->update([
                'is_locked' => false,
                'unlocked_at' => now(),
            ]);
        }

        // 3. Catat ke Audit Trail
        AuditLogService::record(
            'initial_balance.unlocked',
            "Membuka Kunci Saldo Awal Perdana ({$this->existingEntryNumber}) dengan alasan: {$this->unlockReason}",
            $user,
            ['locked' => true],
            ['locked' => false, 'reason' => $this->unlockReason]
        );

        session()->flash('message', 'Kunci Saldo Awal berhasil dibuka untuk koreksi (Status tersimpan). Pastikan mengunci kembali setelah selesai.');
    }

    public function lockAgain(): void
    {
        $this->isLocked = true;

        if ($this->existingEntryId) {
            JournalEntry::where('id', $this->existingEntryId)->update([
                'is_locked' => true,
            ]);
        }

        session()->flash('message', 'Saldo Awal Perdana kembali dikunci dengan aman (Status tersimpan).');
    }

    public function openSaveModal(): void
    {
        if ($this->isLocked) {
            session()->flash('error', 'Saldo Awal dalam status terkunci. Buka kunci terlebih dahulu untuk memperbarui.');

            return;
        }

        $this->showConfirmModal = true;
    }

    public function closeSaveModal(): void
    {
        $this->showConfirmModal = false;
    }

    /**
     * Simpan & Posting Saldo Awal Perdana ke JournalEntry & JournalLines dengan Auto-Balance Laba Ditahan.
     */
    public function saveAndPost(): void
    {
        if ($this->isLocked) {
            return;
        }

        $this->validate([
            'entryDate' => 'required|date',
            'documentNumber' => 'required|string|max:50',
            'retainedEarningsAccountId' => 'required|exists:accounts,id',
        ], [
            'entryDate.required' => 'Tanggal saldo awal wajib diisi.',
            'documentNumber.required' => 'Nomor bukti dokumen wajib diisi.',
            'retainedEarningsAccountId.required' => 'Pilih akun Laba Ditahan untuk penyeimbang otomatis.',
        ]);

        $calc = $this->calculation;
        $companyId = Company::first()?->id ?? 1;

        // Cari atau buat periode akuntansi yang mencakup entryDate
        $period = AccountingPeriod::where('company_id', $companyId)
            ->whereDate('start_date', '<=', $this->entryDate)
            ->whereDate('end_date', '>=', $this->entryDate)
            ->first();

        if (! $period) {
            $year = date('Y', strtotime($this->entryDate));
            $month = (int) date('m', strtotime($this->entryDate));
            $period = AccountingPeriod::create([
                'company_id' => $companyId,
                'year' => $year,
                'month' => $month,
                'start_date' => date('Y-m-01', strtotime($this->entryDate)),
                'end_date' => date('Y-m-t', strtotime($this->entryDate)),
                'status' => 'open',
            ]);
        }

        try {
            DB::transaction(function () use ($companyId, $period, $calc) {
                // Idempotency: Jika sudah ada jurnal saldo awal sebelumnya, gunakan atau replace lines-nya
                $entryNumber = $this->existingEntryNumber ?: ('SA-'.date('Y', strtotime($this->entryDate)).'-001');

                $entry = JournalEntry::where('company_id', $companyId)
                    ->where(function ($q) use ($entryNumber) {
                        $q->where('id', $this->existingEntryId)
                            ->orWhere('entry_number', $entryNumber);
                    })
                    ->first();

                if (! $entry) {
                    $entry = JournalEntry::create([
                        'company_id' => $companyId,
                        'period_id' => $period->id,
                        'entry_number' => $entryNumber,
                        'entry_date' => $this->entryDate,
                        'document_number' => $this->documentNumber,
                        'description' => $this->notes,
                        'source_type' => 'opening_balance',
                        'entry_type' => 'opening_balance',
                        'status' => 'posted',
                        'is_locked' => true,
                        'posted_by' => auth()->id() ?? 1,
                        'posted_at' => now(),
                    ]);
                } else {
                    $entry->update([
                        'period_id' => $period->id,
                        'entry_date' => $this->entryDate,
                        'document_number' => $this->documentNumber,
                        'description' => $this->notes,
                        'source_type' => 'opening_balance',
                        'entry_type' => 'opening_balance',
                        'status' => 'posted',
                        'is_locked' => true,
                    ]);

                    // Hapus lines lama untuk digantikan yang baru seimbang
                    $entry->lines()->delete();
                }

                $lineIndex = 1;

                // 1. Simpan baris-baris akun yang diisi saldo
                foreach ($this->balances as $accId => $item) {
                    if ((int) $accId === (int) $this->retainedEarningsAccountId) {
                        continue; // Ditangani via penyeimbang otomatis
                    }

                    $val = (float) ($item['amount'] ?? 0.0);
                    if (abs($val) < 0.0001) {
                        continue;
                    }

                    $debit = 0.0;
                    $credit = 0.0;

                    if ($item['normal_balance'] === 'debit') {
                        if ($val >= 0) {
                            $debit = $val;
                        } else {
                            $credit = abs($val);
                        }
                    } else {
                        if ($val >= 0) {
                            $credit = $val;
                        } else {
                            $debit = abs($val);
                        }
                    }

                    JournalLine::create([
                        'journal_entry_id' => $entry->id,
                        'line_no' => $lineIndex++,
                        'account_id' => $accId,
                        'unit_id' => $this->unitId ?? 1,
                        'description' => 'Saldo Awal Perdana - '.$item['name'],
                        'debit' => $debit,
                        'credit' => $credit,
                    ]);
                }

                // 2. Simpan baris penyeimbang otomatis ke akun Laba Ditahan
                $retainedAmt = (float) $calc['retained_earnings_amount'];
                if (abs($retainedAmt) > 0.0001) {
                    $retainedAcc = Account::find($this->retainedEarningsAccountId);
                    $debitRet = 0.0;
                    $creditRet = 0.0;

                    if ($retainedAmt > 0) {
                        $creditRet = $retainedAmt; // Laba akumulasi
                    } else {
                        $debitRet = abs($retainedAmt); // Rugi akumulasi
                    }

                    JournalLine::create([
                        'journal_entry_id' => $entry->id,
                        'line_no' => $lineIndex++,
                        'account_id' => $this->retainedEarningsAccountId,
                        'unit_id' => $this->unitId ?? 1,
                        'description' => 'Penyeimbang Saldo Awal Otomatis - '.($retainedAcc?->name ?? 'Laba Ditahan'),
                        'debit' => $debitRet,
                        'credit' => $creditRet,
                    ]);
                }

                // Kunci kembali saldo awal secara resmi
                $this->isLocked = true;
                $this->existingEntryId = $entry->id;
                $this->existingEntryNumber = $entry->entry_number;

                AuditLogService::record(
                    'initial_balance.saved',
                    "Menyimpan dan memposting Saldo Awal Perdana ({$entry->entry_number}) dengan penyeimbang otomatis ke Laba Ditahan",
                    $entry,
                    [],
                    ['entry_number' => $entry->entry_number, 'total_debit' => $calc['final_debit'], 'is_locked' => true]
                );
            });

            $this->closeSaveModal();
            $this->loadData();
            session()->flash('message', 'Selamat! Saldo Awal Perdana berhasil diposting seimbang (100% Balance) dan terkunci resmi.');
        } catch (Exception $e) {
            session()->flash('error', 'Gagal memposting Saldo Awal: '.$e->getMessage());
        }
    }

    public function render()
    {
        $filteredBalances = collect($this->balances)->filter(function ($item) {
            // Filter Search
            if (trim($this->search) !== '') {
                $term = strtolower(trim($this->search));
                $matchCode = str_contains(strtolower($item['code']), $term);
                $matchName = str_contains(strtolower($item['name']), $term);
                if (! $matchCode && ! $matchName) {
                    return false;
                }
            }

            // Filter Kategori
            if ($this->selectedCategory !== 'all') {
                $type = strtoupper($item['type']);
                $code = $item['code'];

                return match ($this->selectedCategory) {
                    'kas' => in_array($type, ['KAS', 'BANK']) || str_starts_with($code, '11'),
                    'piutang' => str_starts_with($code, '11.03') || str_contains(strtolower($item['name']), 'piutang'),
                    'aset' => in_array($type, ['ASET_LANCAR', 'ASET_TETAP', 'ASET']) || str_starts_with($code, '1'),
                    'hutang' => in_array($type, ['KEWAJIBAN', 'HUTANG_LANCAR', 'HUTANG_JANGKA_PANJANG', 'LIABILITAS']) || str_starts_with($code, '2'),
                    'modal' => in_array($type, ['EKUITAS', 'MODAL']) || str_starts_with($code, '3'),
                    default => true,
                };
            }

            return true;
        });

        $retainedAccount = $this->retainedEarningsAccountId ? Account::find($this->retainedEarningsAccountId) : null;
        $units = Unit::orderBy('code')->get();

        return view('livewire.accounting.settings.initial-balance-index', [
            'filteredBalances' => $filteredBalances,
            'calc' => $this->calculation,
            'retainedAccount' => $retainedAccount,
            'units' => $units,
        ]);
    }
}
