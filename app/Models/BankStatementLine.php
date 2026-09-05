<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BankStatementLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_statement_id',
        'transaction_date',
        'transaction_time',
        'description',
        'debit',
        'credit',
        'balance',
        'teller_id',
        'reference_number',
        'match_status',
        'matched_journal_line_id',
        'matched_at',
        'matched_by',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
            'balance' => 'decimal:2',
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
     * @return BelongsTo<JournalLine, $this>
     */
    public function matchedJournalLine(): BelongsTo
    {
        return $this->belongsTo(JournalLine::class, 'matched_journal_line_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function matchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_by');
    }

    /**
     * @return BelongsToMany<BankReconciliationMatchGroup, $this>
     */
    public function matchGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            BankReconciliationMatchGroup::class,
            'bank_statement_line_matches',
            'bank_statement_line_id',
            'match_group_id'
        )->withTimestamps();
    }
}
