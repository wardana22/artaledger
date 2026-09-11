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
        // 1. Ubah default is_locked menjadi false
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->change();
        });

        // 2. Buka kunci untuk seluruh jurnal yang bukan berasal dari Opening Balance / Saldo Awal
        DB::statement("UPDATE journal_entries SET is_locked = 0 WHERE (source_type != 'opening_balance' OR source_type IS NULL) AND (entry_number NOT LIKE 'SA-%' OR entry_number IS NULL)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->boolean('is_locked')->default(true)->change();
        });
    }
};
