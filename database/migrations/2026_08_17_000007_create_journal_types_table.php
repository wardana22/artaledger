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
        Schema::create('journal_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->foreignId('journal_type_id')->nullable()->after('period_id')->constrained('journal_types')->nullOnDelete();
            $table->boolean('is_auto_document_number')->default(true)->after('document_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropForeign(['journal_type_id']);
            $table->dropColumn(['journal_type_id', 'is_auto_document_number']);
        });

        Schema::dropIfExists('journal_types');
    }
};
