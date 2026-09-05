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
        Schema::create('bank_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->string('bank_name')->default('BANK BRI');
            $table->string('account_number');
            $table->string('account_holder');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('opening_balance', 18, 2)->default(0);
            $table->decimal('total_debit', 18, 2)->default(0);
            $table->decimal('total_credit', 18, 2)->default(0);
            $table->decimal('closing_balance', 18, 2)->default(0);
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('status')->default('pending'); // pending, in_progress, reconciled
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['account_id', 'period_start', 'period_end']);
        });

        Schema::create('bank_statement_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_statement_id')->constrained('bank_statements')->cascadeOnDelete();
            $table->date('transaction_date');
            $table->time('transaction_time')->nullable();
            $table->text('description');
            $table->decimal('debit', 18, 2)->default(0);   // Bank Debit (Pengeluaran rekening bank)
            $table->decimal('credit', 18, 2)->default(0);  // Bank Credit (Penerimaan rekening bank)
            $table->decimal('balance', 18, 2)->default(0); // Saldo buku bank / ledger
            $table->string('teller_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('match_status')->default('unmatched'); // unmatched, matched, manual_matched, adjusted, ignored
            $table->foreignId('matched_journal_line_id')->nullable()->constrained('journal_lines')->nullOnDelete();
            $table->timestamp('matched_at')->nullable();
            $table->foreignId('matched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['bank_statement_id', 'match_status']);
            $table->index(['transaction_date', 'debit', 'credit']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_statement_lines');
        Schema::dropIfExists('bank_statements');
    }
};
