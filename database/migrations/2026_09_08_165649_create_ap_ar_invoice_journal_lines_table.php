<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ap_ar_invoice_journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ap_ar_invoice_id')->constrained('ap_ar_invoices')->cascadeOnDelete();
            $table->foreignId('journal_line_id')->constrained('journal_lines')->cascadeOnDelete();
            $table->decimal('allocated_amount', 15, 2);
            $table->timestamps();

            $table->index(['ap_ar_invoice_id', 'journal_line_id'], 'ap_ar_inv_jl_idx');
        });

        // Migrasikan data yang sudah ada dari ap_ar_invoices ke tabel pivot
        if (Schema::hasColumn('ap_ar_invoices', 'journal_line_id')) {
            $existingInvoices = DB::table('ap_ar_invoices')->whereNotNull('journal_line_id')->get();
            foreach ($existingInvoices as $inv) {
                DB::table('ap_ar_invoice_journal_lines')->insert([
                    'ap_ar_invoice_id' => $inv->id,
                    'journal_line_id' => $inv->journal_line_id,
                    'allocated_amount' => $inv->original_amount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::table('ap_ar_invoices', function (Blueprint $table) {
                $table->foreignId('journal_line_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ap_ar_invoice_journal_lines');
    }
};
