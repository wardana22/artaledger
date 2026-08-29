<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalTemplateLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_template_id',
        'line_no',
        'account_id',
        'unit_id',
        'description',
        'debit',
        'credit',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(JournalTemplate::class, 'journal_template_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
