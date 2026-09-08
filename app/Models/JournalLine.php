<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_entry_id',
        'line_no',
        'account_id',
        'unit_id',
        'description',
        'debit',
        'credit',
        'source_import_row_id',
    ];

    protected $casts = [
        'line_no' => 'integer',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * @return BelongsToMany<BankReconciliationMatchGroup, $this>
     */
    public function bankReconciliationGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            BankReconciliationMatchGroup::class,
            'bank_journal_line_matches',
            'journal_line_id',
            'match_group_id'
        )->withTimestamps();
    }

    public function apArInvoices(): HasMany
    {
        return $this->hasMany(ApArInvoice::class, 'journal_line_id');
    }

    public function apArSettlements(): HasMany
    {
        return $this->hasMany(ApArSettlement::class, 'payment_journal_line_id');
    }
}
