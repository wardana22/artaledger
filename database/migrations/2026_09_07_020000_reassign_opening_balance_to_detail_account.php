<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $headerAccount = DB::table('accounts')->where('code', '21.02')->first();
        $detailAccount = DB::table('accounts')->where('code', '21.02.02')->first();

        if ($headerAccount && $detailAccount) {
            // 1. Reassign the opening balance journal line from header (21.02) to detail (21.02.02)
            DB::table('journal_lines')
                ->where('account_id', $headerAccount->id)
                ->where('credit', 3156025634.61)
                ->update(['account_id' => $detailAccount->id]);

            // 2. Adjust cached opening_balance column on accounts table
            DB::table('accounts')
                ->where('id', $headerAccount->id)
                ->update(['opening_balance' => 0.00]);

            DB::table('accounts')
                ->where('id', $detailAccount->id)
                ->update(['opening_balance' => 3156025634.61]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $headerAccount = DB::table('accounts')->where('code', '21.02')->first();
        $detailAccount = DB::table('accounts')->where('code', '21.02.02')->first();

        if ($headerAccount && $detailAccount) {
            DB::table('journal_lines')
                ->where('account_id', $detailAccount->id)
                ->where('credit', 3156025634.61)
                ->update(['account_id' => $headerAccount->id]);

            DB::table('accounts')
                ->where('id', $headerAccount->id)
                ->update(['opening_balance' => 3156025634.61]);

            DB::table('accounts')
                ->where('id', $detailAccount->id)
                ->update(['opening_balance' => 0.00]);
        }
    }
};
