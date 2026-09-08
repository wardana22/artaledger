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
        Schema::create('ap_ar_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('journal_line_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['receivable', 'payable'])->index();
            $table->string('invoice_number', 100);
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('original_amount', 15, 2);
            $table->string('partner_name', 255)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['open', 'partial', 'paid'])->default('open')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['account_id', 'status']);
            $table->index(['company_id', 'due_date']);
        });

        Schema::create('ap_ar_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ap_ar_invoice_id')->constrained('ap_ar_invoices')->cascadeOnDelete();
            $table->foreignId('payment_journal_line_id')->constrained('journal_lines')->cascadeOnDelete();
            $table->decimal('settled_amount', 15, 2);
            $table->date('settled_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['ap_ar_invoice_id', 'settled_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ap_ar_settlements');
        Schema::dropIfExists('ap_ar_invoices');
    }
};
