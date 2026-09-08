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
     * Gabungkan banyak baris jurnal menjadi banyak Invoice Fisik (Many to Many / M:N Invoicing).
     *
     * @param  array<int, int>  $journalLineIds
     * @param  array<int, array<string, mixed>>  $invoicesData
     * @return array<int, ApArInvoice>
     *
     * @throws Exception
     */
    public function consolidateJournalLinesIntoMultipleInvoices(array $journalLineIds, array $invoicesData, ?int $userId = null): array
    {
        if (count($journalLineIds) < 2) {
            throw new Exception('Penggabungan multi-jurnal memerlukan minimal 2 baris jurnal.');
        }

        if (empty($invoicesData)) {
            throw new Exception('Daftar invoice hasil pemecahan tidak boleh kosong.');
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

        $totalJournalsAmount = 0.0;
        $lineCapacities = []; // [line_id => remaining_capacity]

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

            $totalJournalsAmount += $lineAmt;
            $lineCapacities[$line->id] = $lineAmt;
        }

        $totalInvoicesAmount = 0.0;
        foreach ($invoicesData as $inv) {
            $amt = (float) ($inv['original_amount'] ?? 0);
            if ($amt <= 0) {
                throw new Exception('Nominal invoice harus lebih besar dari 0.');
            }
            if (empty(trim((string) ($inv['invoice_number'] ?? '')))) {
                throw new Exception('Nomor invoice tidak boleh kosong.');
            }
            $totalInvoicesAmount += $amt;
        }

        if (abs($totalInvoicesAmount - $totalJournalsAmount) > 0.01) {
            throw new Exception('Total invoice (Rp '.number_format($totalInvoicesAmount, 2, ',', '.').') tidak sama dengan total baris jurnal terpilih (Rp '.number_format($totalJournalsAmount, 2, ',', '.').').');
        }

        return DB::transaction(function () use ($firstLine, $companyId, $accountId, $type, $lines, $lineCapacities, $invoicesData, $totalInvoicesAmount, $userId) {
            $createdInvoices = [];
            $remainingCapacities = $lineCapacities;
            $lineIds = array_keys($remainingCapacities);
            $currentLineIdx = 0;

            foreach ($invoicesData as $invData) {
                $invAmount = (float) $invData['original_amount'];
                $invoice = ApArInvoice::create([
                    'company_id' => $companyId,
                    'unit_id' => $firstLine->unit_id,
                    'account_id' => $accountId,
                    'journal_line_id' => $firstLine->id,
                    'type' => $type,
                    'invoice_number' => trim((string) $invData['invoice_number']),
                    'invoice_date' => $invData['invoice_date'] ?? date('Y-m-d'),
                    'due_date' => $invData['due_date'] ?? $invData['invoice_date'] ?? date('Y-m-d'),
                    'original_amount' => $invAmount,
                    'partner_name' => $invData['partner_name'] ?? null,
                    'notes' => $invData['notes'] ?? null,
                    'status' => 'open',
                    'created_by' => $userId,
                ]);

                // Alokasikan nilai invoice ke baris jurnal secara proporsional/FIFO
                $remainingToAllocate = $invAmount;
                $pivotAllocations = [];

                while ($remainingToAllocate > 0.0001 && $currentLineIdx < count($lineIds)) {
                    $currLineId = $lineIds[$currentLineIdx];
                    $availCapacity = $remainingCapacities[$currLineId];

                    if ($availCapacity <= 0.0001) {
                        $currentLineIdx++;

                        continue;
                    }

                    $take = min($remainingToAllocate, $availCapacity);
                    $pivotAllocations[$currLineId] = ['allocated_amount' => round($take, 2)];

                    $remainingCapacities[$currLineId] -= $take;
                    $remainingToAllocate -= $take;

                    if ($remainingCapacities[$currLineId] <= 0.0001) {
                        $currentLineIdx++;
                    }
                }

                $invoice->journalLines()->attach($pivotAllocations);
                $createdInvoices[] = $invoice;
            }

            AuditLogService::record(
                'aging.invoice.consolidate_multi',
                "Menggabungkan {$lines->count()} baris jurnal menjadi ".count($createdInvoices).' lembar invoice senilai Rp '.number_format($totalInvoicesAmount, 2, ',', '.'),
                $createdInvoices[0],
                null,
                [
                    'journal_line_ids' => array_keys($lineCapacities),
                    'invoices_count' => count($createdInvoices),
                    'total_amount' => $totalInvoicesAmount,
                ],
                $userId
            );

            return $createdInvoices;
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

    /**
     * Perbarui / koreksi metadata dan/atau nominal faktur invoice.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Exception
     */
    public function updateInvoice(ApArInvoice $invoice, array $data, ?int $userId = null): ApArInvoice
    {
        $invoice->loadMissing(['settlements', 'journalLines', 'journalLine']);

        $totalSettled = (float) $invoice->settlements->sum('settled_amount');

        // Jika ada perubahan original_amount
        $newAmount = isset($data['original_amount']) ? (float) $data['original_amount'] : (float) $invoice->original_amount;

        if ($newAmount <= 0) {
            throw new Exception('Nominal invoice harus lebih besar dari 0.');
        }

        if ($newAmount < ($totalSettled - 0.01)) {
            throw new Exception('Nominal invoice baru (Rp '.number_format($newAmount, 2, ',', '.').') tidak boleh lebih kecil dari total pelunasan yang sudah dicatat (Rp '.number_format($totalSettled, 2, ',', '.').').');
        }

        return DB::transaction(function () use ($invoice, $data, $newAmount, $totalSettled, $userId) {
            $oldData = [
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date?->format('Y-m-d'),
                'due_date' => $invoice->due_date?->format('Y-m-d'),
                'original_amount' => (float) $invoice->original_amount,
                'partner_name' => $invoice->partner_name,
                'notes' => $invoice->notes,
            ];

            // Tentukan status invoice berdasarkan nominal baru dan pelunasan
            $newRemaining = $newAmount - $totalSettled;
            $newStatus = 'open';
            if ($newRemaining <= 0.01) {
                $newStatus = 'paid';
            } elseif ($totalSettled > 0.01) {
                $newStatus = 'partial';
            }

            $updatePayload = [
                'invoice_number' => trim((string) ($data['invoice_number'] ?? $invoice->invoice_number)),
                'invoice_date' => $data['invoice_date'] ?? $invoice->invoice_date,
                'due_date' => $data['due_date'] ?? $invoice->due_date,
                'partner_name' => array_key_exists('partner_name', $data) ? $data['partner_name'] : $invoice->partner_name,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $invoice->notes,
                'original_amount' => $newAmount,
                'status' => $newStatus,
            ];

            $invoice->update($updatePayload);

            // Jika relasi 1:1 langsung ke 1 journal line dan nominal berubah, perbarui allocated_amount pivot
            if ($invoice->journalLines->count() === 1 && abs((float) $oldData['original_amount'] - $newAmount) > 0.01) {
                $firstLine = $invoice->journalLines->first();
                $invoice->journalLines()->updateExistingPivot($firstLine->id, [
                    'allocated_amount' => $newAmount,
                ]);
            }

            AuditLogService::record(
                'aging.invoice.update',
                "Memperbarui data Invoice {$invoice->invoice_number}",
                $invoice,
                $oldData,
                $updatePayload,
                $userId
            );

            return $invoice->fresh();
        });
    }

    /**
     * Lepas / Batalkan penugasan invoice (Unlink).
     *
     * @throws Exception
     */
    public function unlinkInvoice(ApArInvoice $invoice, ?int $userId = null): void
    {
        $invoice->loadMissing(['settlements', 'journalLines']);

        if ($invoice->settlements()->exists()) {
            throw new Exception("Invoice {$invoice->invoice_number} tidak dapat dilepas karena sudah memiliki riwayat transaksi pelunasan. Batalkan pelunasan terlebih dahulu.");
        }

        DB::transaction(function () use ($invoice, $userId) {
            $invoiceNumber = $invoice->invoice_number;
            $amount = (float) $invoice->original_amount;
            $linkedLines = $invoice->journalLines->pluck('id')->all();

            $invoice->journalLines()->detach();
            $invoice->delete();

            AuditLogService::record(
                'aging.invoice.unlink',
                "Membatalkan penugasan Invoice {$invoiceNumber} senilai Rp ".number_format($amount, 2, ',', '.'),
                null,
                [
                    'invoice_number' => $invoiceNumber,
                    'original_amount' => $amount,
                    'journal_line_ids' => $linkedLines,
                ],
                null,
                $userId
            );
        });
    }
}
