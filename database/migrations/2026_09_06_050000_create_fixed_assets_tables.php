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
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedInteger('useful_life_years')->default(4);
            $table->decimal('salvage_percentage', 5, 2)->default(0.00);
            $table->foreignId('asset_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('accumulated_depreciation_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('depreciation_expense_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->foreignId('asset_category_id')->constrained('asset_categories')->cascadeOnDelete();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->string('serial_number')->nullable();
            $table->string('location')->nullable();
            $table->string('person_in_charge')->nullable();
            $table->date('acquisition_date');
            $table->decimal('acquisition_cost', 18, 2);
            $table->decimal('salvage_value', 18, 2)->default(0);
            $table->unsignedInteger('useful_life_months')->default(0);
            $table->string('depreciation_method')->default('straight_line');
            $table->decimal('monthly_depreciation_amount', 18, 2)->default(0);
            $table->decimal('accumulated_depreciation', 18, 2)->default(0);
            $table->decimal('book_value', 18, 2)->default(0);
            $table->date('start_depreciation_date')->nullable();
            $table->string('last_depreciated_period', 7)->nullable(); // YYYY-MM
            $table->string('status')->default('active'); // active, fully_depreciated, disposed
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['unit_id', 'status']);
        });

        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained('fixed_assets')->cascadeOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->string('period', 7); // YYYY-MM
            $table->date('depreciation_date');
            $table->decimal('amount', 18, 2);
            $table->decimal('accumulated_to_date', 18, 2);
            $table->decimal('book_value_after', 18, 2);
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('posted_at')->nullable();
            $table->timestamps();

            $table->unique(['fixed_asset_id', 'period']);
            $table->index(['period', 'journal_entry_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');
        Schema::dropIfExists('fixed_assets');
        Schema::dropIfExists('asset_categories');
    }
};
