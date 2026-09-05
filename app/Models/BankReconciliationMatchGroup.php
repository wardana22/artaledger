<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BankReconciliationMatchGroup extends Model
{
    protected $fillable = [
        'bank_statement_id',
        'match_code',
        'match_type',
        'total_bank_amount',
        'total_book_amount',
        'difference',
        'notes',
        'matched_by',
        'matched_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_bank_amount' => 'decimal:2',
            'total_book_amount' => 'decimal:2',
            'difference' => 'decimal:2',
            'matched_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<BankStatement, $this>
     */
    public function bankStatement(): BelongsTo
    {
        return $this->belongsTo(BankStatement::class);
    }

    /**
     * @return BelongsToMany<BankStatementLine, $this>
     */
    public function statementLines(): BelongsToMany
    {
        return $this->belongsToMany(
            BankStatementLine::class,
            'bank_statement_line_matches',
            'match_group_id',
            'bank_statement_line_id'
        )->withTimestamps();
    }

    /**
     * @return BelongsToMany<JournalLine, $this>
     */
    public function journalLines(): BelongsToMany
    {
        return $this->belongsToMany(
            JournalLine::class,
            'bank_journal_line_matches',
            'match_group_id',
            'journal_line_id'
        )->withTimestamps();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function matchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_by');
    }
}
