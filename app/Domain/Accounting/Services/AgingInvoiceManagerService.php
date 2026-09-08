<?php

namespace App\Domain\Accounting\Services;

use App\Models\ApArInvoice;
use App\Models\ApArSettlement;
use App\Models\JournalLine;
use App\Services\AuditLogService;
use Exception;
use Illuminate\Support\Facades\DB;

class AgingInvoiceManagerService
{
    /**
     * Daftarkan satu invoice langsung dari baris jurnal (1 to 1).
     *
     * @param  array<string, mixed>  $data  ['invoice_number', 'invoice_date', 'due_date', 'partner_name', 'notes']
     *
     * @throws Exception
     */
    public function assignSingleInvoice(JournalLine $line, array $data, ?int $userId = null): ApArInvoice
    {
        $line->loadMissing(['account', 'journalEntry']);

        $accountType = strtoupper((string) $line->account?->type);
        $type = str_contains($accountType, 'HUTANG') ? 'payable' : 'receivable';
        $originalAmount = $type === 'receivable' ? (float) $line->debit : (float) $line->credit;

        if ($originalAmount <= 0) {
            throw new Exception('Baris jurnal ini bukan mutasi pengakuan tagihan baru yang valid.');
        }

        if ($line->apArInvoices()->exists()) {
            throw new Exception('Baris jurnal ini sudah memiliki invoice terdaftar. Gunakan fitur split jika ingin mengubah pemecahan invoice.');
        }

        return DB::transaction(function () use ($line, $type, $originalAmount, $data, $userId) {
            $invoice = ApArInvoice::create([
                'company_id' => $line->journalEntry->company_id,
                'unit_id' => $line->unit_id,
                'account_id' => $line->account_id,
                'journal_line_id' => $line->id,
                'type' => $type,
                'invoice_number' => trim((string) $data['invoice_number']),
                'invoice_date' => $data['invoice_date'] ?? $line->journalEntry->entry_date->format('Y-m-d'),
                'due_date' => $data['due_date'] ?? $data['invoice_date'] ?? $line->journalEntry->entry_date->format('Y-m-d'),
                'original_amount' => $originalAmount,
                'partner_name' => $data['partner_name'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'open',
                'created_by' => $userId,
            ]);

            $invoice->journalLines()->attach($line->id, [
                'allocated_amount' => $originalAmount,
            ]);

            AuditLogService::record(
                'aging.invoice.assign',
                "Menugaskan Invoice {$invoice->invoice_number} senilai Rp ".number_format($originalAmount, 2)." pada Jurnal {$line->journalEntry->entry_number}",
                $invoice,
                null,
                [
                    'journal_line_id' => $line->id,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'original_amount' => $originalAmount,
                ],
                $userId
            );

            return $invoice;
        });
    }

    /**
     * Pecah 1 baris jurnal menjadi banyak invoice (1 to Many / Split Invoicing).
     *
     * @param  array<int, array<string, mixed>>  $invoicesData  [['invoice_number', 'invoice_date', 'due_date', 'original_amount', 'partner_name', 'notes'], ...]
     *
     * @throws Exception
     */
    public function splitJournalLineIntoInvoices(JournalLine $line, array $invoicesData, ?int $userId = null): array
    {
        $line->loadMissing(['account', 'journalEntry']);

        $accountType = strtoupper((string) $line->account?->type);
        $type = str_contains($accountType, 'HUTANG') ? 'payable' : 'receivable';
        $totalLineAmount = $type === 'receivable' ? (float) $line->debit : (float) $line->credit;

        if (empty($invoicesData)) {
            throw new Exception('Daftar pemecahan invoice tidak boleh kosong.');
        }

        $sumInvoices = 0.0;
        foreach ($invoicesData as $inv) {
            $amt = (float) ($inv['original_amount'] ?? 0);
            if ($amt <= 0) {
                throw new Exception('Nominal invoice hasil pemecahan harus lebih besar dari 0.');
            }
            $sumInvoices += $amt;
        }

        if (abs($sumInvoices - $totalLineAmount) > 0.01) {
            throw new Exception('Total pemecahan invoice (Rp '.number_format($sumInvoices, 2).') tidak sama dengan nilai baris jurnal (Rp '.number_format($totalLineAmount, 2).').');
        }

        // Cek jika invoice lama sudah memiliki settlement
        $existingInvoices = $line->apArInvoices()->with('settlements')->get();
        foreach ($existingInvoices as $exist) {
            if ($exist->settlements->count() > 0) {
                throw new Exception("Tidak dapat memecah ulang invoice karena Invoice {$exist->invoice_number} sudah memiliki transaksi pelunasan.");
            }
        }

        return DB::transaction(function () use ($line, $existingInvoices, $invoicesData, $type, $userId) {
            foreach ($existingInvoices as $exist) {
                $exist->delete();
            }

            $created = [];
            foreach ($invoicesData as $inv) {
                $invoice = ApArInvoice::create([
                    'company_id' => $line->journalEntry->company_id,
                    'unit_id' => $line->unit_id,
                    'account_id' => $line->account_id,
                    'journal_line_id' => $line->id,
                    'type' => $type,
                    'invoice_number' => trim((string) $inv['invoice_number']),
                    'invoice_date' => $inv['invoice_date'] ?? $line->journalEntry->entry_date->format('Y-m-d'),
                    'due_date' => $inv['due_date'] ?? $inv['invoice_date'] ?? $line->journalEntry->entry_date->format('Y-m-d'),
                    'original_amount' => (float) $inv['original_amount'],
                    'partner_name' => $inv['partner_name'] ?? null,
                    'notes' => $inv['notes'] ?? null,
                    'status' => 'open',
                    'created_by' => $userId,
                ]);

                $invoice->journalLines()->attach($line->id, [
                    'allocated_amount' => (float) $inv['original_amount'],
                ]);

                $created[] = $invoice;
            }

            AuditLogService::record(
                'aging.invoice.split',
                "Memecah baris jurnal {$line->id} menjadi ".count($created)." invoice pada Jurnal {$line->journalEntry->entry_number}",
                $line,
                null,
                ['journal_line_id' => $line->id, 'invoices_count' => count($created)],
                $userId
            );

            return $created;
        });
    }

    /**
     * Gabungkan banyak baris jurnal menjadi 1 Invoice Fisik (Many to 1 / Multi-Journal Consolidation).
     *
     * @param  array<int, int>  $journalLineIds
     * @param  array<string, mixed>  $invoiceData  ['invoice_number', 'invoice_date', 'due_date', 'partner_name', 'notes']
     *
     * @throws Exception
     */
    public function consolidateJournalLinesIntoInvoice(array $journalLineIds, array $invoiceData, ?int $userId = null): ApArInvoice
    {
        if (count($journalLineIds) < 2) {
            throw new Exception('Penggabungan invoice memerlukan minimal 2 baris jurnal.');
        }

        $lines = JournalLine::with(['account', 'journalEntry'])->whereIn('id', $journalLineIds)->get();

        if ($lines->count() !== count($journalLineIds)) {
            throw new Exception('Sebagian baris jurnal yang dipilih tidak ditemukan.');
        }

        $firstLine = $lines->first();
        $accountId = $firstLine->account_id;
        $companyId = $firstLine->journalEntry->company_id;
        $accountType = strtoupper((string) $firstLine->account?->type);
        $type = str_contains($accountType, 'HUTANG') ? 'payable' : 'receivable';

        $totalConsolidated = 0.0;
        $allocations = [];

        foreach ($lines as $line) {
            if ($line->account_id !== $accountId) {
                throw new Exception("Seluruh baris jurnal yang digabung harus berasal dari Akun COA yang sama ({$firstLine->account?->code}).");
            }

            if ($line->apArInvoices()->exists()) {
                throw new Exception("Baris jurnal pada Jurnal {$line->journalEntry->entry_number} sudah terikat pada invoice lain.");
            }

            $lineAmt = $type === 'receivable' ? (float) $line->debit : (float) $line->credit;
            if ($lineAmt <= 0) {
                throw new Exception("Baris jurnal {$line->id} memiliki nominal tidak valid.");
            }

            $totalConsolidated += $lineAmt;
            $allocations[$line->id] = ['allocated_amount' => $lineAmt];
        }

        return DB::transaction(function () use ($firstLine, $companyId, $accountId, $type, $totalConsolidated, $allocations, $invoiceData, $userId, $lines) {
            $invoice = ApArInvoice::create([
                'company_id' => $companyId,
                'unit_id' => $firstLine->unit_id,
                'account_id' => $accountId,
                'journal_line_id' => $firstLine->id, // Fallback backward compatibility
                'type' => $type,
                'invoice_number' => trim((string) $invoiceData['invoice_number']),
                'invoice_date' => $invoiceData['invoice_date'] ?? date('Y-m-d'),
                'due_date' => $invoiceData['due_date'] ?? $invoiceData['invoice_date'] ?? date('Y-m-d'),
                'original_amount' => $totalConsolidated,
                'partner_name' => $invoiceData['partner_name'] ?? null,
                'notes' => $invoiceData['notes'] ?? null,
                'status' => 'open',
                'created_by' => $userId,
            ]);

            $invoice->journalLines()->attach($allocations);

            AuditLogService::record(
                'aging.invoice.consolidate',
                "Menggabungkan {$lines->count()} baris jurnal menjadi Invoice {$invoice->invoice_number} senilai Rp ".number_format($totalConsolidated, 2),
                $invoice,
                null,
                [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'journal_line_ids' => array_keys($allocations),
                    'total_amount' => $totalConsolidated,
                ],
                $userId
            );

            return $invoice;
        });
    }

    /**
     * Catat alokasi pelunasan invoice spesifik (Settlement).
     *
     * @throws Exception
     */
    public function settleInvoice(ApArInvoice $invoice, JournalLine $paymentLine, float $amount, ?int $userId = null): ApArSettlement
    {
        $remaining = $invoice->remaining_amount;

        if ($amount <= 0) {
            throw new Exception('Nominal pelunasan harus lebih besar dari 0.');
        }

        if ($amount > ($remaining + 0.01)) {
            throw new Exception('Nominal pelunasan (Rp '.number_format($amount, 2).') melebihi sisa tagihan invoice (Rp '.number_format($remaining, 2).').');
        }

        return DB::transaction(function () use ($invoice, $paymentLine, $amount, $userId) {
            $paymentLine->loadMissing('journalEntry');

            $settlement = ApArSettlement::create([
                'ap_ar_invoice_id' => $invoice->id,
                'payment_journal_line_id' => $paymentLine->id,
                'settled_amount' => $amount,
                'settled_date' => $paymentLine->journalEntry?->entry_date->format('Y-m-d') ?? date('Y-m-d'),
                'notes' => $paymentLine->description,
                'created_by' => $userId,
            ]);

            // Update invoice status
            $newRemaining = $invoice->fresh()->remaining_amount;
            if ($newRemaining <= 0.01) {
                $invoice->update(['status' => 'paid']);
            } else {
                $invoice->update(['status' => 'partial']);
            }

            AuditLogService::record(
                'aging.invoice.settle',
                "Melunasi Invoice {$invoice->invoice_number} sebesar Rp ".number_format($amount, 2)." via Jurnal {$paymentLine->journalEntry?->entry_number}",
                $settlement,
                null,
                [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'settled_amount' => $amount,
                    'remaining_amount' => $newRemaining,
                ],
                $userId
            );

            return $settlement;
        });
    }
}
