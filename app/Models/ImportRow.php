<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_batch_id',
        'row_index',
        'raw_data',
        'entry_date',
        'document_number',
        'description',
        'raw_account_code',
        'account_id',
        'unit_id',
        'debit',
        'credit',
        'source_block_key',
        'validation_status',
        'validation_messages',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'validation_messages' => 'array',
        'entry_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
