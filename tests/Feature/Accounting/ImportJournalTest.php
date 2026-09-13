<?php

use App\Domain\Import\Services\ExcelImportService;
use App\Domain\Import\Services\ImportValidationService;
use App\Domain\Import\Services\UnitMappingService;
use App\Livewire\Accounting\Import\JournalImportWizard;
use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    $this->actingAs($this->user);

    $this->company = Company::firstOrCreate([
        'code' => 'AL',
    ], [
        'name' => 'PT Arta Ledger',
    ]);

    AccountingPeriod::create([
        'company_id' => $this->company->id,
        'year' => 2026,
        'month' => 1,
        'period_name' => 'Januari 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'open',
    ]);
});

it('can render journal import wizard page', function () {
    Livewire::test(JournalImportWizard::class)
        ->assertStatus(200)
        ->assertSee('Import Jurnal Transaksi Excel');
});

it('can detect unit from text using UnitMappingService', function () {
    Unit::create([
        'code' => 'RST',
        'name' => 'RS Tandun',
        'keywords' => 'RST, TANDUN',
    ]);

    $service = new UnitMappingService;

    $unitId = $service->detectUnitId('Pembayaran Listrik RS Tandun');
    expect($unitId)->not->toBeNull();
});

it('validates account existence in ImportValidationService', function () {
    Account::create([
        'company_id' => $this->company->id,
        'code' => '11.01.01',
        'name' => 'Kas Kantor Pusat',
        'type' => 'asset',
        'normal_balance' => 'debit',
        'report_type' => 'neraca',
        'is_group' => false,
        'is_active' => true,
    ]);

    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Jurnal Umum');

    // Header (row 1)
    $sheet->setCellValue('M1', 'Tanggal');
    $sheet->setCellValue('N1', 'No. Bukti');
    $sheet->setCellValue('O1', 'Keterangan');
    $sheet->setCellValue('S1', 'Sub Akun');
    $sheet->setCellValue('T1', 'Debit');
    $sheet->setCellValue('U1', 'Kredit');

    // Data Row 1 (row 10)
    $sheet->setCellValue('M10', '2026-01-15');
    $sheet->setCellValue('N10', 'BM-001');
    $sheet->setCellValue('O10', 'Kas Masuk RS Tandun');
    $sheet->setCellValue('S10', '11.01.01');
    $sheet->setCellValue('T10', 100000);
    $sheet->setCellValue('U10', 0);

    // Data Row 2 (row 11)
    $sheet->setCellValue('M11', '2026-01-15');
    $sheet->setCellValue('N11', 'BM-001');
    $sheet->setCellValue('O11', 'Kas Masuk RS Tandun');
    $sheet->setCellValue('S11', '11.01.01');
    $sheet->setCellValue('T11', 0);
    $sheet->setCellValue('U11', 100000);

    $tempPath = tempnam(sys_get_temp_dir(), 'excel_test_').'.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempPath);

    $service = new ExcelImportService;
    $batch = $service->importFile($tempPath, 'test_jurnal.xlsx', $this->user->id);

    expect($batch->total_rows)->toBe(2);

    $validator = new ImportValidationService;
    $batch = $validator->validateBatch($batch);

    expect($batch->status)->toBe('validated');
    expect($batch->error_rows)->toBe(0);

    @unlink($tempPath);
});

it('ignores excel rows that do not have account code without causing validation errors', function () {
    Account::create([
        'company_id' => $this->company->id,
        'code' => '11.01.01',
        'name' => 'Kas Kantor Pusat',
        'type' => 'asset',
        'normal_balance' => 'debit',
        'report_type' => 'neraca',
        'is_group' => false,
        'is_active' => true,
    ]);

    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Jurnal Umum');

    // Header
    $sheet->setCellValue('M1', 'Tanggal');
    $sheet->setCellValue('N1', 'No. Bukti');
    $sheet->setCellValue('O1', 'Keterangan');
    $sheet->setCellValue('S1', 'Sub Akun');
    $sheet->setCellValue('T1', 'Debit');
    $sheet->setCellValue('U1', 'Kredit');

    // Baris 1: Valid
    $sheet->setCellValue('M10', '2026-01-15');
    $sheet->setCellValue('N10', 'BM-001');
    $sheet->setCellValue('O10', 'Kas Masuk');
    $sheet->setCellValue('S10', '11.01.01');
    $sheet->setCellValue('T10', 500000);
    $sheet->setCellValue('U10', 0);

    // Baris 2: KODE AKUN KOSONG (seperti kasus baris 2170 & 2171)
    $sheet->setCellValue('M11', '2026-01-15');
    $sheet->setCellValue('N11', 'BM-001');
    $sheet->setCellValue('O11', 'Biaya Pengganti tanpa kode akun');
    $sheet->setCellValue('S11', ''); // KOSONG
    $sheet->setCellValue('Q11', ''); // KOSONG
    $sheet->setCellValue('T11', 250000);
    $sheet->setCellValue('U11', 0);

    // Baris 3: KODE AKUN KOSONG dan TANGGAL KOSONG (seperti kasus baris 4705)
    $sheet->setCellValue('M12', '');
    $sheet->setCellValue('N12', '-');
    $sheet->setCellValue('O12', '-');
    $sheet->setCellValue('S12', '');
    $sheet->setCellValue('T12', 10000);
    $sheet->setCellValue('U12', 0);

    $tempPath = tempnam(sys_get_temp_dir(), 'excel_ignore_test_').'.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempPath);

    $service = new ExcelImportService;
    $batch = $service->importFile($tempPath, 'test_ignore.xlsx', $this->user->id);

    // Hanya baris 10 yang terimpor, baris 11 & 12 diabaikan
    expect($batch->total_rows)->toBe(1);

    $validator = new ImportValidationService;
    $batch = $validator->validateBatch($batch);

    expect($batch->status)->toBe('validated');
    expect($batch->error_rows)->toBe(0);

    @unlink($tempPath);
});
