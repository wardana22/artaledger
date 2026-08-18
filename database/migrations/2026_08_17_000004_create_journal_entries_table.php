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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('period_id')->nullable()->constrained('accounting_periods')->nullOnDelete();
            $table->string('entry_number')->index();
            $table->date('entry_date')->index();
            $table->string('document_number')->nullable()->index();
            $table->text('description')->nullable();
            $table->enum('source_type', ['manual', 'import'])->default('manual');
            $table->unsignedBigInteger('source_file_id')->nullable();
            $table->unsignedBigInteger('import_batch_id')->nullable();
            $table->string('source_block_key')->nullable()->index();
            $table->enum('status', ['draft', 'posted', 'reversed'])->default('draft')->index();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'entry_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
