<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_batch_id',
        'original_filename',
        'stored_path',
        'mime_type',
        'file_size_bytes',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }
}
