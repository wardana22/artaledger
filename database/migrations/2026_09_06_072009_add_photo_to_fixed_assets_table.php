<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('serial_number');
        });
    }

    public function down(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
};
