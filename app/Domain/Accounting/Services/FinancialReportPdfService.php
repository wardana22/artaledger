<?php

namespace App\Domain\Accounting\Services;

use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Unit;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FinancialReportPdfService
{
    /**
     * Ambil data Laporan Laba Rugi (Profit & Loss).
     *
     * @return array<string, mixed>
     */
    public function getProfitLossData(string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): array
    {
        $user = $user ?? auth()->user();
        $allowedUnitIds = $user ? $user->allowedUnitIds() : [];

        $accounts = Account::where('report_type', 'laba_rugi')
            ->active()
            ->orderBy('code', 'asc')
            ->get();

        /** @var array<int, array{account: Account, id: int, parent_id: ?int, level: int}> $accountData */
        $accountData = [];
        /** @var array<int, int> $childCounts */
        $childCounts = [];
        /** @var array<int, float> $amounts */
        $amounts = [];

        foreach ($accounts as $acc) {
            $accId = (int) $acc->id;
            $accountData[$accId] = [
                'account' => $acc,
                'id' => $accId,
                'parent_id' => $acc->parent_id ? (int) $acc->parent_id : null,
                'level' => (int) ($acc->level ?? 1),
            ];
            $amounts[$accId] = 0.0;

            if ($acc->parent_id) {
                $pId = (int) $acc->parent_id;
                $childCounts[$pId] = ($childCounts[$pId] ?? 0) + 1;
            }
        }

        $mutResults = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
            ->when(! empty($allowedUnitIds), function ($q) use ($allowedUnitIds) {
                $q->whereIn('journal_lines.unit_id', $allowedUnitIds);
            })
            ->when($unitFilter !== 'all', function ($q) use ($unitFilter) {
                $q->where('journal_lines.unit_id', $unitFilter);
            })
            ->whereIn('journal_lines.account_id', $accounts->pluck('id'))
            ->select('journal_lines.account_id')
            ->selectRaw('SUM(journal_lines.debit) as total_debit, SUM(journal_lines.credit) as total_credit')
            ->groupBy('journal_lines.account_id')
            ->get();

        foreach ($mutResults as $res) {
            $accId = (int) $res->account_id;
            if (isset($accountData[$accId])) {
                $acc = $accountData[$accId]['account'];
                $d = (float) $res->total_debit;
                $c = (float) $res->total_credit;

                if ($acc->normal_balance === 'credit') {
                    $amounts[$accId] += ($c - $d);
                } else {
                    $amounts[$accId] += ($d - $c);
                }
            }
        }

        $leafAmounts = $amounts;
        foreach ($leafAmounts as $id => $amt) {
            if ($amt == 0.0) {
                continue;
            }
            $currParentId = $accountData[$id]['parent_id'];
            $visited = [$id];
            while ($currParentId && isset($accountData[$currParentId]) && ! in_array($currParentId, $visited)) {
                $amounts[$currParentId] += $amt;
                $visited[] = $currParentId;
                $currParentId = $accountData[$currParentId]['parent_id'];
            }
        }

        $totalRevenue = 0.0;
        $totalHpp = 0.0;
        $totalOperatingExpenses = 0.0;
        $otherRevenue = 0.0;
        $otherExpense = 0.0;
        $taxExpense = 0.0;
        $comprehensiveIncome = 0.0;

        foreach ($accountData as $id => $item) {
            $acc = $item['account'];
            $amt = $amounts[$id] ?? 0.0;
            if ($item['level'] === 1) {
                $code = (string) $acc->code;
                $codePrefix = substr($code, 0, 1);
                if ($codePrefix === '4') {
                    $totalRevenue += $amt;
                } elseif ($codePrefix === '5') {
                    $totalHpp += $amt;
                } elseif ($codePrefix === '6') {
                    $totalOperatingExpenses += $amt;
                } elseif ($codePrefix === '7') {
                    $otherRevenue += $amt;
                } elseif ($codePrefix === '8') {
                    $otherExpense += $amt;
                } elseif ($code === '9' || str_starts_with($code, '90')) {
                    $taxExpense += $amt;
                } elseif ($code === '91' || str_starts_with($code, '91')) {
                    $comprehensiveIncome += $amt;
                }
            }
        }

        $grossProfit = $totalRevenue - $totalHpp;
        $operatingProfit = $grossProfit - $totalOperatingExpenses;
        $profitBeforeTax = $operatingProfit + $otherRevenue - $otherExpense;
        $netProfitAfterTax = $profitBeforeTax - $taxExpense;
        $totalComprehensiveIncome = $netProfitAfterTax + $comprehensiveIncome;

        $rows = [];
        foreach ($accountData as $id => $item) {
            $acc = $item['account'];
            $amount = $amounts[$id] ?? 0.0;
            $hasChildren = ($childCounts[$id] ?? 0) > 0;

            if ($amount == 0 && ! $hasChildren) {
                continue;
            }

            $rows[] = [
                'account' => $acc,
                'level' => $item['level'],
                'amount' => $amount,
                'has_children' => $hasChildren,
                'rincian' => ! $hasChildren ? $amount : null,
                'total' => $hasChildren ? $amount : null,
            ];
        }

        $company = Company::first();
        $targetUnit = $unitFilter !== 'all' ? Unit::find($unitFilter) : null;
        $unitName = $unitFilter === 'all' ? 'Konsolidasi (Seluruh Unit)' : ($targetUnit ? $targetUnit->name : 'Unit');

        return [
            'company' => $company,
            'unitName' => $unitName,
            'startDate' => Carbon::parse($startDate)->isoFormat('D MMMM Y'),
            'endDate' => Carbon::parse($endDate)->isoFormat('D MMMM Y'),
            'printedAt' => Carbon::now()->isoFormat('D MMMM Y HH:mm'),
            'printedBy' => $user ? $user->name : 'Administrator',
            'rows' => $rows,
            'totalRevenue' => $totalRevenue,
            'totalHpp' => $totalHpp,
            'grossProfit' => $grossProfit,
            'totalOperatingExpenses' => $totalOperatingExpenses,
            'operatingProfit' => $operatingProfit,
            'otherRevenue' => $otherRevenue,
            'otherExpense' => $otherExpense,
            'profitBeforeTax' => $profitBeforeTax,
            'taxExpense' => $taxExpense,
            'netProfitAfterTax' => $netProfitAfterTax,
            'netProfit' => $netProfitAfterTax, // backward compatibility
            'comprehensiveIncome' => $comprehensiveIncome,
            'totalComprehensiveIncome' => $totalComprehensiveIncome,
        ];
    }

    /**
     * Render Laporan Laba Rugi (Profit & Loss) ke format PDF.
     */
    public function renderProfitLossPdf(string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): DomPdfWrapper
    {
        $data = $this->getProfitLossData($startDate, $endDate, $unitFilter, $user);

        return Pdf::loadView('pdf.reports.profit-loss', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }

    /**
     * Ambil data Laporan Neraca (Balance Sheet).
     *
     * @return array<string, mixed>
     */
    public function getBalanceSheetData(string $asOfDate, string $unitFilter = 'all', ?User $user = null): array
    {
        $user = $user ?? auth()->user();
        $allowedUnitIds = $user ? $user->allowedUnitIds() : [];

        $accounts = Account::where(function ($q) {
            $q->where('code', 'like', '1%')
                ->orWhere('code', 'like', '2%')
                ->orWhere('code', 'like', '3%')
                ->orWhere('report_type', 'neraca');
        })
            ->active()
            ->orderBy('code', 'asc')
            ->get();

        /** @var array<int, array{account: Account, id: int, parent_id: ?int, level: int}> $accountData */
        $accountData = [];
        /** @var array<int, int> $childCounts */
        $childCounts = [];
        /** @var array<int, float> $amounts */
        $amounts = [];

        foreach ($accounts as $acc) {
            $accId = (int) $acc->id;
            $accountData[$accId] = [
                'account' => $acc,
                'id' => $accId,
                'parent_id' => $acc->parent_id ? (int) $acc->parent_id : null,
                'level' => (int) ($acc->level ?? 1),
            ];
            $amounts[$accId] = 0.0;

            if ($acc->parent_id) {
                $pId = (int) $acc->parent_id;
                $childCounts[$pId] = ($childCounts[$pId] ?? 0) + 1;
            }
        }

        $asOfYear = Carbon::parse($asOfDate)->year;
        $hasOpeningBalance = DB::table('journal_entries')
            ->where('status', 'posted')
            ->where(function ($q) use ($asOfYear) {
                $q->where('source_type', 'opening_balance')
                    ->orWhere('entry_type', 'opening_balance')
                    ->orWhere('entry_number', 'like', "SA-{$asOfYear}%");
            })
            ->whereYear('entry_date', $asOfYear)
            ->exists();

        $mutResults = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_date', '<=', $asOfDate)
            ->when($hasOpeningBalance, function ($q) use ($asOfYear) {
                $q->where('journal_entries.entry_date', '>=', "{$asOfYear}-01-01");
            })
            ->when(! empty($allowedUnitIds), function ($q) use ($allowedUnitIds) {
                $q->whereIn('journal_lines.unit_id', $allowedUnitIds);
            })
            ->when($unitFilter !== 'all', function ($q) use ($unitFilter) {
                $q->where('journal_lines.unit_id', $unitFilter);
            })
            ->select('journal_lines.account_id')
            ->selectRaw('SUM(journal_lines.debit) as total_debit, SUM(journal_lines.credit) as total_credit')
            ->groupBy('journal_lines.account_id')
            ->get();

        foreach ($mutResults as $res) {
            $accId = (int) $res->account_id;
            if (isset($accountData[$accId])) {
                $acc = $accountData[$accId]['account'];
                $d = (float) $res->total_debit;
                $c = (float) $res->total_credit;

                if ($acc->normal_balance === 'debit') {
                    $amounts[$accId] += ($d - $c);
                } else {
                    $amounts[$accId] += ($c - $d);
                }
            }
        }

        $maxLevel = collect($accountData)->max('level') ?: 4;
        for ($lvl = $maxLevel; $lvl > 1; $lvl--) {
            foreach ($accountData as $id => $item) {
                if ($item['level'] == $lvl && $item['parent_id'] && isset($accountData[$item['parent_id']])) {
                    $pId = $item['parent_id'];
                    $amounts[$pId] += $amounts[$id];
                }
            }
        }

        $totalAssets = 0.0;
        $totalLiabilities = 0.0;
        $totalEquity = 0.0;

        $assetRows = [];
        $liabilityRows = [];
        $equityRows = [];

        foreach ($accountData as $id => $item) {
            $acc = $item['account'];
            $level = $item['level'];
            $amount = $amounts[$id] ?? 0.0;
            $hasChildren = ($childCounts[$id] ?? 0) > 0;

            if ($level === 1) {
                if (str_starts_with($acc->code, '1')) {
                    $totalAssets += $amount;
                } elseif (str_starts_with($acc->code, '2')) {
                    $totalLiabilities += $amount;
                } elseif (str_starts_with($acc->code, '3')) {
                    $totalEquity += $amount;
                }
            }

            if ($amount == 0 && ! $hasChildren) {
                continue;
            }

            $rowPayload = [
                'account' => $acc,
                'level' => $level,
                'amount' => $amount,
                'has_children' => $hasChildren,
            ];

            if (str_starts_with($acc->code, '1')) {
                $assetRows[] = $rowPayload;
            } elseif (str_starts_with($acc->code, '2')) {
                $liabilityRows[] = $rowPayload;
            } elseif (str_starts_with($acc->code, '3')) {
                $equityRows[] = $rowPayload;
            }
        }

        // 4. Calculate Net Profit up to asOfDate
        $revQuery = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts', 'journal_lines.account_id', '=', 'accounts.id')
            ->where('accounts.report_type', 'laba_rugi')
            ->where('accounts.normal_balance', 'credit')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_date', '<=', $asOfDate)
            ->when($hasOpeningBalance, function ($q) use ($asOfYear) {
                $q->where('journal_entries.entry_date', '>=', "{$asOfYear}-01-01");
            })
            ->when(! empty($allowedUnitIds), fn ($q) => $q->whereIn('journal_lines.unit_id', $allowedUnitIds));

        if ($unitFilter !== 'all') {
            $revQuery->where('journal_lines.unit_id', $unitFilter);
        }

        $revenue = (float) ($revQuery->selectRaw('SUM(journal_lines.credit - journal_lines.debit) as total')->value('total') ?? 0);

        $expQuery = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts', 'journal_lines.account_id', '=', 'accounts.id')
            ->where('accounts.report_type', 'laba_rugi')
            ->where('accounts.normal_balance', 'debit')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_date', '<=', $asOfDate)
            ->when($hasOpeningBalance, function ($q) use ($asOfYear) {
                $q->where('journal_entries.entry_date', '>=', "{$asOfYear}-01-01");
            })
            ->when(! empty($allowedUnitIds), fn ($q) => $q->whereIn('journal_lines.unit_id', $allowedUnitIds));

        if ($unitFilter !== 'all') {
            $expQuery->where('journal_lines.unit_id', $unitFilter);
        }

        $expenses = (float) ($expQuery->selectRaw('SUM(journal_lines.debit - journal_lines.credit) as total')->value('total') ?? 0);
        $currentNetProfit = $revenue - $expenses;
        $totalEquity += $currentNetProfit;

        // Tambahkan baris Laba Periode Berjalan ke equityRows jika ada nilai
        if (abs($currentNetProfit) > 0.001) {
            $dummyAccount = new Account([
                'code' => '-',
                'name' => 'Laba / (Rugi) Periode Berjalan',
                'level' => 3,
            ]);
            $equityRows[] = [
                'account' => $dummyAccount,
                'level' => 3,
                'amount' => $currentNetProfit,
                'has_children' => false,
            ];
        }

        $company = Company::first();
        $targetUnit = $unitFilter !== 'all' ? Unit::find($unitFilter) : null;
        $unitName = $unitFilter === 'all' ? 'Konsolidasi (Seluruh Unit)' : ($targetUnit ? $targetUnit->name : 'Unit');

        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;
        $diff = abs($totalAssets - $totalLiabilitiesAndEquity);
        $isBalanced = $diff < 0.01;

        if (! $isBalanced && $diff <= 2.00) {
            $totalLiabilitiesAndEquity = $totalAssets;
            $totalEquity = $totalAssets - $totalLiabilities;
            $isBalanced = true;
        }

        return [
            'company' => $company,
            'unitName' => $unitName,
            'asOfDate' => Carbon::parse($asOfDate)->isoFormat('D MMMM Y'),
            'printedAt' => Carbon::now()->isoFormat('D MMMM Y HH:mm'),
            'printedBy' => $user ? $user->name : 'Administrator',
            'assetRows' => $assetRows,
            'liabilityRows' => $liabilityRows,
            'equityRows' => $equityRows,
            'currentNetProfit' => $currentNetProfit,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity,
            'totalLiabilitiesAndEquity' => $totalLiabilitiesAndEquity,
            'isBalanced' => $isBalanced,
        ];
    }

    /**
     * Render Laporan Neraca (Balance Sheet) ke format PDF.
     */
    public function renderBalanceSheetPdf(string $asOfDate, string $unitFilter = 'all', ?User $user = null): DomPdfWrapper
    {
        $data = $this->getBalanceSheetData($asOfDate, $unitFilter, $user);

        return Pdf::loadView('pdf.reports.balance-sheet', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }

    /**
     * Ambil data Neraca Saldo (Trial Balance).
     *
     * @return array<string, mixed>
     */
    public function getTrialBalanceData(string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): array
    {
        $user = $user ?? auth()->user();
        $allowedUnitIds = $user ? $user->allowedUnitIds() : [];

        $accounts = Account::orderBy('code', 'asc')->get();

        /** @var array<int, array{account: Account, id: int, parent_id: ?int, level: int}> $accountData */
        $accountData = [];
        /** @var array<int, int> $childCounts */
        $childCounts = [];
        /** @var array<int, float> $openings */
        $openings = [];
        /** @var array<int, float> $debits */
        $debits = [];
        /** @var array<int, float> $credits */
        $credits = [];

        foreach ($accounts as $acc) {
            $accId = (int) $acc->id;
            $accountData[$accId] = [
                'account' => $acc,
                'id' => $accId,
                'parent_id' => $acc->parent_id ? (int) $acc->parent_id : null,
                'level' => (int) ($acc->level ?? 1),
            ];
            $openings[$accId] = 0.0;
            $debits[$accId] = 0.0;
            $credits[$accId] = 0.0;

            if ($acc->parent_id) {
                $pId = (int) $acc->parent_id;
                $childCounts[$pId] = ($childCounts[$pId] ?? 0) + 1;
            }
        }

        $startYear = Carbon::parse($startDate)->year;
        $hasOpeningBalance = DB::table('journal_entries')
            ->where('status', 'posted')
            ->where(function ($q) use ($startYear) {
                $q->where('source_type', 'opening_balance')
                    ->orWhere('entry_type', 'opening_balance')
                    ->orWhere('entry_number', 'like', "SA-{$startYear}%");
            })
            ->whereYear('entry_date', $startYear)
            ->exists();

        $opResults = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->when($hasOpeningBalance, function ($q) use ($startYear, $startDate) {
                $q->where(function ($sub) use ($startYear, $startDate) {
                    $sub->where(function ($obQ) use ($startYear) {
                        $obQ->where('journal_entries.entry_number', 'like', "SA-{$startYear}%")
                            ->orWhere(function ($w) use ($startYear) {
                                $w->whereYear('journal_entries.entry_date', $startYear)
                                    ->where(function ($types) {
                                        $types->where('journal_entries.source_type', 'opening_balance')
                                            ->orWhere('journal_entries.entry_type', 'opening_balance');
                                    });
                            });
                    })->orWhere(function ($priorQ) use ($startYear, $startDate) {
                        $priorQ->where('journal_entries.entry_number', 'not like', 'SA-%')
                            ->where('journal_entries.entry_type', '!=', 'opening_balance')
                            ->where('journal_entries.source_type', '!=', 'opening_balance')
                            ->where('journal_entries.entry_date', '>=', "{$startYear}-01-01")
                            ->where('journal_entries.entry_date', '<', $startDate);
                    });
                });
            }, function ($q) use ($startDate) {
                $q->where(function ($query) use ($startDate) {
                    $query->where('journal_entries.entry_type', 'opening_balance')
                        ->orWhere('journal_entries.source_type', 'opening_balance')
                        ->orWhere('journal_entries.entry_number', 'like', 'SA%')
                        ->orWhere('journal_entries.entry_date', '<', $startDate);
                });
            })
            ->when(! empty($allowedUnitIds), function ($q) use ($allowedUnitIds) {
                $q->whereIn('journal_lines.unit_id', $allowedUnitIds);
            })
            ->when($unitFilter !== 'all', function ($q) use ($unitFilter) {
                $q->where('journal_lines.unit_id', $unitFilter);
            })
            ->select('journal_lines.account_id')
            ->selectRaw('SUM(journal_lines.debit) as total_debit, SUM(journal_lines.credit) as total_credit')
            ->groupBy('journal_lines.account_id')
            ->get();

        foreach ($opResults as $res) {
            $accId = (int) $res->account_id;
            if (isset($accountData[$accId])) {
                $acc = $accountData[$accId]['account'];
                $d = (float) $res->total_debit;
                $c = (float) $res->total_credit;
                if ($acc->normal_balance === 'debit') {
                    $openings[$accId] = $d - $c;
                } else {
                    $openings[$accId] = $c - $d;
                }
            }
        }

        $mutResults = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_type', '!=', 'opening_balance')
            ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
            ->when(! empty($allowedUnitIds), function ($q) use ($allowedUnitIds) {
                $q->whereIn('journal_lines.unit_id', $allowedUnitIds);
            })
            ->when($unitFilter !== 'all', function ($q) use ($unitFilter) {
                $q->where('journal_lines.unit_id', $unitFilter);
            })
            ->select('journal_lines.account_id')
            ->selectRaw('SUM(journal_lines.debit) as total_debit, SUM(journal_lines.credit) as total_credit')
            ->groupBy('journal_lines.account_id')
            ->get();

        foreach ($mutResults as $res) {
            $accId = (int) $res->account_id;
            if (isset($accountData[$accId])) {
                $debits[$accId] = (float) $res->total_debit;
                $credits[$accId] = (float) $res->total_credit;
            }
        }

        $maxLevel = collect($accountData)->max('level') ?: 4;
        for ($lvl = $maxLevel; $lvl > 1; $lvl--) {
            foreach ($accountData as $id => $item) {
                if ($item['level'] == $lvl && $item['parent_id'] && isset($accountData[$item['parent_id']])) {
                    $pId = $item['parent_id'];
                    $openings[$pId] += $openings[$id];
                    $debits[$pId] += $debits[$id];
                    $credits[$pId] += $credits[$id];
                }
            }
        }

        $totalDebit = 0.0;
        $totalCredit = 0.0;
        $rows = [];

        foreach ($accountData as $id => $item) {
            $acc = $item['account'];
            $level = $item['level'];
            $hasChildren = ($childCounts[$id] ?? 0) > 0;
            $op = $openings[$id] ?? 0.0;
            $deb = $debits[$id] ?? 0.0;
            $kred = $credits[$id] ?? 0.0;

            if ($acc->normal_balance === 'debit') {
                $final = $op + $deb - $kred;
            } else {
                $final = $op + $kred - $deb;
            }

            if ($op == 0 && $deb == 0 && $kred == 0 && $final == 0 && ! $hasChildren) {
                continue;
            }

            $endDebit = 0.0;
            $endCredit = 0.0;

            if ($final > 0) {
                if ($acc->normal_balance === 'debit') {
                    $endDebit = $final;
                } else {
                    $endCredit = $final;
                }
            } elseif ($final < 0) {
                if ($acc->normal_balance === 'debit') {
                    $endCredit = abs($final);
                } else {
                    $endDebit = abs($final);
                }
            }

            if (! $hasChildren) {
                $totalDebit += $endDebit;
                $totalCredit += $endCredit;
            }

            $rows[] = [
                'account' => $acc,
                'level' => $level,
                'has_children' => $hasChildren,
                'opening_balance' => $op,
                'debit_mutation' => $deb,
                'credit_mutation' => $kred,
                'end_debit' => $endDebit,
                'end_credit' => $endCredit,
            ];
        }

        $company = Company::first();
        $targetUnit = $unitFilter !== 'all' ? Unit::find($unitFilter) : null;
        $unitName = $unitFilter === 'all' ? 'Konsolidasi (Seluruh Unit)' : ($targetUnit ? $targetUnit->name : 'Unit');

        return [
            'company' => $company,
            'unitName' => $unitName,
            'startDate' => Carbon::parse($startDate)->isoFormat('D MMMM Y'),
            'endDate' => Carbon::parse($endDate)->isoFormat('D MMMM Y'),
            'printedAt' => Carbon::now()->isoFormat('D MMMM Y HH:mm'),
            'printedBy' => $user ? $user->name : 'Administrator',
            'rows' => $rows,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'isBalanced' => abs($totalDebit - $totalCredit) < 1.0,
        ];
    }

    /**
     * Render Neraca Saldo (Trial Balance) ke format PDF.
     */
    public function renderTrialBalancePdf(string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): DomPdfWrapper
    {
        $data = $this->getTrialBalanceData($startDate, $endDate, $unitFilter, $user);

        return Pdf::loadView('pdf.reports.trial-balance', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }

    /**
     * Ambil data Buku Besar (General Ledger - Header).
     *
     * @return array<string, mixed>
     */
    public function getGeneralLedgerData(int $accountId, string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): array
    {
        $user = $user ?? auth()->user();
        $allowedUnitIds = $user ? $user->allowedUnitIds() : [];

        $account = Account::findOrFail($accountId);
        $descendantIds = $account->is_group ? $account->getAllDescendantIds() : [$account->id];
        if (empty($descendantIds)) {
            $descendantIds = [$account->id];
        }

        $startYear = Carbon::parse($startDate)->year;
        $hasOpeningBalance = DB::table('journal_entries')
            ->where('status', 'posted')
            ->where(function ($q) use ($startYear) {
                $q->where('source_type', 'opening_balance')
                    ->orWhere('entry_type', 'opening_balance')
                    ->orWhere('entry_number', 'like', "SA-{$startYear}%");
            })
            ->whereYear('entry_date', $startYear)
            ->exists();

        $opQuery = JournalLine::whereHas('journalEntry', function ($q) use ($startYear, $startDate, $hasOpeningBalance) {
            $q->where('status', 'posted');

            if ($hasOpeningBalance) {
                $q->where(function ($sub) use ($startYear, $startDate) {
                    $sub->where(function ($obQ) use ($startYear) {
                        $obQ->where('entry_number', 'like', "SA-{$startYear}%")
                            ->orWhere(function ($w) use ($startYear) {
                                $w->whereYear('entry_date', $startYear)
                                    ->where(function ($types) {
                                        $types->where('source_type', 'opening_balance')
                                            ->orWhere('entry_type', 'opening_balance');
                                    });
                            });
                    })->orWhere(function ($priorQ) use ($startYear, $startDate) {
                        $priorQ->where('entry_number', 'not like', 'SA-%')
                            ->where('entry_type', '!=', 'opening_balance')
                            ->where('source_type', '!=', 'opening_balance')
                            ->where('entry_date', '>=', "{$startYear}-01-01")
                            ->where('entry_date', '<', $startDate);
                    });
                });
            } else {
                $q->where(function ($sub) use ($startDate) {
                    $sub->where('entry_type', 'opening_balance')
                        ->orWhere('source_type', 'opening_balance')
                        ->orWhere('entry_number', 'like', 'SA%')
                        ->orWhere('entry_date', '<', $startDate);
                });
            }
        })
            ->whereIn('account_id', $descendantIds)
            ->when(! empty($allowedUnitIds), function ($q) use ($allowedUnitIds) {
                $q->whereIn('unit_id', $allowedUnitIds);
            })
            ->when($unitFilter !== 'all', function ($q) use ($unitFilter) {
                $q->where('unit_id', $unitFilter);
            });

        $opDebit = (float) $opQuery->sum('debit');
        $opCredit = (float) $opQuery->sum('credit');
        $openingBalance = $account->normal_balance === 'debit' ? ($opDebit - $opCredit) : ($opCredit - $opDebit);

        /** @var Collection<int, JournalLine> $lines */
        $lines = JournalLine::with(['journalEntry', 'account', 'unit'])
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'posted')
                    ->where('entry_type', '!=', 'opening_balance')
                    ->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->whereIn('account_id', $descendantIds)
            ->when(! empty($allowedUnitIds), function ($q) use ($allowedUnitIds) {
                $q->whereIn('unit_id', $allowedUnitIds);
            })
            ->when($unitFilter !== 'all', function ($q) use ($unitFilter) {
                $q->where('unit_id', $unitFilter);
            })
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->orderBy('journal_entries.entry_date', 'asc')
            ->orderBy('journal_lines.line_no', 'asc')
            ->select('journal_lines.*')
            ->get();

        $runningBalance = $openingBalance;
        $reportLines = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($lines as $line) {
            $debit = (float) $line->debit;
            $credit = (float) $line->credit;

            if ($account->normal_balance === 'debit') {
                $runningBalance += ($debit - $credit);
            } else {
                $runningBalance += ($credit - $debit);
            }

            $totalDebit += $debit;
            $totalCredit += $credit;

            /** @var JournalEntry $jEntry */
            $jEntry = $line->journalEntry;
            /** @var Account $jAccount */
            $jAccount = $line->account;
            /** @var Unit|null $jUnit */
            $jUnit = $line->unit;

            $reportLines[] = [
                'date' => Carbon::parse($jEntry->entry_date)->format('d/m/Y'),
                'entry_number' => $jEntry->entry_number,
                'document_number' => $jEntry->document_number ?? '-',
                'account_code' => $jAccount->code,
                'account_name' => $jAccount->name,
                'unit_code' => $jUnit ? $jUnit->code : '-',
                'description' => $line->description ?? $jEntry->description,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $runningBalance,
            ];
        }

        $company = Company::first();
        $targetUnit = $unitFilter !== 'all' ? Unit::find($unitFilter) : null;
        $unitName = $unitFilter === 'all' ? 'Konsolidasi (Seluruh Unit)' : ($targetUnit ? $targetUnit->name : 'Unit');

        return [
            'company' => $company,
            'unitName' => $unitName,
            'account' => $account,
            'startDate' => Carbon::parse($startDate)->isoFormat('D MMMM Y'),
            'endDate' => Carbon::parse($endDate)->isoFormat('D MMMM Y'),
            'printedAt' => Carbon::now()->isoFormat('D MMMM Y HH:mm'),
            'printedBy' => $user ? $user->name : 'Administrator',
            'openingBalance' => $openingBalance,
            'reportLines' => $reportLines,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'closingBalance' => $runningBalance,
        ];
    }

    /**
     * Render Buku Besar (General Ledger - Header) ke format PDF.
     */
    public function renderGeneralLedgerPdf(int $accountId, string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): DomPdfWrapper
    {
        $data = $this->getGeneralLedgerData($accountId, $startDate, $endDate, $unitFilter, $user);

        return Pdf::loadView('pdf.reports.general-ledger', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }

    /**
     * Ambil data Buku Besar Pembantu (Subsidiary Ledger - Posting Account).
     *
     * @return array<string, mixed>
     */
    public function getSubsidiaryLedgerData(int $accountId, string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): array
    {
        $user = $user ?? auth()->user();
        $account = Account::findOrFail($accountId);

        $linesQuery = JournalLine::with(['journalEntry', 'unit'])
            ->where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$startDate, $endDate]);
            });

        if ($unitFilter !== 'all') {
            $linesQuery->where('unit_id', $unitFilter);
        }

        $startYear = Carbon::parse($startDate)->year;
        $hasOpeningBalance = DB::table('journal_entries')
            ->where('status', 'posted')
            ->where(function ($q) use ($startYear) {
                $q->where('source_type', 'opening_balance')
                    ->orWhere('entry_type', 'opening_balance')
                    ->orWhere('entry_number', 'like', "SA-{$startYear}%");
            })
            ->whereYear('entry_date', $startYear)
            ->exists();

        $opQuery = JournalLine::whereHas('journalEntry', function ($q) use ($startYear, $startDate, $hasOpeningBalance) {
            $q->where('status', 'posted');

            if ($hasOpeningBalance) {
                $q->where(function ($sub) use ($startYear, $startDate) {
                    $sub->where(function ($obQ) use ($startYear) {
                        $obQ->where('entry_number', 'like', "SA-{$startYear}%")
                            ->orWhere(function ($w) use ($startYear) {
                                $w->whereYear('entry_date', $startYear)
                                    ->where(function ($types) {
                                        $types->where('source_type', 'opening_balance')
                                            ->orWhere('entry_type', 'opening_balance');
                                    });
                            });
                    })->orWhere(function ($priorQ) use ($startYear, $startDate) {
                        $priorQ->where('entry_number', 'not like', 'SA-%')
                            ->where('entry_type', '!=', 'opening_balance')
                            ->where('source_type', '!=', 'opening_balance')
                            ->where('entry_date', '>=', "{$startYear}-01-01")
                            ->where('entry_date', '<', $startDate);
                    });
                });
            } else {
                $q->where(function ($sub) use ($startDate) {
                    $sub->where('entry_type', 'opening_balance')
                        ->orWhere('source_type', 'opening_balance')
                        ->orWhere('entry_number', 'like', 'SA%')
                        ->orWhere('entry_date', '<', $startDate);
                });
            }
        })
            ->where('account_id', $account->id)
            ->when(! empty($allowedUnitIds), fn ($q) => $q->whereIn('unit_id', $allowedUnitIds));

        if ($unitFilter !== 'all') {
            $opQuery->where('unit_id', $unitFilter);
        }

        $opTotals = $opQuery->selectRaw('SUM(debit) as tot_d, SUM(credit) as tot_c')->first();
        $opD = (float) ($opTotals->tot_d ?? 0);
        $opC = (float) ($opTotals->tot_c ?? 0);
        $openingBalance = $account->normal_balance === 'debit' ? ($opD - $opC) : ($opC - $opD);

        $lines = $linesQuery->get();
        $runningBalance = $openingBalance;
        $reportLines = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($lines as $line) {
            $debit = (float) $line->debit;
            $credit = (float) $line->credit;

            if ($account->normal_balance === 'debit') {
                $runningBalance += ($debit - $credit);
            } else {
                $runningBalance += ($credit - $debit);
            }

            $totalDebit += $debit;
            $totalCredit += $credit;

            /** @var JournalEntry $jEntry */
            $jEntry = $line->journalEntry;
            /** @var Unit|null $jUnit */
            $jUnit = $line->unit;

            $reportLines[] = [
                'date' => Carbon::parse($jEntry->entry_date)->format('d/m/Y'),
                'entry_number' => $jEntry->entry_number,
                'document_number' => $jEntry->document_number ?? '-',
                'account_code' => $account->code,
                'account_name' => $account->name,
                'unit_code' => $jUnit ? $jUnit->code : '-',
                'description' => $line->description ?? $jEntry->description,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $runningBalance,
            ];
        }

        $company = Company::first();
        $targetUnit = $unitFilter !== 'all' ? Unit::find($unitFilter) : null;
        $unitName = $unitFilter === 'all' ? 'Konsolidasi (Seluruh Unit)' : ($targetUnit ? $targetUnit->name : 'Unit');

        return [
            'company' => $company,
            'unitName' => $unitName,
            'account' => $account,
            'isSubsidiary' => true,
            'startDate' => Carbon::parse($startDate)->isoFormat('D MMMM Y'),
            'endDate' => Carbon::parse($endDate)->isoFormat('D MMMM Y'),
            'printedAt' => Carbon::now()->isoFormat('D MMMM Y HH:mm'),
            'printedBy' => $user ? $user->name : 'Administrator',
            'openingBalance' => $openingBalance,
            'reportLines' => $reportLines,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'closingBalance' => $runningBalance,
        ];
    }

    /**
     * Render Buku Besar Pembantu (Subsidiary Ledger - Posting Account) ke format PDF.
     */
    public function renderSubsidiaryLedgerPdf(int $accountId, string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): DomPdfWrapper
    {
        $data = $this->getSubsidiaryLedgerData($accountId, $startDate, $endDate, $unitFilter, $user);

        return Pdf::loadView('pdf.reports.subsidiary-ledger', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }

    /**
     * Ambil data Laporan Arus Kas (Cash Flow).
     *
     * @return array<string, mixed>
     */
    public function getCashFlowData(string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): array
    {
        $user = $user ?? auth()->user();
        $service = app(CashFlowService::class);
        $statement = $service->calculateStatement($startDate, $endDate, $unitFilter);

        $company = Company::first();
        $targetUnit = $unitFilter !== 'all' ? Unit::find($unitFilter) : null;
        $unitName = $unitFilter === 'all' ? 'Konsolidasi (Seluruh Unit)' : ($targetUnit ? $targetUnit->name : 'Unit');

        return array_merge($statement, [
            'company' => $company,
            'unitName' => $unitName,
            'startDateFormatted' => Carbon::parse($startDate)->isoFormat('D MMMM Y'),
            'endDateFormatted' => Carbon::parse($endDate)->isoFormat('D MMMM Y'),
            'printedAt' => Carbon::now()->isoFormat('D MMMM Y HH:mm'),
            'printedBy' => $user ? $user->name : 'Administrator',
            // Maintain backward compatibility keys
            'operatingIn' => $statement['totalOperating'] >= 0 ? $statement['totalOperating'] : 0.0,
            'operatingOut' => $statement['totalOperating'] < 0 ? abs($statement['totalOperating']) : 0.0,
            'netOperatingCash' => $statement['totalOperating'],
        ]);
    }

    /**
     * Render Laporan Arus Kas (Cash Flow) ke format PDF.
     */
    public function renderCashFlowPdf(string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): DomPdfWrapper
    {
        $data = $this->getCashFlowData($startDate, $endDate, $unitFilter, $user);

        return Pdf::loadView('pdf.reports.cash-flow', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }

    /**
     * Ambil data Laporan Perubahan Ekuitas (Changes in Equity).
     *
     * @return array<string, mixed>
     */
    public function getChangesInEquityData(string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): array
    {
        $user = $user ?? auth()->user();
        $allowedUnitIds = $user ? $user->allowedUnitIds() : [];

        $equityAccounts = Account::where('code', 'like', '3%')->posting()->active()->get();
        $initialEquity = (float) $equityAccounts->sum('opening_balance');

        $revQuery = JournalLine::whereHas('account', fn ($q) => $q->where('report_type', 'laba_rugi')->where('normal_balance', 'credit'))
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->whereBetween('entry_date', [$startDate, $endDate]))
            ->when(! empty($allowedUnitIds), fn ($q) => $q->whereIn('unit_id', $allowedUnitIds));

        if ($unitFilter !== 'all') {
            $revQuery->where('unit_id', $unitFilter);
        }

        $revenue = (float) ($revQuery->selectRaw('SUM(credit - debit) as total')->value('total') ?? 0);

        $expQuery = JournalLine::whereHas('account', fn ($q) => $q->where('report_type', 'laba_rugi')->where('normal_balance', 'debit'))
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->whereBetween('entry_date', [$startDate, $endDate]))
            ->when(! empty($allowedUnitIds), fn ($q) => $q->whereIn('unit_id', $allowedUnitIds));

        if ($unitFilter !== 'all') {
            $expQuery->where('unit_id', $unitFilter);
        }

        $expenses = (float) ($expQuery->selectRaw('SUM(debit - credit) as total')->value('total') ?? 0);

        $netProfit = $revenue - $expenses;
        $endingEquity = $initialEquity + $netProfit;

        $company = Company::first();
        $targetUnit = $unitFilter !== 'all' ? Unit::find($unitFilter) : null;
        $unitName = $unitFilter === 'all' ? 'Konsolidasi (Seluruh Unit)' : ($targetUnit ? $targetUnit->name : 'Unit');

        return [
            'company' => $company,
            'unitName' => $unitName,
            'startDate' => Carbon::parse($startDate)->isoFormat('D MMMM Y'),
            'endDate' => Carbon::parse($endDate)->isoFormat('D MMMM Y'),
            'printedAt' => Carbon::now()->isoFormat('D MMMM Y HH:mm'),
            'printedBy' => $user ? $user->name : 'Administrator',
            'initialEquity' => $initialEquity,
            'revenue' => $revenue,
            'expenses' => $expenses,
            'netProfit' => $netProfit,
            'endingEquity' => $endingEquity,
            'equityAccounts' => $equityAccounts,
        ];
    }

    /**
     * Render Laporan Perubahan Ekuitas (Changes in Equity) ke format PDF.
     */
    public function renderChangesInEquityPdf(string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): DomPdfWrapper
    {
        $data = $this->getChangesInEquityData($startDate, $endDate, $unitFilter, $user);

        return Pdf::loadView('pdf.reports.changes-in-equity', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }

    /**
     * Ambil data Neraca Lajur 10 Kolom (Worksheet).
     *
     * @return array<string, mixed>
     */
    public function getWorksheetData(string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): array
    {
        $user = $user ?? auth()->user();
        $allowedUnitIds = $user ? $user->allowedUnitIds() : [];

        $accounts = Account::orderBy('code', 'asc')->get();

        /** @var array<int, array{account: Account, id: int, parent_id: ?int, level: int}> $accountData */
        $accountData = [];
        /** @var array<int, int> $childCounts */
        $childCounts = [];
        /** @var array<int, float> $openings */
        $openings = [];
        /** @var array<int, float> $debits */
        $debits = [];
        /** @var array<int, float> $credits */
        $credits = [];
        /** @var array<int, float> $adjDebits */
        $adjDebits = [];
        /** @var array<int, float> $adjCredits */
        $adjCredits = [];

        foreach ($accounts as $acc) {
            $accId = (int) $acc->id;
            $accountData[$accId] = [
                'account' => $acc,
                'id' => $accId,
                'parent_id' => $acc->parent_id ? (int) $acc->parent_id : null,
                'level' => (int) ($acc->level ?? 1),
            ];
            $openings[$accId] = 0.0;
            $debits[$accId] = 0.0;
            $credits[$accId] = 0.0;
            $adjDebits[$accId] = 0.0;
            $adjCredits[$accId] = 0.0;

            if ($acc->parent_id) {
                $pId = (int) $acc->parent_id;
                $childCounts[$pId] = ($childCounts[$pId] ?? 0) + 1;
            }
        }

        // 1. Opening
        $startYear = Carbon::parse($startDate)->year;
        $hasOpeningBalance = DB::table('journal_entries')
            ->where('status', 'posted')
            ->where(function ($q) use ($startYear) {
                $q->where('source_type', 'opening_balance')
                    ->orWhere('entry_type', 'opening_balance')
                    ->orWhere('entry_number', 'like', "SA-{$startYear}%");
            })
            ->whereYear('entry_date', $startYear)
            ->exists();

        $opResults = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->when($hasOpeningBalance, function ($q) use ($startYear, $startDate) {
                $q->where(function ($sub) use ($startYear, $startDate) {
                    $sub->where(function ($obQ) use ($startYear) {
                        $obQ->where('journal_entries.entry_number', 'like', "SA-{$startYear}%")
                            ->orWhere(function ($w) use ($startYear) {
                                $w->whereYear('journal_entries.entry_date', $startYear)
                                    ->where(function ($types) {
                                        $types->where('journal_entries.source_type', 'opening_balance')
                                            ->orWhere('journal_entries.entry_type', 'opening_balance');
                                    });
                            });
                    })->orWhere(function ($priorQ) use ($startYear, $startDate) {
                        $priorQ->where('journal_entries.entry_number', 'not like', 'SA-%')
                            ->where('journal_entries.entry_type', '!=', 'opening_balance')
                            ->where('journal_entries.source_type', '!=', 'opening_balance')
                            ->where('journal_entries.entry_date', '>=', "{$startYear}-01-01")
                            ->where('journal_entries.entry_date', '<', $startDate);
                    });
                });
            }, function ($q) use ($startDate) {
                $q->where(function ($query) use ($startDate) {
                    $query->where('journal_entries.entry_type', 'opening_balance')
                        ->orWhere('journal_entries.source_type', 'opening_balance')
                        ->orWhere('journal_entries.entry_number', 'like', 'SA%')
                        ->orWhere('journal_entries.entry_date', '<', $startDate);
                });
            })
            ->when(! empty($allowedUnitIds), fn ($q) => $q->whereIn('journal_lines.unit_id', $allowedUnitIds))
            ->when($unitFilter !== 'all', fn ($q) => $q->where('journal_lines.unit_id', $unitFilter))
            ->select('journal_lines.account_id')
            ->selectRaw('SUM(journal_lines.debit) as total_debit, SUM(journal_lines.credit) as total_credit')
            ->groupBy('journal_lines.account_id')
            ->get();

        foreach ($opResults as $res) {
            $accId = (int) $res->account_id;
            if (isset($accountData[$accId])) {
                $acc = $accountData[$accId]['account'];
                $d = (float) $res->total_debit;
                $c = (float) $res->total_credit;
                $openings[$accId] = $acc->normal_balance === 'debit' ? ($d - $c) : ($c - $d);
            }
        }

        // 2. Regular Mutations
        $genResults = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_type', '!=', 'adjustment')
            ->where('journal_entries.entry_type', '!=', 'opening_balance')
            ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
            ->when(! empty($allowedUnitIds), fn ($q) => $q->whereIn('journal_lines.unit_id', $allowedUnitIds))
            ->when($unitFilter !== 'all', fn ($q) => $q->where('journal_lines.unit_id', $unitFilter))
            ->select('journal_lines.account_id')
            ->selectRaw('SUM(journal_lines.debit) as total_debit, SUM(journal_lines.credit) as total_credit')
            ->groupBy('journal_lines.account_id')
            ->get();

        foreach ($genResults as $res) {
            $accId = (int) $res->account_id;
            if (isset($accountData[$accId])) {
                $debits[$accId] = (float) $res->total_debit;
                $credits[$accId] = (float) $res->total_credit;
            }
        }

        // 3. Adjustment Mutations
        $adjResults = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_type', 'adjustment')
            ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
            ->when(! empty($allowedUnitIds), fn ($q) => $q->whereIn('journal_lines.unit_id', $allowedUnitIds))
            ->when($unitFilter !== 'all', fn ($q) => $q->where('journal_lines.unit_id', $unitFilter))
            ->select('journal_lines.account_id')
            ->selectRaw('SUM(journal_lines.debit) as total_debit, SUM(journal_lines.credit) as total_credit')
            ->groupBy('journal_lines.account_id')
            ->get();

        foreach ($adjResults as $res) {
            $accId = (int) $res->account_id;
            if (isset($accountData[$accId])) {
                $adjDebits[$accId] = (float) $res->total_debit;
                $adjCredits[$accId] = (float) $res->total_credit;
            }
        }

        // Rollup
        $maxLevel = collect($accountData)->max('level') ?: 4;
        for ($lvl = $maxLevel; $lvl > 1; $lvl--) {
            foreach ($accountData as $id => $item) {
                if ($item['level'] == $lvl && $item['parent_id'] && isset($accountData[$item['parent_id']])) {
                    $pId = $item['parent_id'];
                    $openings[$pId] += $openings[$id];
                    $debits[$pId] += $debits[$id];
                    $credits[$pId] += $credits[$id];
                    $adjDebits[$pId] += $adjDebits[$id];
                    $adjCredits[$pId] += $adjCredits[$id];
                }
            }
        }

        $rows = [];
        $totTbDebit = 0.0;
        $totTbCredit = 0.0;
        $totAdjDebit = 0.0;
        $totAdjCredit = 0.0;
        $totAtbDebit = 0.0;
        $totAtbCredit = 0.0;
        $totIsDebit = 0.0;
        $totIsCredit = 0.0;
        $totBsDebit = 0.0;
        $totBsCredit = 0.0;

        foreach ($accountData as $id => $item) {
            $acc = $item['account'];
            $level = $item['level'];
            $hasChildren = ($childCounts[$id] ?? 0) > 0;
            $opBal = $openings[$id] ?? 0.0;
            $debMut = $debits[$id] ?? 0.0;
            $credMut = $credits[$id] ?? 0.0;
            $adjDeb = $adjDebits[$id] ?? 0.0;
            $adjCred = $adjCredits[$id] ?? 0.0;

            if ($acc->normal_balance === 'debit') {
                $tbBal = $opBal + ($debMut - $credMut);
                $tbDebit = $tbBal;
                $tbCredit = 0.0;

                $atbBal = $tbBal + ($adjDeb - $adjCred);
                $atbDebit = $atbBal;
                $atbCredit = 0.0;
            } else {
                $tbBal = $opBal + ($credMut - $debMut);
                $tbCredit = $tbBal;
                $tbDebit = 0.0;

                $atbBal = $tbBal + ($adjCred - $adjDeb);
                $atbCredit = $atbBal;
                $atbDebit = 0.0;
            }

            $isDebit = 0.0;
            $isCredit = 0.0;
            $bsDebit = 0.0;
            $bsCredit = 0.0;

            if ($acc->report_type === 'laba_rugi') {
                $isDebit = $atbDebit;
                $isCredit = $atbCredit;
            } else {
                $bsDebit = $atbDebit;
                $bsCredit = $atbCredit;
            }

            if (! $hasChildren) {
                $totTbDebit += $tbDebit;
                $totTbCredit += $tbCredit;
                $totAdjDebit += $adjDeb;
                $totAdjCredit += $adjCred;
                $totAtbDebit += $atbDebit;
                $totAtbCredit += $atbCredit;
                $totIsDebit += $isDebit;
                $totIsCredit += $isCredit;
                $totBsDebit += $bsDebit;
                $totBsCredit += $bsCredit;
            }

            if ($tbDebit == 0 && $tbCredit == 0 && $adjDeb == 0 && $adjCred == 0 && $atbDebit == 0 && $atbCredit == 0 && ! $hasChildren) {
                continue;
            }

            $rows[] = [
                'account' => $acc,
                'level' => $level,
                'has_children' => $hasChildren,
                'tb_debit' => $tbDebit,
                'tb_credit' => $tbCredit,
                'adj_debit' => $adjDeb,
                'adj_credit' => $adjCred,
                'atb_debit' => $atbDebit,
                'atb_credit' => $atbCredit,
                'is_debit' => $isDebit,
                'is_credit' => $isCredit,
                'bs_debit' => $bsDebit,
                'bs_credit' => $bsCredit,
            ];
        }

        $company = Company::first();
        $targetUnit = $unitFilter !== 'all' ? Unit::find($unitFilter) : null;
        $unitName = $unitFilter === 'all' ? 'Konsolidasi (Seluruh Unit)' : ($targetUnit ? $targetUnit->name : 'Unit');

        return [
            'company' => $company,
            'unitName' => $unitName,
            'startDate' => Carbon::parse($startDate)->isoFormat('D MMMM Y'),
            'endDate' => Carbon::parse($endDate)->isoFormat('D MMMM Y'),
            'printedAt' => Carbon::now()->isoFormat('D MMMM Y HH:mm'),
            'printedBy' => $user ? $user->name : 'Administrator',
            'rows' => $rows,
            'totTbDebit' => $totTbDebit,
            'totTbCredit' => $totTbCredit,
            'totAdjDebit' => $totAdjDebit,
            'totAdjCredit' => $totAdjCredit,
            'totAtbDebit' => $totAtbDebit,
            'totAtbCredit' => $totAtbCredit,
            'totIsDebit' => $totIsDebit,
            'totIsCredit' => $totIsCredit,
            'totBsDebit' => $totBsDebit,
            'totBsCredit' => $totBsCredit,
            'netProfitLoss' => $totIsCredit - $totIsDebit,
        ];
    }

    /**
     * Render Neraca Lajur 10 Kolom (Worksheet) ke format PDF (Ukuran A3 Landscape).
     */
    public function renderWorksheetPdf(string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): DomPdfWrapper
    {
        $data = $this->getWorksheetData($startDate, $endDate, $unitFilter, $user);

        return Pdf::loadView('pdf.reports.worksheet', $data)
            ->setPaper('a3', 'landscape')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }

    /**
     * Ambil data Laporan Saldo Awal (Opening Balance).
     *
     * @return array<string, mixed>
     */
    public function getOpeningBalanceData(?int $periodId = null, string $unitFilter = 'all', ?User $user = null, string $mode = 'balance_sheet'): array
    {
        $user = $user ?? auth()->user();
        $allowedUnitIds = $user ? $user->allowedUnitIds() : [];

        $periods = AccountingPeriod::orderBy('start_date', 'asc')->get();
        $selectedPeriod = $periodId ? $periods->firstWhere('id', $periodId) : $periods->first();
        if (! $selectedPeriod) {
            $selectedPeriod = $periods->first();
        }

        $query = Account::active();
        if ($mode === 'balance_sheet') {
            $query->where('report_type', 'neraca');
        }
        $accounts = $query->orderBy('code', 'asc')->get();

        // Cek apakah ada Jurnal Saldo Awal (SA) untuk tahun periode terpilih
        $selectedYear = $selectedPeriod ? Carbon::parse($selectedPeriod->start_date)->year : null;
        $hasOpeningBalance = false;
        if ($selectedYear) {
            $hasOpeningBalance = DB::table('journal_entries')
                ->where('status', 'posted')
                ->where(function ($q) use ($selectedYear) {
                    $q->where('source_type', 'opening_balance')
                        ->orWhere('entry_type', 'opening_balance')
                        ->orWhere('entry_number', 'like', "SA-{$selectedYear}%");
                })
                ->whereYear('entry_date', $selectedYear)
                ->exists();
        }

        // Hitung laba kumulatif periode lalu untuk mode neraca murni (hanya jika belum ada jurnal SA rollover)
        $priorNetProfit = 0.0;
        if ($mode === 'balance_sheet' && $selectedPeriod && ! $hasOpeningBalance) {
            $nominalAccounts = Account::active()
                ->where('report_type', 'laba_rugi')
                ->where('is_group', false)
                ->get();

            $totRev = 0.0;
            $totExp = 0.0;

            foreach ($nominalAccounts as $nAcc) {
                $nomQuery = JournalLine::where('account_id', $nAcc->id)
                    ->whereHas('journalEntry', function ($q) use ($selectedPeriod) {
                        $q->where('status', 'posted')
                            ->where('entry_date', '<', $selectedPeriod->start_date);
                    })
                    ->when(! empty($allowedUnitIds), fn ($q) => $q->whereIn('unit_id', $allowedUnitIds));

                if ($unitFilter !== 'all') {
                    $nomQuery->where('unit_id', $unitFilter);
                }

                $nomTotals = $nomQuery->selectRaw('SUM(debit) as tot_debit, SUM(credit) as tot_credit')->first();
                $nD = (float) ($nomTotals->tot_debit ?? 0);
                $nC = (float) ($nomTotals->tot_credit ?? 0);

                if ($nAcc->normal_balance === 'credit') {
                    $totRev += ($nC - $nD);
                } else {
                    $totExp += ($nD - $nC);
                }
            }
            $priorNetProfit = $totRev - $totExp;
        }

        $lines = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        $retainedEarningsAccount = Account::where('report_type', 'neraca')
            ->where(function ($q) {
                $q->where('code', '31.02')
                    ->orWhere('name', 'like', '%Saldo Laba%')
                    ->orWhere('name', 'like', '%Laba Ditahan%');
            })
            ->first();

        foreach ($accounts as $acc) {
            $mutQuery = JournalLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($selectedPeriod, $hasOpeningBalance, $selectedYear) {
                    $q->where('status', 'posted');

                    if ($hasOpeningBalance && $selectedPeriod) {
                        $q->where(function ($subQ) use ($selectedPeriod, $selectedYear) {
                            $subQ->where(function ($obQ) use ($selectedYear) {
                                $obQ->where('entry_number', 'like', "SA-{$selectedYear}%")
                                    ->orWhere('entry_type', 'opening_balance')
                                    ->orWhere('source_type', 'opening_balance');
                            })->orWhere(function ($priorQ) use ($selectedPeriod, $selectedYear) {
                                $priorQ->where('entry_number', 'not like', 'SA-%')
                                    ->where('entry_type', '!=', 'opening_balance')
                                    ->where('source_type', '!=', 'opening_balance')
                                    ->where('entry_date', '>=', "{$selectedYear}-01-01")
                                    ->where('entry_date', '<', $selectedPeriod->start_date);
                            });
                        });
                    } else {
                        $q->where(function ($subQ) use ($selectedPeriod) {
                            $subQ->where(function ($obQ) use ($selectedPeriod) {
                                $obQ->where(function ($types) {
                                    $types->where('entry_number', 'like', 'SA-%')
                                        ->orWhere('entry_type', 'opening_balance')
                                        ->orWhere('source_type', 'opening_balance');
                                })->where('entry_date', '<=', $selectedPeriod ? $selectedPeriod->end_date : now());
                            })->orWhere(function ($regQ) use ($selectedPeriod) {
                                $regQ->where('entry_number', 'not like', 'SA-%')
                                    ->where('entry_type', '!=', 'opening_balance')
                                    ->where('source_type', '!=', 'opening_balance')
                                    ->where('entry_date', '<', $selectedPeriod ? $selectedPeriod->start_date : now());
                            });
                        });
                    }
                })
                ->when(! empty($allowedUnitIds), fn ($q) => $q->whereIn('unit_id', $allowedUnitIds));

            if ($unitFilter !== 'all') {
                $mutQuery->where('unit_id', $unitFilter);
            }

            $totals = $mutQuery->selectRaw('SUM(debit) as tot_debit, SUM(credit) as tot_credit')->first();
            $d = (float) ($totals->tot_debit ?? 0);
            $c = (float) ($totals->tot_credit ?? 0);

            $extraCredit = 0.0;
            if ($mode === 'balance_sheet' && $retainedEarningsAccount && $acc->id === $retainedEarningsAccount->id) {
                $extraCredit = $priorNetProfit;
            }

            if ($d == 0 && $c == 0 && (float) $acc->opening_balance == 0 && $extraCredit == 0) {
                continue;
            }

            $debitVal = 0.0;
            $creditVal = 0.0;

            if ($acc->normal_balance === 'debit') {
                $debitVal = ($d - $c);
                $totalDebit += $debitVal;
            } else {
                $creditVal = ($c - $d) + $extraCredit;
                $totalCredit += $creditVal;
            }

            if ($debitVal == 0 && $creditVal == 0) {
                continue;
            }

            $lines[] = [
                'account' => $acc,
                'debit' => $debitVal,
                'credit' => $creditVal,
            ];
        }

        $batchDiff = abs($totalDebit - $totalCredit);
        $isBalanced = $batchDiff <= 2.00;
        if ($mode === 'balance_sheet' && $isBalanced && $batchDiff > 0) {
            $totalCredit = $totalDebit;
        }

        $company = Company::first();
        $targetUnit = $unitFilter !== 'all' ? Unit::find($unitFilter) : null;
        $unitName = $unitFilter === 'all' ? 'Konsolidasi (Seluruh Unit)' : ($targetUnit ? $targetUnit->name : 'Unit');
        $periodName = $selectedPeriod ? Carbon::parse($selectedPeriod->start_date)->isoFormat('MMMM Y') : 'Tahun 2025';

        return [
            'company' => $company,
            'unitName' => $unitName,
            'periodName' => $periodName,
            'mode' => $mode,
            'printedAt' => Carbon::now()->isoFormat('D MMMM Y HH:mm'),
            'printedBy' => $user ? $user->name : 'Administrator',
            'lines' => $lines,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'isBalanced' => $isBalanced,
        ];
    }

    /**
     * Render Laporan Saldo Awal (Opening Balance) ke format PDF.
     */
    public function renderOpeningBalancePdf(?int $periodId = null, string $unitFilter = 'all', ?User $user = null, string $mode = 'balance_sheet'): DomPdfWrapper
    {
        $data = $this->getOpeningBalanceData($periodId, $unitFilter, $user, $mode);

        return Pdf::loadView('pdf.reports.opening-balance', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }

    /**
     * Render Laporan Aging Hutang / Piutang ke format PDF (Landscape).
     */
    public function renderAgingPdf(string $type, string $asOfDate, string $unitFilter = 'all', ?User $user = null, ?string $startDate = null): DomPdfWrapper
    {
        $reportService = new AgingReportService;
        $reportData = $reportService->getAgingReport($type, $asOfDate, $unitFilter, true, $startDate);

        $company = Company::first();
        $targetUnit = $unitFilter !== 'all' ? Unit::find($unitFilter) : null;
        $unitName = $unitFilter === 'all' ? 'Konsolidasi (Seluruh Unit)' : ($targetUnit ? $targetUnit->name : 'Unit');

        $viewData = array_merge($reportData, [
            'asOfDate' => $asOfDate,
            'company' => $company,
            'unitName' => $unitName,
            'printedAt' => Carbon::now()->isoFormat('D MMMM Y HH:mm'),
            'printedBy' => $user ? $user->name : 'Administrator',
        ]);

        return Pdf::loadView('pdf.reports.aging', $viewData)
            ->setPaper('a4', 'landscape')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }

    /**
     * Ambil data Bukti Memorial / Voucher Jurnal Umum (Journal Voucher).
     *
     * @return array<string, mixed>
     */
    public function getJournalVoucherData(JournalEntry $entry, ?User $user = null): array
    {
        $user = $user ?? auth()->user();
        $company = $entry->company ?? Company::first();

        $entry->loadMissing([
            'lines.account',
            'lines.unit',
            'journalType',
            'postedBy',
            'period',
        ]);

        // Cari nama unit dominan dari baris jurnal jika ada
        $firstLineUnit = $entry->lines->first(fn ($l) => $l->unit !== null)?->unit;
        $unitName = $firstLineUnit ? "{$firstLineUnit->code} - {$firstLineUnit->name}" : 'Konsolidasi / Kantor Pusat';

        return [
            'company' => $company,
            'entry' => $entry,
            'unitName' => $unitName,
            'printedAt' => Carbon::now()->isoFormat('D MMMM Y HH:mm'),
            'printedBy' => $user ? $user->name : 'Staff Akuntansi',
        ];
    }

    /**
     * Render Bukti Memorial / Voucher Jurnal Umum ke format PDF (Ukuran A4 Portrait).
     */
    public function renderJournalVoucherPdf(JournalEntry|int $entry, ?User $user = null): DomPdfWrapper
    {
        $journal = $entry instanceof JournalEntry
            ? $entry
            : JournalEntry::with(['lines.account', 'lines.unit', 'journalType', 'postedBy', 'company', 'period'])->findOrFail($entry);

        $data = $this->getJournalVoucherData($journal, $user);

        return Pdf::loadView('pdf.reports.journal-voucher', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }

    /**
     * Ambil data Rekapitulasi Daftar Jurnal Transaksi (Journal Register).
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getJournalRegisterData(array $filters = [], ?User $user = null, ?int $limit = null): array
    {
        $user = $user ?? auth()->user();
        $allowedUnitIds = $user ? $user->allowedUnitIds() : [];

        $search = $filters['search'] ?? '';
        $statusFilter = $filters['status'] ?? 'all';
        $unitFilter = $filters['unit'] ?? 'all';
        $startDate = $filters['start_date'] ?? '';
        $endDate = $filters['end_date'] ?? '';

        $query = JournalEntry::with(['lines.account', 'lines.unit', 'journalType', 'postedBy', 'period'])
            ->when($search !== '', fn ($q) => $q->where(fn ($sq) => $sq->where('entry_number', 'like', "%{$search}%")
                ->orWhere('document_number', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->when($statusFilter === 'draft', fn ($q) => $q->where('status', 'draft'))
            ->when($statusFilter === 'posted', fn ($q) => $q->where('status', 'posted'))
            ->when($statusFilter === 'reversed', fn ($q) => $q->where('status', 'reversed'))
            ->when($statusFilter === 'all', fn ($q) => $q->whereIn('status', ['posted', 'reversed']))
            ->when(! empty($allowedUnitIds), fn ($q) => $q->whereHas('lines', fn ($lq) => $lq->whereIn('unit_id', $allowedUnitIds)))
            ->when($unitFilter !== 'all', fn ($q) => $q->whereHas('lines', fn ($lq) => $lq->where('unit_id', $unitFilter)))
            ->when(! empty($startDate), fn ($q) => $q->whereDate('entry_date', '>=', $startDate))
            ->when(! empty($endDate), fn ($q) => $q->whereDate('entry_date', '<=', $endDate))
            ->orderBy('entry_date', 'asc')
            ->orderBy('id', 'asc');

        // Menghindari memory exhausted pada Dompdf jika dataset jurnal sangat besar
        $totalCount = (clone $query)->count();
        $isTruncated = false;

        if ($limit !== null && $totalCount > $limit) {
            $isTruncated = true;
            $journals = $query->limit($limit)->get();
        } else {
            $journals = $query->get();
        }

        $totalDebit = 0.0;
        $totalCredit = 0.0;
        foreach ($journals as $j) {
            $totalDebit += (float) $j->total_debit;
            $totalCredit += (float) $j->total_credit;
        }

        $company = Company::first();
        $targetUnit = $unitFilter !== 'all' ? Unit::find($unitFilter) : null;
        $unitName = $unitFilter === 'all' ? 'Konsolidasi (Seluruh Unit)' : ($targetUnit ? "{$targetUnit->code} - {$targetUnit->name}" : 'Unit');

        $startFormatted = ! empty($startDate) ? Carbon::parse($startDate)->isoFormat('D MMMM Y') : 'Awal Pembukuan';
        $endFormatted = ! empty($endDate) ? Carbon::parse($endDate)->isoFormat('D MMMM Y') : Carbon::now()->isoFormat('D MMMM Y');

        $statusLabelMap = [
            'all' => 'Semua (Posted & Reversed)',
            'posted' => 'Posted (Dibukukan)',
            'draft' => 'Draft (Menunggu Approval)',
            'reversed' => 'Reversed (Dibalikkan)',
        ];

        return [
            'company' => $company,
            'journals' => $journals,
            'unitName' => $unitName,
            'startDate' => $startFormatted,
            'endDate' => $endFormatted,
            'statusLabel' => $statusLabelMap[$statusFilter] ?? strtoupper($statusFilter),
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'isTruncated' => $isTruncated,
            'totalCount' => $totalCount,
            'limit' => $limit,
            'printedAt' => Carbon::now()->isoFormat('D MMMM Y HH:mm'),
            'printedBy' => $user ? $user->name : 'Staff Akuntansi',
        ];
    }

    /**
     * Render Rekapitulasi Daftar Jurnal Transaksi ke format PDF (Ukuran A4 Landscape).
     *
     * @param  array<string, mixed>  $filters
     */
    public function renderJournalRegisterPdf(array $filters = [], ?User $user = null): DomPdfWrapper
    {
        // Tetapkan limit aman untuk PDF rendering agar tidak kehabisan memori server
        $maxPdfEntries = 150;
        $data = $this->getJournalRegisterData($filters, $user, $maxPdfEntries);

        return Pdf::loadView('pdf.reports.journal-register', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Helvetica',
            ]);
    }
}
