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
        Schema::create('account_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color_theme')->default('indigo');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('account_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('account_prefix')->nullable(); // e.g. '4', '5', '11'
            $table->string('account_type')->nullable(); // e.g. 'PENDAPATAN', 'BEBAN'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_group_members');
        Schema::dropIfExists('account_groups');
    }
};
