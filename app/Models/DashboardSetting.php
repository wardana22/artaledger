<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'show_kpi_cards',
        'show_revenue_expense_chart',
        'show_recent_journals',
        'show_quick_actions',
        'show_period_status',
        'show_cash_bank_summary',
        'chart_type',
        'recent_journals_count',
    ];

    protected $casts = [
        'show_kpi_cards' => 'boolean',
        'show_revenue_expense_chart' => 'boolean',
        'show_recent_journals' => 'boolean',
        'show_quick_actions' => 'boolean',
        'show_period_status' => 'boolean',
        'show_cash_bank_summary' => 'boolean',
        'recent_journals_count' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
