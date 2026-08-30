<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class AccountGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'description',
        'color_theme',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(AccountGroupMember::class, 'account_group_id');
    }

    /**
     * Calculate total live balance of all accounts belonging to this group.
     */
    public function calculateTotalBalance(?int $unitId = null, int $month = 0, int $year = 0): float
    {
        $members = $this->members;
        if ($members->isEmpty()) {
            return 0.0;
        }

        $query = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts', 'journal_lines.account_id', '=', 'accounts.id')
            ->where('journal_entries.status', 'posted');

        if ($this->company_id) {
            $query->where('accounts.company_id', $this->company_id);
        }

        if ($unitId) {
            $query->where('journal_lines.unit_id', $unitId);
        }

        // Apply member filters OR condition
        $query->where(function ($groupQuery) use ($members) {
            foreach ($members as $m) {
                $groupQuery->orWhere(function ($subQ) use ($m) {
                    if ($m->account_id) {
                        $subQ->where('journal_lines.account_id', $m->account_id);
                    } elseif ($m->account_prefix) {
                        $subQ->where('accounts.code', 'like', $m->account_prefix.'%');
                    } elseif ($m->account_type) {
                        $subQ->where('accounts.type', $m->account_type);
                    }
                });
            }
        });

        if ($year > 0) {
            $query->whereYear('journal_entries.entry_date', $year);
            if ($month > 0) {
                $query->whereMonth('journal_entries.entry_date', $month);
            }
        }

        $totalDebit = (float) (clone $query)->sum('journal_lines.debit');
        $totalCredit = (float) (clone $query)->sum('journal_lines.credit');

        // Check if group is mostly revenue/credit or asset/expense
        $firstMember = $members->first();
        if ($firstMember && ($firstMember->account_prefix === '4' || $firstMember->account_prefix === '2' || $firstMember->account_prefix === '3' || $firstMember->account_type === 'PENDAPATAN')) {
            return $totalCredit - $totalDebit;
        }

        return $totalDebit - $totalCredit;
    }
}
