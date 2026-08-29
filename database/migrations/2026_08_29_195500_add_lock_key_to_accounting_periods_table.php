<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounting_periods', function (Blueprint $table) {
            $table->string('lock_key')->nullable()->after('status');
            $table->timestamp('closed_at')->nullable()->after('lock_key');
            $table->foreignId('closed_by')->nullable()->after('closed_at')->constrained('users')->nullOnDelete();
            $table->timestamp('opened_at')->nullable()->after('closed_by');
            $table->foreignId('opened_by')->nullable()->after('opened_at')->constrained('users')->nullOnDelete();
            $table->text('reopen_reason')->nullable()->after('opened_by');
        });
    }

    public function down(): void
    {
        Schema::table('accounting_periods', function (Blueprint $table) {
            $table->dropForeign(['closed_by']);
            $table->dropForeign(['opened_by']);
            $table->dropColumn(['lock_key', 'closed_at', 'closed_by', 'opened_at', 'opened_by', 'reopen_reason']);
        });
    }
};
