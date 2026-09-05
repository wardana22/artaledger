<?php

namespace App\Domain\Accounting\Services;

use App\Models\Account;
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
     * Render Laporan Laba Rugi (Profit & Loss) ke format PDF.
     */
    public function renderProfitLossPdf(string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): DomPdfWrapper
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

        // 1. Fetch Mutations for Period
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

        // 2. Hierarchical Rollup
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

        // 3. Totals
        $totalRevenue = 0.0;
        $totalHpp = 0.0;
        $totalOperatingExpenses = 0.0;
        $otherRevenue = 0.0;
        $otherExpense = 0.0;
        $taxExpense = 0.0;

        foreach ($accountData as $id => $item) {
            $acc = $item['account'];
            $amt = $amounts[$id] ?? 0.0;
            if ($item['level'] === 1) {
                $codePrefix = substr($acc->code, 0, 1);
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
                } elseif ($codePrefix === '9') {
                    $taxExpense += $amt;
                }
            }
        }

        $grossProfit = $totalRevenue - $totalHpp;
        $operatingProfit = $grossProfit - $totalOperatingExpenses;
        $profitBeforeTax = $operatingProfit + $otherRevenue - $otherExpense;
        $netProfit = $profitBeforeTax - $taxExpense;

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

        $data = [
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
            'netProfit' => $netProfit,
        ];

        return Pdf::loadView('pdf.reports.profit-loss', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }

    /**
     * Render Laporan Neraca (Balance Sheet) ke format PDF.
     */
    public function renderBalanceSheetPdf(string $asOfDate, string $unitFilter = 'all', ?User $user = null): DomPdfWrapper
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

        $mutResults = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_date', '<=', $asOfDate)
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

        $company = Company::first();
        $targetUnit = $unitFilter !== 'all' ? Unit::find($unitFilter) : null;
        $unitName = $unitFilter === 'all' ? 'Konsolidasi (Seluruh Unit)' : ($targetUnit ? $targetUnit->name : 'Unit');

        $data = [
            'company' => $company,
            'unitName' => $unitName,
            'asOfDate' => Carbon::parse($asOfDate)->isoFormat('D MMMM Y'),
            'printedAt' => Carbon::now()->isoFormat('D MMMM Y HH:mm'),
            'printedBy' => $user ? $user->name : 'Administrator',
            'assetRows' => $assetRows,
            'liabilityRows' => $liabilityRows,
            'equityRows' => $equityRows,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity,
            'totalLiabilitiesAndEquity' => $totalLiabilities + $totalEquity,
            'isBalanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
        ];

        return Pdf::loadView('pdf.reports.balance-sheet', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }

    /**
     * Render Neraca Saldo (Trial Balance) ke format PDF.
     */
    public function renderTrialBalancePdf(string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): DomPdfWrapper
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

        // Opening Balances
        $opResults = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->where(function ($query) use ($startDate) {
                $query->where('journal_entries.entry_type', 'opening_balance')
                    ->orWhere('journal_entries.entry_date', '<', $startDate);
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

        // Period Mutations
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

        // Rollup
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

        $data = [
            'company' => $company,
            'unitName' => $unitName,
            'startDate' => Carbon::parse($startDate)->isoFormat('D MMMM Y'),
            'endDate' => Carbon::parse($endDate)->isoFormat('D MMMM Y'),
            'printedAt' => Carbon::now()->isoFormat('D MMMM Y HH:mm'),
            'printedBy' => $user ? $user->name : 'Administrator',
            'rows' => $rows,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'isBalanced' => abs($totalDebit - $totalCredit) < 0.01,
        ];

        return Pdf::loadView('pdf.reports.trial-balance', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }

    /**
     * Render Buku Besar (General Ledger) ke format PDF.
     */
    public function renderGeneralLedgerPdf(int $accountId, string $startDate, string $endDate, string $unitFilter = 'all', ?User $user = null): DomPdfWrapper
    {
        $user = $user ?? auth()->user();
        $allowedUnitIds = $user ? $user->allowedUnitIds() : [];

        $account = Account::findOrFail($accountId);
        $descendantIds = $account->is_group ? $account->getAllDescendantIds() : [$account->id];
        if (empty($descendantIds)) {
            $descendantIds = [$account->id];
        }

        // Opening Balance
        $opQuery = JournalLine::whereHas('journalEntry', function ($q) use ($startDate) {
            $q->where('status', 'posted')
                ->where(function ($sub) use ($startDate) {
                    $sub->where('entry_type', 'opening_balance')
                        ->orWhere('entry_date', '<', $startDate);
                });
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

        // Lines for Period
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

        $data = [
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

        return Pdf::loadView('pdf.reports.general-ledger', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }
}
