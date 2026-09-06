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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('prepared_by_name')->nullable()->default('Staff Akuntansi')->after('tax_number');
            $table->string('prepared_by_title')->nullable()->default('Bagian Keuangan & Akuntansi')->after('prepared_by_name');
            $table->string('reviewed_by_name')->nullable()->default('Manager Akuntansi')->after('prepared_by_title');
            $table->string('reviewed_by_title')->nullable()->default('Accounting & Tax Lead')->after('reviewed_by_name');
            $table->string('approved_by_name')->nullable()->default('Direktur Keuangan')->after('reviewed_by_title');
            $table->string('approved_by_title')->nullable()->default('Chief Financial Officer (CFO)')->after('approved_by_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'prepared_by_name',
                'prepared_by_title',
                'reviewed_by_name',
                'reviewed_by_title',
                'approved_by_name',
                'approved_by_title',
            ]);
        });
    }
};
