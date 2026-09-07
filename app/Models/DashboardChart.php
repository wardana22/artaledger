<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardChart extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'type',
        'width',
        'months',
        'is_visible',
        'order',
        'metric_ids',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'months' => 'integer',
        'order' => 'integer',
        'metric_ids' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
