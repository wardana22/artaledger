<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dashboard_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('show_kpi_cards')->default(true);
            $table->boolean('show_revenue_expense_chart')->default(true);
            $table->boolean('show_recent_journals')->default(true);
            $table->boolean('show_quick_actions')->default(true);
            $table->boolean('show_period_status')->default(true);
            $table->boolean('show_cash_bank_summary')->default(true);
            $table->string('chart_type')->default('bar'); // 'bar', 'area', 'table'
            $table->integer('recent_journals_count')->default(5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_settings');
    }
};
