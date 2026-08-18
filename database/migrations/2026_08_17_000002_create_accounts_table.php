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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('code')->index();
            $table->string('name');
            $table->string('type')->nullable(); // AKTIVA, BANK, PIUTANG, HUTANG, BEBAN, PENDAPATAN, EKUITAS
            $table->enum('normal_balance', ['debit', 'credit'])->default('debit');
            $table->enum('report_type', ['neraca', 'laba_rugi'])->default('neraca');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->boolean('is_group')->default(false); // true = Parent Group (non-postable), false = Posting Account
            $table->boolean('is_active')->default(true);
            $table->integer('level')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
