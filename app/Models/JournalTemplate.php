<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'template_code',
        'name',
        'description',
        'journal_type_id',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function journalType(): BelongsTo
    {
        return $this->belongsTo(JournalType::class, 'journal_type_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalTemplateLine::class, 'journal_template_id')->orderBy('line_no');
    }
}
