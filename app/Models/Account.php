<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'parent_id',
        'code',
        'name',
        'type',
        'normal_balance',
        'report_type',
        'opening_balance',
        'is_group',
        'is_active',
        'level',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_group' => 'boolean',
        'is_active' => 'boolean',
        'level' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id')->orderBy('code', 'asc');
    }

    public function scopeGroup(Builder $query): Builder
    {
        return $query->where('is_group', true);
    }

    public function scopePosting(Builder $query): Builder
    {
        return $query->where('is_group', false);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getAllDescendantIds(): array
    {
        $ids = [];
        foreach ($this->children as $child) {
            $ids[] = $child->id;
            if ($child->is_group) {
                $ids = array_merge($ids, $child->getAllDescendantIds());
            }
        }

        return $ids;
    }
}
