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
        Schema::table('journal_lines', function (Blueprint $table) {
            $table->index(['account_id', 'unit_id'], 'journal_lines_account_unit_idx');
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->index(['company_id', 'status', 'entry_date'], 'journal_entries_company_status_date_idx');
            $table->index(['company_id', 'entry_type', 'status'], 'journal_entries_company_type_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_lines', function (Blueprint $table) {
            $table->dropIndex('journal_lines_account_unit_idx');
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropIndex('journal_entries_company_status_date_idx');
            $table->dropIndex('journal_entries_company_type_status_idx');
        });
    }
};
