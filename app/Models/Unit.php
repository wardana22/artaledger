<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'keywords',
    ];

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class, 'unit_id');
    }
}
