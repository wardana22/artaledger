<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bank_reconciliation_match_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_statement_id')->constrained('bank_statements')->cascadeOnDelete();
            $table->string('match_code')->unique();
            $table->string('match_type')->default('manual_multi'); // auto, manual_single, manual_multi
            $table->decimal('total_bank_amount', 18, 2)->default(0);
            $table->decimal('total_book_amount', 18, 2)->default(0);
            $table->decimal('difference', 18, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('matched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('matched_at')->nullable();
            $table->timestamps();

            $table->index(['bank_statement_id', 'match_type'], 'brmg_statement_type_idx');
        });

        Schema::create('bank_statement_line_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_group_id')->constrained('bank_reconciliation_match_groups')->cascadeOnDelete();
            $table->foreignId('bank_statement_line_id')->constrained('bank_statement_lines')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['match_group_id', 'bank_statement_line_id'], 'bslm_grp_line_unique');
            $table->index('bank_statement_line_id', 'bslm_line_idx');
        });

        Schema::create('bank_journal_line_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_group_id')->constrained('bank_reconciliation_match_groups')->cascadeOnDelete();
            $table->foreignId('journal_line_id')->constrained('journal_lines')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['match_group_id', 'journal_line_id'], 'bjlm_grp_jline_unique');
            $table->index('journal_line_id', 'bjlm_jline_idx');
        });

        // Migrasi data eksisting yang sudah matched ke dalam struktur grup
        $existingGroups = DB::table('bank_statement_lines')
            ->whereNotNull('matched_journal_line_id')
            ->get()
            ->groupBy('matched_journal_line_id');

        foreach ($existingGroups as $journalLineId => $lines) {
            $firstLine = $lines->first();
            $totalBank = (float) $lines->sum(function ($l) {
                return (float) $l->debit > 0 ? (float) $l->debit : (float) $l->credit;
            });
            $jLine = DB::table('journal_lines')->where('id', $journalLineId)->first();
            $totalBook = $jLine
                ? ((float) $jLine->debit > 0 ? (float) $jLine->debit : (float) $jLine->credit)
                : $totalBank;

            $groupId = DB::table('bank_reconciliation_match_groups')->insertGetId([
                'bank_statement_id' => $firstLine->bank_statement_id,
                'match_code' => 'GRP-'.Str::upper(Str::random(8)),
                'match_type' => $lines->count() > 1 ? 'manual_multi' : ($firstLine->match_status === 'manual_matched' ? 'manual_single' : 'auto'),
                'total_bank_amount' => $totalBank,
                'total_book_amount' => $totalBook,
                'difference' => abs($totalBank - $totalBook),
                'matched_by' => $firstLine->matched_by,
                'matched_at' => $firstLine->matched_at ?? now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($lines as $line) {
                DB::table('bank_statement_line_matches')->insert([
                    'match_group_id' => $groupId,
                    'bank_statement_line_id' => $line->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('bank_journal_line_matches')->insert([
                'match_group_id' => $groupId,
                'journal_line_id' => $journalLineId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_journal_line_matches');
        Schema::dropIfExists('bank_statement_line_matches');
        Schema::dropIfExists('bank_reconciliation_match_groups');
    }
};
