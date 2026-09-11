<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_id',
        'account_id',
        'unit_id',
        'annual_amount',
        'm01_amount',
        'm02_amount',
        'm03_amount',
        'm04_amount',
        'm05_amount',
        'm06_amount',
        'm07_amount',
        'm08_amount',
        'm09_amount',
        'm10_amount',
        'm11_amount',
        'm12_amount',
        'notes',
    ];

    protected $casts = [
        'annual_amount' => 'decimal:2',
        'm01_amount' => 'decimal:2',
        'm02_amount' => 'decimal:2',
        'm03_amount' => 'decimal:2',
        'm04_amount' => 'decimal:2',
        'm05_amount' => 'decimal:2',
        'm06_amount' => 'decimal:2',
        'm07_amount' => 'decimal:2',
        'm08_amount' => 'decimal:2',
        'm09_amount' => 'decimal:2',
        'm10_amount' => 'decimal:2',
        'm11_amount' => 'decimal:2',
        'm12_amount' => 'decimal:2',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get budget amount for a specific month (1-12) or cumulative up to that month.
     */
    public function getMonthlyAmount(int $month): float
    {
        $padMonth = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $field = "m{$padMonth}_amount";

        return (float) ($this->{$field} ?? 0);
    }

    /**
     * Get cumulative YTD budget up to a given month.
     */
    public function getCumulativeAmount(int $month): float
    {
        $total = 0.0;
        for ($m = 1; $m <= min(12, max(1, $month)); $m++) {
            $total += $this->getMonthlyAmount($m);
        }

        return $total;
    }

    /**
     * Auto-distribute the annual amount equally across 12 months.
     */
    public function distributeEvenly(): void
    {
        $monthly = round((float) $this->annual_amount / 12, 2);
        $totalMonthly = $monthly * 11;
        $remainder = round((float) $this->annual_amount - $totalMonthly, 2);

        for ($m = 1; $m <= 11; $m++) {
            $pad = str_pad((string) $m, 2, '0', STR_PAD_LEFT);
            $this->{"m{$pad}_amount"} = $monthly;
        }
        $this->m12_amount = $remainder;
    }
}
