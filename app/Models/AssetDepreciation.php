<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDepreciation extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixed_asset_id',
        'journal_entry_id',
        'period',
        'depreciation_date',
        'amount',
        'accumulated_to_date',
        'book_value_after',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'depreciation_date' => 'date',
        'amount' => 'decimal:2',
        'accumulated_to_date' => 'decimal:2',
        'book_value_after' => 'decimal:2',
        'posted_at' => 'datetime',
    ];

    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
