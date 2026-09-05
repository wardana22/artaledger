<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'bank_name',
        'account_number',
        'account_holder',
        'period_start',
        'period_end',
        'opening_balance',
        'total_debit',
        'total_credit',
        'closing_balance',
        'file_path',
        'file_name',
        'status',
        'uploaded_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'opening_balance' => 'decimal:2',
            'total_debit' => 'decimal:2',
            'total_credit' => 'decimal:2',
            'closing_balance' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * @return HasMany<BankStatementLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(BankStatementLine::class);
    }

    public function getReconciledPercentage(): float
    {
        $totalLines = $this->lines()->count();
        if ($totalLines === 0) {
            return 0.0;
        }

        $matchedLines = $this->lines()->whereIn('match_status', ['matched', 'manual_matched', 'adjusted'])->count();

        return round(($matchedLines / $totalLines) * 100, 1);
    }
}
