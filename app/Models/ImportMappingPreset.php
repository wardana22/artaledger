<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportMappingPreset extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'sheet_name',
        'start_row',
        'mapping_config',
    ];

    protected $casts = [
        'start_row' => 'integer',
        'mapping_config' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
