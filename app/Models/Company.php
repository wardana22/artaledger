<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'app_name',
        'logo_path',
        'address',
        'phone',
        'email',
        'tax_number',
        'prepared_by_name',
        'prepared_by_title',
        'reviewed_by_name',
        'reviewed_by_title',
        'approved_by_name',
        'approved_by_title',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            return '/storage/'.ltrim($this->logo_path, '/');
        }

        return null;
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }
}
