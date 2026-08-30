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
        Schema::create('dashboard_kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('source_type')->default('account'); // 'account' or 'account_type'
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('account_type')->nullable(); // 'asset', 'revenue', 'expense', 'liability', 'equity'
            $table->string('calculation_type')->default('ending_balance'); // 'ending_balance', 'period_mutation', 'debit_sum', 'credit_sum'
            $table->string('color_theme')->default('indigo'); // 'indigo', 'emerald', 'rose', 'amber', 'sky', 'violet'
            $table->string('icon')->default('wallet');
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_kpis');
    }
};
