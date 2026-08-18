<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_code',
        'file_name',
        'file_hash',
        'total_rows',
        'valid_rows',
        'error_rows',
        'status',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ImportFile::class, 'import_batch_id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ImportRow::class, 'import_batch_id')->orderBy('row_index');
    }
}
