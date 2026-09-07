<?php

namespace App\Domain\Accounting\Services;

use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\CashFlowRow;
use App\Models\Company;
use Database\Seeders\CashFlowRowSeeder;
use Illuminate\Support\Facades\DB;

class CashFlowService
{
    /**
     * Calculate comprehensive Direct Method Cash Flow Statement.
     */
    public function calculateStatement(string $startDate, string $endDate, string $unitFilter = 'all', ?int $companyId = null): array
    {
        $company = $companyId ? Company::find($companyId) : Company::first();
        $compId = $company ? $company->id : 1;

        // 1. Fetch active Cash Flow Rows
        $rows = CashFlowRow::where('company_id', $compId)
            ->where('is_active', true)
            ->orderBy('section')
            ->orderBy('order_index')
            ->get();

        // 2. If no rows exist yet, ensure default rows are seeded
        if ($rows->isEmpty()) {
            $this->seedDefaultRows($compId);
            $rows = CashFlowRow::where('company_id', $compId)
                ->where('is_active', true)
                ->orderBy('section')
                ->orderBy('order_index')
                ->get();
        }

        // 3. Process each row value
        $calculatedRows = $rows->map(function ($row) use ($startDate, $endDate, $unitFilter) {
            $value = $this->evaluateRowValue($row, $startDate, $endDate, $unitFilter);

            return [
                'id' => $row->id,
                'section' => $row->section,
                'label' => $row->label,
                'order_index' => $row->order_index,
                'source_type' => $row->source_type,
                'operator_sign' => $row->operator_sign,
                'raw_value' => $value,
                'display_value' => ($row->operator_sign === '-' && $value > 0) ? -$value : $value,
                'has_breakdown' => true,
            ];
        });

        // 4. Split by section
        $operating = $calculatedRows->where('section', 'operating')->values();
        $investing = $calculatedRows->where('section', 'investing')->values();
        $financing = $calculatedRows->where('section', 'financing')->values();

        // Sum sections
        $totalOperating = $operating->sum('display_value');
        $totalInvesting = $investing->sum('display_value');
        $totalFinancing = $financing->sum('display_value');

        // Net Increase / (Decrease) in Cash
        $netIncrease = $totalOperating + $totalInvesting + $totalFinancing;

        // 5. Calculate Opening Cash & Cash Equivalents (Kas 11.01, Bank 11.02, Deposito 11.03)
        $openingBalance = $this->calculateOpeningCash($startDate, $unitFilter, $compId);

        // Reconciled Closing Balance = Opening + Net Increase
        $closingBalance = $openingBalance + $netIncrease;

        $sections = [
            'operating' => [
                'title' => 'A. ARUS KAS DARI KEGIATAN OPERASI',
                'rows' => $operating->map(function ($r) {
                    return [
                        'id' => $r['id'],
                        'label' => $r['label'],
                        'value' => $r['display_value'],
                        'raw_value' => $r['raw_value'],
                        'order_index' => $r['order_index'],
                        'source_type' => $r['source_type'],
                        'operator_sign' => $r['operator_sign'],
                    ];
                })->all(),
                'total' => $totalOperating,
            ],
            'investing' => [
                'title' => 'B. ARUS KAS UNTUK KEGIATAN INVESTASI',
                'rows' => $investing->map(function ($r) {
                    return [
                        'id' => $r['id'],
                        'label' => $r['label'],
                        'value' => $r['display_value'],
                        'raw_value' => $r['raw_value'],
                        'order_index' => $r['order_index'],
                        'source_type' => $r['source_type'],
                        'operator_sign' => $r['operator_sign'],
                    ];
                })->all(),
                'total' => $totalInvesting,
            ],
            'financing' => [
                'title' => 'C. ARUS KAS PEMBIAYAAN',
                'rows' => $financing->map(function ($r) {
                    return [
                        'id' => $r['id'],
                        'label' => $r['label'],
                        'value' => $r['display_value'],
                        'raw_value' => $r['raw_value'],
                        'order_index' => $r['order_index'],
                        'source_type' => $r['source_type'],
                        'operator_sign' => $r['operator_sign'],
                    ];
                })->all(),
                'total' => $totalFinancing,
            ],
        ];

        return [
            'company' => $company,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'unitFilter' => $unitFilter,
            'sections' => $sections,
            'operating' => $operating,
            'investing' => $investing,
            'financing' => $financing,
            'totalOperating' => $totalOperating,
            'totalInvesting' => $totalInvesting,
            'totalFinancing' => $totalFinancing,
            'netIncrease' => $netIncrease,
            'netCashFlow' => $netIncrease,
            'openingBalance' => $openingBalance,
            'openingCash' => $openingBalance,
            'closingBalance' => $closingBalance,
            'endingCash' => $closingBalance,
        ];
    }

    /**
     * Calculate Opening Cash & Equivalents balance before $startDate.
     */
    public function calculateOpeningCash(string $startDate, string $unitFilter = 'all', int $companyId = 1): float
    {
        $cashAccounts = Account::where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('code', 'like', '11.01%')
                    ->orWhere('code', 'like', '11.02%')
                    ->orWhere('code', 'like', '11.03%')
                    ->orWhere('type', 'KAS')
                    ->orWhere('type', 'BANK');
            })
            ->where('is_group', false)
            ->pluck('id')
            ->toArray();

        if (empty($cashAccounts)) {
            return 0.0;
        }

        // 1. Initial Opening Balance from Account Master
        $masterOpening = (float) Account::whereIn('id', $cashAccounts)->sum('opening_balance');

        // 2. Prior Journals (including SA and any transactions strictly before $startDate)
        $priorQuery = DB::table('journal_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->whereIn('journal_lines.account_id', $cashAccounts)
            ->where('journal_entries.status', 'posted');

        if ($unitFilter !== 'all') {
            $priorQuery->where('journal_lines.unit_id', $unitFilter);
        }

        // When $startDate is 2025-01-01, journals on 2025-01-01 with SA entry_number are opening balance journals
        $saBalance = (float) (clone $priorQuery)
            ->where('journal_entries.entry_number', 'like', 'SA%')
            ->sum(DB::raw('journal_lines.debit - journal_lines.credit'));

        $priorMutations = (float) (clone $priorQuery)
            ->where('journal_entries.entry_number', 'not like', 'SA%')
            ->where('journal_entries.entry_date', '<', $startDate)
            ->sum(DB::raw('journal_lines.debit - journal_lines.credit'));

        // If masterOpening is 0 and SA exists, use SA + prior mutations
        $totalOpening = $masterOpening > 0 ? ($masterOpening + $priorMutations) : ($saBalance + $priorMutations);

        return round($totalOpening, 2);
    }

    /**
     * Evaluate single row value with specific fallback calibrations for official reports.
     */
    public function evaluateRowValue(CashFlowRow $row, string $startDate, string $endDate, string $unitFilter = 'all'): float
    {
        // 1. If formula is defined
        if ($row->source_type === 'formula' && ! empty($row->formula_expression)) {
            return abs($row->evaluateFormula($startDate, $endDate, $unitFilter));
        }

        // 2. Official calibrated default rows:
        $normalizedLabel = strtolower(trim($row->label));

        if (str_contains($normalizedLabel, 'pelanggan')) {
            // [Pendapatan] - [Kenaikan Piutang]
            $revGroup = AccountGroup::where('code', 'CF_REV')->first();
            $arGroup = AccountGroup::where('code', 'CF_AR')->first();
            if ($revGroup && $arGroup) {
                $revVal = $row->calculateGroupValue($revGroup->id, $startDate, $endDate, $unitFilter);
                $arVal = $row->calculateGroupValue($arGroup->id, $startDate, $endDate, $unitFilter);

                return abs($revVal - $arVal);
            }
        } elseif (str_contains($normalizedLabel, 'bunga')) {
            // Debet Beban Administrasi Bank (80.01) + Beban Bunga (80.02)
            $interestAccounts = Account::where(function ($q) {
                $q->where('code', 'like', '80.01%')->orWhere('code', 'like', '80.02%');
            })->pluck('id')->toArray();

            return (float) DB::table('journal_lines')
                ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
                ->whereIn('journal_lines.account_id', $interestAccounts)
                ->where('journal_entries.status', 'posted')
                ->where('journal_entries.entry_number', 'not like', 'SA%')
                ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
                ->when($unitFilter !== 'all', fn ($q) => $q->where('journal_lines.unit_id', $unitFilter))
                ->sum('journal_lines.debit');
        } elseif (str_contains($normalizedLabel, 'pajak')) {
            // Panjar Pajak PPh 25 (11.10.02)
            $taxAccounts = Account::where('code', '11.10.02')->pluck('id')->toArray();

            return (float) DB::table('journal_lines')
                ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
                ->whereIn('journal_lines.account_id', $taxAccounts)
                ->where('journal_entries.status', 'posted')
                ->where('journal_entries.entry_number', 'not like', 'SA%')
                ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
                ->when($unitFilter !== 'all', fn ($q) => $q->where('journal_lines.unit_id', $unitFilter))
                ->sum('journal_lines.debit');
        } elseif (str_contains($normalizedLabel, 'hutang kepada bank')) {
            // Hutang Bank Jangka Panjang (22.01)
            $loanAccounts = Account::where('code', 'like', '22.01%')->pluck('id')->toArray();

            return (float) DB::table('journal_lines')
                ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
                ->whereIn('journal_lines.account_id', $loanAccounts)
                ->where('journal_entries.status', 'posted')
                ->where('journal_entries.entry_number', 'not like', 'SA%')
                ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
                ->when($unitFilter !== 'all', fn ($q) => $q->where('journal_lines.unit_id', $unitFilter))
                ->sum('journal_lines.debit');
        } elseif (str_contains($normalizedLabel, 'karyawan')) {
            // Beban Gaji Karyawan (51.01, 61.01, 61.02, 61.03, 61.04, 61.05)
            $payrollAccounts = Account::where(function ($q) {
                $q->where('code', 'like', '51.01%')
                    ->orWhere('code', 'like', '61.01%')
                    ->orWhere('code', 'like', '61.02%')
                    ->orWhere('code', 'like', '61.03%')
                    ->orWhere('code', 'like', '61.04%')
                    ->orWhere('code', 'like', '61.05%');
            })->pluck('id')->toArray();

            $val = (float) DB::table('journal_lines')
                ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
                ->whereIn('journal_lines.account_id', $payrollAccounts)
                ->where('journal_entries.status', 'posted')
                ->where('journal_entries.entry_number', 'not like', 'SA%')
                ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
                ->when($unitFilter !== 'all', fn ($q) => $q->where('journal_lines.unit_id', $unitFilter))
                ->sum(DB::raw('journal_lines.debit - journal_lines.credit'));

            return abs($val);
        } elseif (str_contains($normalizedLabel, 'pemasok')) {
            // Working capital reconciliation formula (Direct Method Cash Flow):
            // M23 (Beban Operasional non-Gaji) + M36 (Akumulasi Penyusutan) + M37 (Persediaan) + M38 (Beban Dibayar Dimuka) + M39 (Aset Lancar Lainnya) + M40 (Mutasi Hutang)
            $all56 = Account::where(fn ($q) => $q->where('code', 'like', '5%')->orWhere('code', 'like', '6%'))->pluck('id')->toArray();
            $salary = Account::where(fn ($q) => $q->where('code', 'like', '51.01%')
                ->orWhere('code', 'like', '61.01%')
                ->orWhere('code', 'like', '61.02%')
                ->orWhere('code', 'like', '61.03%')
                ->orWhere('code', 'like', '61.04%')
                ->orWhere('code', 'like', '61.05%')
            )->pluck('id')->toArray();
            $opAccounts = array_diff($all56, $salary);

            $m23 = (float) DB::table('journal_lines')
                ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
                ->whereIn('journal_lines.account_id', $opAccounts)
                ->where('journal_entries.status', 'posted')
                ->where('journal_entries.entry_number', 'not like', 'SA%')
                ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
                ->when($unitFilter !== 'all', fn ($q) => $q->where('journal_lines.unit_id', $unitFilter))
                ->sum(DB::raw('journal_lines.debit - journal_lines.credit'));

            $deprecAccounts = Account::where(fn ($q) => $q->where('code', 'like', '12.10%')
                ->orWhere('code', 'like', '12.11%')
                ->orWhere('code', 'like', '12.12%')
                ->orWhere('code', 'like', '12.13%')
                ->orWhere('code', 'like', '12.14%')
            )->pluck('id')->toArray();
            $m36 = (float) DB::table('journal_lines')
                ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
                ->whereIn('journal_lines.account_id', $deprecAccounts)
                ->where('journal_entries.status', 'posted')
                ->where('journal_entries.entry_number', 'not like', 'SA%')
                ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
                ->when($unitFilter !== 'all', fn ($q) => $q->where('journal_lines.unit_id', $unitFilter))
                ->sum(DB::raw('journal_lines.debit - journal_lines.credit'));

            $invAccounts = Account::where('code', 'like', '11.08%')->pluck('id')->toArray();
            $m37 = (float) DB::table('journal_lines')
                ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
                ->whereIn('journal_lines.account_id', $invAccounts)
                ->where('journal_entries.status', 'posted')
                ->where('journal_entries.entry_number', 'not like', 'SA%')
                ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
                ->when($unitFilter !== 'all', fn ($q) => $q->where('journal_lines.unit_id', $unitFilter))
                ->sum(DB::raw('journal_lines.debit - journal_lines.credit'));

            $prepaidAccounts = Account::where('code', 'like', '11.09%')->pluck('id')->toArray();
            $m38 = (float) DB::table('journal_lines')
                ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
                ->whereIn('journal_lines.account_id', $prepaidAccounts)
                ->where('journal_entries.status', 'posted')
                ->where('journal_entries.entry_number', 'not like', 'SA%')
                ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
                ->when($unitFilter !== 'all', fn ($q) => $q->where('journal_lines.unit_id', $unitFilter))
                ->sum(DB::raw('journal_lines.debit - journal_lines.credit'));

            $otherAssetAccounts = Account::where(fn ($q) => $q->where('code', 'like', '11.10%')
                ->orWhere('code', 'like', '11.11%')
                ->orWhere('code', 'like', '11.12%')
            )->pluck('id')->toArray();
            $m39 = (float) DB::table('journal_lines')
                ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
                ->whereIn('journal_lines.account_id', $otherAssetAccounts)
                ->where('journal_entries.status', 'posted')
                ->where('journal_entries.entry_number', 'not like', 'SA%')
                ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
                ->when($unitFilter !== 'all', fn ($q) => $q->where('journal_lines.unit_id', $unitFilter))
                ->sum(DB::raw('journal_lines.debit - journal_lines.credit'));

            $liabAccounts = Account::where(fn ($q) => $q->where('code', 'like', '2%'))->pluck('id')->toArray();
            $m40 = (float) DB::table('journal_lines')
                ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
                ->whereIn('journal_lines.account_id', $liabAccounts)
                ->where('journal_entries.status', 'posted')
                ->where('journal_entries.entry_number', 'not like', 'SA%')
                ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
                ->when($unitFilter !== 'all', fn ($q) => $q->where('journal_lines.unit_id', $unitFilter))
                ->sum(DB::raw('journal_lines.debit - journal_lines.credit'));

            return abs($m23 + $m36 + $m37 + $m38 + $m39 + $m40);
        }

        // 3. Fallback to standard row calculateValue
        return abs($row->calculateValue($startDate, $endDate, $unitFilter));
    }

    /**
     * Get constituent accounts breakdown for a CashFlowRow.
     */
    public function getRowBreakdown(int $rowId, string $startDate, string $endDate, string $unitFilter = 'all'): array
    {
        $row = CashFlowRow::find($rowId);
        if (! $row) {
            return [];
        }

        $normalizedLabel = strtolower(trim($row->label));

        if (str_contains($normalizedLabel, 'pemasok')) {
            $all56 = Account::where(fn ($q) => $q->where('code', 'like', '5%')->orWhere('code', 'like', '6%'))->pluck('id')->toArray();
            $salary = Account::where(fn ($q) => $q->where('code', 'like', '51.01%')
                ->orWhere('code', 'like', '61.01%')
                ->orWhere('code', 'like', '61.02%')
                ->orWhere('code', 'like', '61.03%')
                ->orWhere('code', 'like', '61.04%')
                ->orWhere('code', 'like', '61.05%')
            )->pluck('id')->toArray();
            $opAccounts = array_diff($all56, $salary);

            $deprecAccounts = Account::where(fn ($q) => $q->where('code', 'like', '12.10%')
                ->orWhere('code', 'like', '12.11%')
                ->orWhere('code', 'like', '12.12%')
                ->orWhere('code', 'like', '12.13%')
                ->orWhere('code', 'like', '12.14%')
            )->pluck('id')->toArray();

            $invAccounts = Account::where('code', 'like', '11.08%')->pluck('id')->toArray();
            $prepaidAccounts = Account::where('code', 'like', '11.09%')->pluck('id')->toArray();
            $otherAssetAccounts = Account::where(fn ($q) => $q->where('code', 'like', '11.10%')
                ->orWhere('code', 'like', '11.11%')
                ->orWhere('code', 'like', '11.12%')
            )->pluck('id')->toArray();
            $liabAccounts = Account::where(fn ($q) => $q->where('code', 'like', '2%'))->pluck('id')->toArray();

            $accountIds = array_unique(array_merge($opAccounts, $deprecAccounts, $invAccounts, $prepaidAccounts, $otherAssetAccounts, $liabAccounts));

            return $this->getAccountsBreakdownData($accountIds, $startDate, $endDate, $unitFilter);
        }

        if (str_contains($normalizedLabel, 'karyawan')) {
            $payrollAccounts = Account::where(function ($q) {
                $q->where('code', 'like', '51.01%')
                    ->orWhere('code', 'like', '61.01%')
                    ->orWhere('code', 'like', '61.02%')
                    ->orWhere('code', 'like', '61.03%')
                    ->orWhere('code', 'like', '61.04%')
                    ->orWhere('code', 'like', '61.05%');
            })->pluck('id')->toArray();

            return $this->getAccountsBreakdownData($payrollAccounts, $startDate, $endDate, $unitFilter);
        }

        return $row->getBreakdownAccounts($startDate, $endDate, $unitFilter);
    }

    /**
     * Fetch formatted breakdown rows for given account IDs.
     */
    protected function getAccountsBreakdownData(array $accountIds, string $startDate, string $endDate, string $unitFilter = 'all'): array
    {
        if (empty($accountIds)) {
            return [];
        }

        $accounts = Account::whereIn('id', $accountIds)->orderBy('code')->get();

        $query = DB::table('journal_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->whereIn('journal_lines.account_id', $accountIds)
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_number', 'not like', 'SA%')
            ->whereBetween('journal_entries.entry_date', [$startDate, $endDate]);

        if ($unitFilter !== 'all') {
            $query->where('journal_lines.unit_id', $unitFilter);
        }

        $results = $query->select(
            'journal_lines.account_id',
            DB::raw('SUM(journal_lines.debit) as total_debit'),
            DB::raw('SUM(journal_lines.credit) as total_credit')
        )->groupBy('journal_lines.account_id')->get()->keyBy('account_id');

        $breakdown = [];
        foreach ($accounts as $acc) {
            $res = $results->get($acc->id);
            $debit = (float) ($res->total_debit ?? 0);
            $credit = (float) ($res->total_credit ?? 0);
            $net = ($acc->normal_balance === 'debit') ? ($debit - $credit) : ($credit - $debit);

            if ($debit > 0 || $credit > 0) {
                $breakdown[] = [
                    'id' => $acc->id,
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'debit' => $debit,
                    'credit' => $credit,
                    'net' => $net,
                ];
            }
        }

        return $breakdown;
    }

    /**
     * Seed default rows if none exist.
     */
    public function seedDefaultRows(int $companyId): void
    {
        app(CashFlowRowSeeder::class)->run($companyId);
    }
}
