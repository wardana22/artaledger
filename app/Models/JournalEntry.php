<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'company_id',
        'period_id',
        'entry_number',
        'entry_date',
        'document_number',
        'description',
        'source_type',
        'source_file_id',
        'import_batch_id',
        'source_block_key',
        'status',
        'entry_type',
        'journal_type_id',
        'is_auto_document_number',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'posted_at' => 'datetime',
        'is_auto_document_number' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class, 'period_id');
    }

    public function journalType(): BelongsTo
    {
        return $this->belongsTo(JournalType::class, 'journal_type_id');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class, 'journal_entry_id')->orderBy('line_no');
    }

    public function scopePosted(Builder $query): Builder
    {
        return $query->where('status', 'posted');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeGeneral(Builder $query): Builder
    {
        return $query->where('entry_type', 'general');
    }

    public function scopeAdjustment(Builder $query): Builder
    {
        return $query->where('entry_type', 'adjustment');
    }

    public function scopeClosing(Builder $query): Builder
    {
        return $query->where('entry_type', 'closing');
    }

    public function getTotalDebitAttribute(): float
    {
        return (float) $this->lines->sum('debit');
    }

    public function getTotalCreditAttribute(): float
    {
        return (float) $this->lines->sum('credit');
    }

    public function getIsBalancedAttribute(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }
}
