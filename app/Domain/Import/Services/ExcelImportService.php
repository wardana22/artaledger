<?php

namespace App\Domain\Import\Services;

use App\Models\Account;
use App\Models\ImportBatch;
use App\Models\ImportFile;
use App\Models\ImportRow;
use App\Models\Unit;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class ExcelImportService
{
    protected UnitMappingService $unitMappingService;

    public function __construct()
    {
        $this->unitMappingService = new UnitMappingService;
    }

    /**
     * Process Excel file, parse Sheet 'Jurnal Umum' or Cleaned Format, and save to staging tables.
     */
    public function importFile(string $filePath, string $originalFilename, ?int $userId = null): ImportBatch
    {
        if (! file_exists($filePath)) {
            throw new Exception("File tidak ditemukan pada path: {$filePath}");
        }

        // 1. Calculate SHA-256 Hash Idempotency
        $fileHash = hash_file('sha256', $filePath);
        $existingBatch = ImportBatch::where('file_hash', $fileHash)->where('status', 'posted')->first();
        if ($existingBatch) {
            throw new Exception("Gagal Unggah! File Excel '{$originalFilename}' sudah pernah di-import dan diposting sebelumnya (Batch: {$existingBatch->batch_code}).");
        }

        // 2. High-Performance Reader Configuration
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);

        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getSheetByName('Jurnal Umum')
            ?? $spreadsheet->getSheetByName('Jurnal Umum Cleaned')
            ?? $spreadsheet->getActiveSheet();

        // High-speed array dump without re-calculating formulas (uses Excel's cached values)
        $rows = $sheet->toArray(null, false, false, true);

        if (count($rows) < 2) {
            throw new Exception('File Excel kosong atau tidak memiliki data transaksi.');
        }

        // Pre-fetch all accounts & units for fast matching
        $accountMap = Account::pluck('id', 'code')->toArray();
        $unitMap = Unit::all()->keyBy('code');

        // Detect if this is a Cleaned Output File or Raw Excel
        $isCleanedFormat = $this->isCleanedPresetFormat($rows);
        $startRowIndex = $isCleanedFormat ? 4 : 10;

        return DB::transaction(function () use ($filePath, $originalFilename, $fileHash, $userId, $sheet, $rows, $accountMap, $isCleanedFormat, $startRowIndex) {
            $batchCode = 'IMP-'.now()->format('YmdHis').'-'.strtoupper(Str::random(4));

            $batch = ImportBatch::create([
                'batch_code' => $batchCode,
                'file_name' => $originalFilename,
                'file_hash' => $fileHash,
                'status' => 'staged',
                'user_id' => $userId,
            ]);

            ImportFile::create([
                'import_batch_id' => $batch->id,
                'original_filename' => $originalFilename,
                'stored_path' => $filePath,
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'file_size_bytes' => filesize($filePath),
            ]);

            $stagedRows = [];

            foreach ($rows as $rowIndex => $row) {
                if ($rowIndex < $startRowIndex) {
                    continue;
                }

                if ($isCleanedFormat) {
                    // Cleaned Preset Column Mapping: B=Tanggal, C=No.Bukti, D=Keterangan, E=Kode Akun, G=Unit, H=Debit, I=Kredit
                    $rawDate = $this->resolveCellValue($row['B'] ?? null, $sheet, 'B', $rowIndex);
                    $entryDate = $this->parseDate($rawDate);

                    $documentNumber = trim((string) ($this->resolveCellValue($row['C'] ?? null, $sheet, 'C', $rowIndex) ?? ''));
                    $description = trim((string) ($this->resolveCellValue($row['D'] ?? null, $sheet, 'D', $rowIndex) ?? ''));
                    $rawAccountCode = trim((string) ($this->resolveCellValue($row['E'] ?? null, $sheet, 'E', $rowIndex) ?? ''));

                    $rawDebit = $this->resolveCellValue($row['H'] ?? null, $sheet, 'H', $rowIndex);
                    $rawCredit = $this->resolveCellValue($row['I'] ?? null, $sheet, 'I', $rowIndex);
                } else {
                    // Raw Template Column Mapping: M=Tanggal, N=No.Bukti, O=Keterangan, Q=Parent, S=Sub, T=Debit, U=Kredit
                    if ($this->isEmptyColumnsNtoU($row)) {
                        continue;
                    }

                    $rawDate = $this->resolveCellValue($row['M'] ?? null, $sheet, 'M', $rowIndex);
                    $entryDate = $this->parseDate($rawDate);

                    $documentNumber = trim((string) ($this->resolveCellValue($row['N'] ?? null, $sheet, 'N', $rowIndex) ?? ''));
                    $description = trim((string) ($this->resolveCellValue($row['O'] ?? null, $sheet, 'O', $rowIndex) ?? ''));

                    $subAccount = trim((string) ($this->resolveCellValue($row['S'] ?? null, $sheet, 'S', $rowIndex) ?? ''));
                    $parentAccount = trim((string) ($this->resolveCellValue($row['Q'] ?? null, $sheet, 'Q', $rowIndex) ?? ''));

                    // Prioritize S (Sub Akun), fallback to Q (Akun Induk)
                    $rawAccountCode = ! empty($subAccount) ? $subAccount : $parentAccount;

                    $rawDebit = $this->resolveCellValue($row['T'] ?? null, $sheet, 'T', $rowIndex);
                    $rawCredit = $this->resolveCellValue($row['U'] ?? null, $sheet, 'U', $rowIndex);
                }

                // Clean trailing '.0' suffix
                if (str_ends_with($rawAccountCode, '.0')) {
                    $rawAccountCode = substr($rawAccountCode, 0, -2);
                }
                $rawAccountCode = trim($rawAccountCode);

                // Ignore formula errors like #REF!, #N/A
                if (str_starts_with($rawAccountCode, '#') || str_starts_with($rawAccountCode, '=')) {
                    $rawAccountCode = '';
                }

                $debit = (float) abs((float) str_replace(',', '.', (string) ($rawDebit ?? 0)));
                $credit = (float) abs((float) str_replace(',', '.', (string) ($rawCredit ?? 0)));

                // Rule: Abaikan baris jika nilai Debit dan Kredit keduanya 0 / kosong
                if ($debit == 0 && $credit == 0) {
                    continue;
                }

                // Match Account ID
                $accountId = $accountMap[$rawAccountCode] ?? null;

                // Step 4: Match Unit ID from Description (Column O) or Unit Code
                $unitId = $this->unitMappingService->detectUnitId($description);

                // Source Block Key for grouping: DATE|DOCUMENT_NUMBER
                $sourceBlockKey = sprintf('%s|%s', $entryDate ?: 'NODATE', $documentNumber ?: 'NODOC');

                $stagedRows[] = [
                    'import_batch_id' => $batch->id,
                    'row_index' => $rowIndex,
                    'raw_data' => json_encode([
                        'M' => $rawDate,
                        'N' => $documentNumber,
                        'O' => $description,
                        'Q' => $rawAccountCode,
                        'S' => $rawAccountCode,
                        'T' => $rawDebit,
                        'U' => $rawCredit,
                    ]),
                    'entry_date' => $entryDate,
                    'document_number' => $documentNumber ?: null,
                    'description' => $description ?: null,
                    'raw_account_code' => $rawAccountCode ?: null,
                    'account_id' => $accountId,
                    'unit_id' => $unitId,
                    'debit' => $debit,
                    'credit' => $credit,
                    'source_block_key' => $sourceBlockKey,
                    'validation_status' => 'valid',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (! empty($stagedRows)) {
                // Chunk insert for ultra-fast database processing
                foreach (array_chunk($stagedRows, 500) as $chunk) {
                    ImportRow::insert($chunk);
                }
            }

            $batch->update(['total_rows' => count($stagedRows)]);

            return $batch;
        });
    }

    /**
     * Check if array rows match Cleaned Preset format.
     */
    protected function isCleanedPresetFormat(array $rows): bool
    {
        $headerRow = $rows[3] ?? [];
        $colB = strtoupper(trim((string) ($headerRow['B'] ?? '')));
        $colE = strtoupper(trim((string) ($headerRow['E'] ?? '')));

        return str_contains($colB, 'TANGGAL') && str_contains($colE, 'KODE AKUN');
    }

    /**
     * Resolve cell value with cached formula fallback.
     * Never returns raw formula string '=IF(...)' if unresolved.
     */
    protected function resolveCellValue(mixed $val, $sheet, string $col, int $rowIndex): mixed
    {
        if ($val === null) {
            return null;
        }

        if (is_string($val) && str_starts_with($val, '=')) {
            try {
                $cell = $sheet->getCell("{$col}{$rowIndex}");

                // Tier 1: Try cached calculated value saved by Excel
                $oldVal = $cell->getOldCalculatedValue();
                if ($oldVal !== null && $oldVal !== '' && ! (is_string($oldVal) && (str_starts_with($oldVal, '=') || str_starts_with($oldVal, '#')))) {
                    return $oldVal;
                }

                // Tier 2: Try calculation engine
                $calcVal = $cell->getCalculatedValue();
                if ($calcVal !== null && $calcVal !== '' && ! (is_string($calcVal) && (str_starts_with($calcVal, '=') || str_starts_with($calcVal, '#')))) {
                    return $calcVal;
                }

                // Tier 3: Try formatted value
                $fmtVal = $cell->getFormattedValue();
                if ($fmtVal !== null && $fmtVal !== '' && ! (is_string($fmtVal) && (str_starts_with($fmtVal, '=') || str_starts_with($fmtVal, '#')))) {
                    return $fmtVal;
                }
            } catch (Throwable $e) {
                // If calculation fails, return null
            }

            // Return null if formula could not be evaluated to real data
            return null;
        }

        return $val;
    }

    protected function isEmptyColumnsNtoU(array $row): bool
    {
        $cols = ['N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U'];
        foreach ($cols as $c) {
            $val = trim((string) ($row[$c] ?? ''));
            if ($val !== '' && $val !== '-' && ! str_starts_with($val, '#') && ! str_starts_with($val, '=')) {
                return false;
            }
        }

        return true;
    }

    protected function parseDate($rawDate): ?string
    {
        if (empty($rawDate)) {
            return null;
        }

        if (is_numeric($rawDate)) {
            try {
                return ExcelDate::excelToDateTimeObject($rawDate)->format('Y-m-d');
            } catch (Exception $e) {
                return null;
            }
        }

        try {
            return Carbon::parse($rawDate)->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }
}
