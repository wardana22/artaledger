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
        Schema::create('cash_flow_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('section'); // 'operating', 'investing', 'financing'
            $table->string('label');
            $table->string('source_type')->default('account_group'); // 'account', 'account_group', 'formula'
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('account_group_id')->nullable()->constrained('account_groups')->nullOnDelete();
            $table->foreignId('counter_account_group_id')->nullable()->constrained('account_groups')->nullOnDelete();
            $table->string('calculation_type')->default('net_mutation'); // 'net_mutation', 'debit_only', 'credit_only', 'ending_balance'
            $table->text('formula_expression')->nullable();
            $table->string('operator_sign')->default('+'); // '+', '-'
            $table->integer('order_index')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_flow_rows');
    }
};
