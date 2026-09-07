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
        if (! Schema::hasTable('dashboard_charts')) {
            Schema::create('dashboard_charts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->string('type')->default('area'); // area, bar, line
                $table->string('width')->default('half'); // half, full
                $table->integer('months')->default(12);
                $table->boolean('is_visible')->default(true);
                $table->integer('order')->default(0);
                $table->json('metric_ids')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_charts');
    }
};
