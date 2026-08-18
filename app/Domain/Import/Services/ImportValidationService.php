<?php

namespace App\Domain\Import\Services;

use App\Models\AccountingPeriod;
use App\Models\ImportBatch;

class ImportValidationService
{
    /**
     * Run validation engine on all rows of an ImportBatch.
     */
    public function validateBatch(ImportBatch $batch): ImportBatch
    {
        $rows = $batch->rows()->with('account')->get();
        $openPeriods = AccountingPeriod::where('status', 'open')->get();

        $validCount = 0;
        $errorCount = 0;

        foreach ($rows as $row) {
            $messages = [];

            // A. Account validation
            if (empty($row->raw_account_code)) {
                $messages[] = "Baris {$row->row_index}: Kode akun kosong.";
            } elseif (! $row->account_id || ! $row->account) {
                $messages[] = "Baris {$row->row_index}: Akun '{$row->raw_account_code}' tidak terdaftar dalam aplikasi ArtaLedger.";
            } elseif ($row->account->is_group) {
                $messages[] = "Baris {$row->row_index}: Akun '{$row->raw_account_code}' ({$row->account->name}) adalah akun Header (bukan akun posting).";
            } elseif (! $row->account->is_active) {
                $messages[] = "Baris {$row->row_index}: Akun '{$row->raw_account_code}' ({$row->account->name}) berstatus Non-Aktif.";
            }

            // B. Date & Period validation
            if (! $row->entry_date) {
                $messages[] = "Baris {$row->row_index}: Tanggal transaksi tidak valid atau kosong.";
            } else {
                $inOpenPeriod = $openPeriods->contains(function ($period) use ($row) {
                    return $row->entry_date->between($period->start_date, $period->end_date);
                });

                if (! $inOpenPeriod) {
                    $messages[] = "Baris {$row->row_index}: Tanggal {$row->entry_date->format('d/m/Y')} tidak berada dalam periode akuntansi yang terbuka (Open).";
                }
            }

            $status = empty($messages) ? 'valid' : 'error';

            if ($status === 'valid') {
                $validCount++;
            } else {
                $errorCount++;
            }

            $row->update([
                'validation_status' => $status,
                'validation_messages' => empty($messages) ? null : array_values(array_unique($messages)),
            ]);
        }

        $batch->update([
            'valid_rows' => $validCount,
            'error_rows' => $errorCount,
            'status' => 'validated',
        ]);

        return $batch;
    }
}
