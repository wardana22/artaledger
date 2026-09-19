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
     * Inspect uploaded Excel file to extract available sheet names and raw sample preview rows.
     *
     * @return array{sheets: array<string>, active_sheet: string, sample_rows: array<int, array<string, mixed>>, columns: array<string>}
     */
    public function inspectSpreadsheet(string $filePath, ?string $sheetName = null, int $maxSampleRows = 15): array
    {
        if (! file_exists($filePath)) {
            throw new Exception("File tidak ditemukan pada path: {$filePath}");
        }

        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);

        $spreadsheet = $reader->load($filePath);
        $sheetNames = $spreadsheet->getSheetNames();

        if (empty($sheetNames)) {
            throw new Exception('Berkas Excel tidak memiliki sheet yang valid.');
        }

        $activeSheetName = $sheetName && in_array($sheetName, $sheetNames, true)
            ? $sheetName
            : ($spreadsheet->getSheetByName('Jurnal Umum') ? 'Jurnal Umum' : ($spreadsheet->getSheetByName('Jurnal Umum Cleaned') ? 'Jurnal Umum Cleaned' : $sheetNames[0]));

        $sheet = $spreadsheet->getSheetByName($activeSheetName) ?? $spreadsheet->getActiveSheet();

        // Ambil sample baris hingga maxSampleRows
        $allRows = $sheet->toArray(null, false, false, true);
        $sampleRows = [];
        $columnsFound = [];

        $count = 0;
        foreach ($allRows as $rowIndex => $row) {
            $hasData = false;
            foreach ($row as $colKey => $val) {
                if ($val !== null && trim((string) $val) !== '') {
                    $hasData = true;
                    if (! in_array($colKey, $columnsFound, true)) {
                        $columnsFound[] = $colKey;
                    }
                }
            }

            if ($hasData || $count < 10) {
                $sampleRows[$rowIndex] = $row;
                $count++;
            }

            if ($count >= $maxSampleRows) {
                break;
            }
        }

        // Urutkan kolom secara alfabetis (A, B, C, ... Z, AA, dst.)
        sort($columnsFound);

        return [
            'sheets' => $sheetNames,
            'active_sheet' => $activeSheetName,
            'sample_rows' => $sampleRows,
            'columns' => $columnsFound,
        ];
    }

    /**
     * Process Excel file, parse Sheet with dynamic or preset mapping, and save to staging tables.
     *
     * @param  array<string, mixed>  $mapping
     */
    public function importFile(string $filePath, string $originalFilename, ?int $userId = null, array $mapping = []): ImportBatch
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

        $targetSheetName = $mapping['sheet_name'] ?? null;
        $sheet = ($targetSheetName ? $spreadsheet->getSheetByName($targetSheetName) : null)
            ?? $spreadsheet->getSheetByName('Jurnal Umum')
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

        // Check if dynamic mapping is provided
        $isDynamic = ! empty($mapping) && ! empty($mapping['col_account']) && ! empty($mapping['col_debit']) && ! empty($mapping['col_credit']);

        // Fallback: Detect if this is a Cleaned Output File or Raw Excel
        $isCleanedFormat = ! $isDynamic && $this->isCleanedPresetFormat($rows);
        $startRowIndex = $isDynamic ? (int) ($mapping['start_row'] ?? 2) : ($isCleanedFormat ? 4 : 10);

        return DB::transaction(function () use ($filePath, $originalFilename, $fileHash, $userId, $sheet, $rows, $accountMap, $isCleanedFormat, $startRowIndex, $isDynamic, $mapping) {
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

                if ($isDynamic) {
                    // Dynamic User Mapping
                    $colDate = strtoupper(trim((string) ($mapping['col_date'] ?? '')));
                    $colDocNo = strtoupper(trim((string) ($mapping['col_doc_no'] ?? '')));
                    $colDesc = strtoupper(trim((string) ($mapping['col_desc'] ?? '')));
                    $colAccount = strtoupper(trim((string) ($mapping['col_account'] ?? '')));
                    $colSubAccount = strtoupper(trim((string) ($mapping['col_sub_account'] ?? '')));
                    $colUnit = strtoupper(trim((string) ($mapping['col_unit'] ?? '')));
                    $colDebit = strtoupper(trim((string) ($mapping['col_debit'] ?? '')));
                    $colCredit = strtoupper(trim((string) ($mapping['col_credit'] ?? '')));

                    $rawDate = $colDate !== '' ? $this->resolveCellValue($row[$colDate] ?? null, $sheet, $colDate, $rowIndex) : null;
                    $entryDate = $this->parseDate($rawDate);

                    $documentNumber = $colDocNo !== '' ? trim((string) ($this->resolveCellValue($row[$colDocNo] ?? null, $sheet, $colDocNo, $rowIndex) ?? '')) : '';
                    $description = $colDesc !== '' ? trim((string) ($this->resolveCellValue($row[$colDesc] ?? null, $sheet, $colDesc, $rowIndex) ?? '')) : '';

                    // Account mapping: Prioritize sub-account if provided, fallback to parent account
                    $rawParentAccount = $colAccount !== '' ? trim((string) ($this->resolveCellValue($row[$colAccount] ?? null, $sheet, $colAccount, $rowIndex) ?? '')) : '';
                    $rawSubAccount = $colSubAccount !== '' ? trim((string) ($this->resolveCellValue($row[$colSubAccount] ?? null, $sheet, $colSubAccount, $rowIndex) ?? '')) : '';
                    $rawAccountCode = ! empty($rawSubAccount) ? $rawSubAccount : $rawParentAccount;

                    $rawUnitVal = $colUnit !== '' ? trim((string) ($this->resolveCellValue($row[$colUnit] ?? null, $sheet, $colUnit, $rowIndex) ?? '')) : '';
                    $rawDebit = $colDebit !== '' ? $this->resolveCellValue($row[$colDebit] ?? null, $sheet, $colDebit, $rowIndex) : 0;
                    $rawCredit = $colCredit !== '' ? $this->resolveCellValue($row[$colCredit] ?? null, $sheet, $colCredit, $rowIndex) : 0;

                    // Detect unit: from column first, fallback to text description
                    $unitId = null;
                    if ($rawUnitVal !== '') {
                        $matchedUnit = Unit::where('code', $rawUnitVal)->orWhere('name', $rawUnitVal)->first();
                        $unitId = $matchedUnit?->id;
                    }
                    if (! $unitId) {
                        $unitId = $this->unitMappingService->detectUnitId($rawUnitVal.' '.$description);
                    }
                } elseif ($isCleanedFormat) {
                    // Cleaned Preset Column Mapping: B=Tanggal, C=No.Bukti, D=Keterangan, E=Kode Akun, G=Unit, H=Debit, I=Kredit
                    $rawDate = $this->resolveCellValue($row['B'] ?? null, $sheet, 'B', $rowIndex);
                    $entryDate = $this->parseDate($rawDate);

                    $documentNumber = trim((string) ($this->resolveCellValue($row['C'] ?? null, $sheet, 'C', $rowIndex) ?? ''));
                    $description = trim((string) ($this->resolveCellValue($row['D'] ?? null, $sheet, 'D', $rowIndex) ?? ''));
                    $rawAccountCode = trim((string) ($this->resolveCellValue($row['E'] ?? null, $sheet, 'E', $rowIndex) ?? ''));

                    $rawDebit = $this->resolveCellValue($row['H'] ?? null, $sheet, 'H', $rowIndex);
                    $rawCredit = $this->resolveCellValue($row['I'] ?? null, $sheet, 'I', $rowIndex);
                    $unitId = $this->unitMappingService->detectUnitId($description);
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
                    $unitId = $this->unitMappingService->detectUnitId($description);
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

                // Rule: Abaikan baris jika tidak memiliki kode akun (kode akun kosong)
                if (empty($rawAccountCode)) {
                    continue;
                }

                $debit = (float) str_replace(',', '.', (string) ($rawDebit ?? 0));
                $credit = (float) str_replace(',', '.', (string) ($rawCredit ?? 0));

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
