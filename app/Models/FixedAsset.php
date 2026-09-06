<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FixedAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'unit_id',
        'asset_category_id',
        'asset_code',
        'name',
        'serial_number',
        'location',
        'person_in_charge',
        'acquisition_date',
        'acquisition_cost',
        'salvage_value',
        'useful_life_months',
        'depreciation_method',
        'monthly_depreciation_amount',
        'accumulated_depreciation',
        'book_value',
        'start_depreciation_date',
        'last_depreciated_period',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'start_depreciation_date' => 'date',
        'acquisition_cost' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'useful_life_months' => 'integer',
        'monthly_depreciation_amount' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'book_value' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function depreciations(): HasMany
    {
        return $this->hasMany(AssetDepreciation::class, 'fixed_asset_id')->orderBy('period');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeFullyDepreciated(Builder $query): Builder
    {
        return $query->where('status', 'fully_depreciated');
    }

    public function scopeDisposed(Builder $query): Builder
    {
        return $query->where('status', 'disposed');
    }
}
