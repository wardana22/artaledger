<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApArInvoiceJournalLine extends Model
{
    use HasFactory;

    protected $table = 'ap_ar_invoice_journal_lines';

    protected $fillable = [
        'ap_ar_invoice_id',
        'journal_line_id',
        'allocated_amount',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ApArInvoice::class, 'ap_ar_invoice_id');
    }

    public function journalLine(): BelongsTo
    {
        return $this->belongsTo(JournalLine::class, 'journal_line_id');
    }
}
