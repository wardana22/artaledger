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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->year('fiscal_year')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft')->index();
            $table->enum('enforcement_mode', ['warning_only', 'strict_block'])->default('warning_only');
            $table->decimal('warning_threshold_pct', 5, 2)->default(80.00); // Trigger warning when spent >= 80%
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['fiscal_year', 'name'], 'uq_budgets_year_name');
        });

        Schema::create('budget_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();

            $table->decimal('annual_amount', 18, 2)->default(0);
            $table->decimal('m01_amount', 18, 2)->default(0);
            $table->decimal('m02_amount', 18, 2)->default(0);
            $table->decimal('m03_amount', 18, 2)->default(0);
            $table->decimal('m04_amount', 18, 2)->default(0);
            $table->decimal('m05_amount', 18, 2)->default(0);
            $table->decimal('m06_amount', 18, 2)->default(0);
            $table->decimal('m07_amount', 18, 2)->default(0);
            $table->decimal('m08_amount', 18, 2)->default(0);
            $table->decimal('m09_amount', 18, 2)->default(0);
            $table->decimal('m10_amount', 18, 2)->default(0);
            $table->decimal('m11_amount', 18, 2)->default(0);
            $table->decimal('m12_amount', 18, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['budget_id', 'account_id', 'unit_id'], 'uq_budget_line_acc_unit');
            $table->index(['account_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_lines');
        Schema::dropIfExists('budgets');
    }
};
