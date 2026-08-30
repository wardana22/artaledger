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
        Schema::table('dashboard_kpis', function (Blueprint $table) {
            $table->text('formula_expression')->nullable()->after('calculation_type');
            $table->foreignId('account_group_id')->nullable()->constrained('account_groups')->nullOnDelete()->after('account_id');
            $table->string('display_format')->default('currency')->after('color_theme'); // 'currency', 'percentage', 'days', 'number', 'times'
            $table->integer('decimal_places')->default(0)->after('display_format');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dashboard_kpis', function (Blueprint $table) {
            $table->dropForeign(['account_group_id']);
            $table->dropColumn(['formula_expression', 'account_group_id', 'display_format', 'decimal_places']);
        });
    }
};
