<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApArInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'unit_id',
        'account_id',
        'journal_line_id',
        'type',
        'invoice_number',
        'invoice_date',
        'due_date',
        'original_amount',
        'partner_name',
        'notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'original_amount' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function journalLine(): BelongsTo
    {
        return $this->belongsTo(JournalLine::class, 'journal_line_id');
    }

    public function journalLines(): BelongsToMany
    {
        return $this->belongsToMany(
            JournalLine::class,
            'ap_ar_invoice_journal_lines',
            'ap_ar_invoice_id',
            'journal_line_id'
        )->withPivot('allocated_amount')->withTimestamps();
    }

    public function journalLineAllocations(): HasMany
    {
        return $this->hasMany(ApArInvoiceJournalLine::class, 'ap_ar_invoice_id');
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(ApArSettlement::class, 'ap_ar_invoice_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeReceivables(Builder $query): Builder
    {
        return $query->where('type', 'receivable');
    }

    public function scopePayables(Builder $query): Builder
    {
        return $query->where('type', 'payable');
    }

    public function getSettledAmountAttribute(): float
    {
        return (float) $this->settlements()->sum('settled_amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0.0, (float) $this->original_amount - $this->settled_amount);
    }
}
