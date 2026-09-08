<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApArSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'ap_ar_invoice_id',
        'payment_journal_line_id',
        'settled_amount',
        'settled_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'settled_date' => 'date',
        'settled_amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ApArInvoice::class, 'ap_ar_invoice_id');
    }

    public function apArInvoice(): BelongsTo
    {
        return $this->belongsTo(ApArInvoice::class, 'ap_ar_invoice_id');
    }

    public function paymentJournalLine(): BelongsTo
    {
        return $this->belongsTo(JournalLine::class, 'payment_journal_line_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
