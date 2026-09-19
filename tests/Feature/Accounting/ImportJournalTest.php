<?php

use App\Domain\Import\Services\ExcelImportService;
use App\Domain\Import\Services\ImportValidationService;
use App\Domain\Import\Services\UnitMappingService;
use App\Livewire\Accounting\Import\JournalImportWizard;
use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\ImportMappingPreset;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Http\UploadedFile;
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

it('can inspect spreadsheet and extract sheets and sample rows', function () {
    $spreadsheet = new Spreadsheet;
    $sheet1 = $spreadsheet->getActiveSheet();
    $sheet1->setTitle('Sheet Transaksi');

    $sheet1->setCellValue('A1', 'Tgl');
    $sheet1->setCellValue('B1', 'Keterangan');
    $sheet1->setCellValue('C1', 'Kode Akun');
    $sheet1->setCellValue('D1', 'Debit');
    $sheet1->setCellValue('E1', 'Kredit');

    $sheet1->setCellValue('A2', '2026-02-01');
    $sheet1->setCellValue('B2', 'Beli Kertas');
    $sheet1->setCellValue('C2', '51.01.01');
    $sheet1->setCellValue('D2', 50000);
    $sheet1->setCellValue('E2', 0);

    $spreadsheet->createSheet()->setTitle('Laporan');

    $tempPath = tempnam(sys_get_temp_dir(), 'excel_inspect_').'.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempPath);

    $service = new ExcelImportService;
    $inspection = $service->inspectSpreadsheet($tempPath, 'Sheet Transaksi', 5);

    expect($inspection['sheets'])->toContain('Sheet Transaksi', 'Laporan');
    expect($inspection['active_sheet'])->toBe('Sheet Transaksi');
    expect($inspection['sample_rows'])->toHaveCount(2);
    expect($inspection['sample_rows'][1]['B'])->toBe('Keterangan');
    expect($inspection['sample_rows'][2]['B'])->toBe('Beli Kertas');
    expect($inspection['columns'])->toContain('A', 'B', 'C', 'D', 'E');

    @unlink($tempPath);
});

it('can import file with custom dynamic column mapping', function () {
    Account::create([
        'company_id' => $this->company->id,
        'code' => '51.01.01',
        'name' => 'Beban Perlengkapan',
        'type' => 'expense',
        'normal_balance' => 'debit',
        'report_type' => 'laba_rugi',
        'is_group' => false,
        'is_active' => true,
    ]);
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
    $sheet->setTitle('Data Kustom');

    // Layout Kustom: Data mulai baris 3
    // Kolom B = Tanggal, Kolom C = No Bukti, Kolom D = Deskripsi, Kolom F = Akun, Kolom H = Debit, Kolom I = Kredit
    $sheet->setCellValue('B3', '2026-01-20');
    $sheet->setCellValue('C3', 'BK-999');
    $sheet->setCellValue('D3', 'Beli Kertas Kantor');
    $sheet->setCellValue('F3', '51.01.01');
    $sheet->setCellValue('H3', 150000);
    $sheet->setCellValue('I3', 0);

    $sheet->setCellValue('B4', '2026-01-20');
    $sheet->setCellValue('C4', 'BK-999');
    $sheet->setCellValue('D4', 'Beli Kertas Kantor');
    $sheet->setCellValue('F4', '11.01.01');
    $sheet->setCellValue('H4', 0);
    $sheet->setCellValue('I4', 150000);

    $tempPath = tempnam(sys_get_temp_dir(), 'excel_custom_').'.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempPath);

    $service = new ExcelImportService;
    $mapping = [
        'sheet_name' => 'Data Kustom',
        'start_row' => 3,
        'col_date' => 'B',
        'col_doc_no' => 'C',
        'col_description' => 'D',
        'col_account' => 'F',
        'col_unit' => '',
        'col_debit' => 'H',
        'col_credit' => 'I',
    ];

    $batch = $service->importFile($tempPath, 'test_custom.xlsx', $this->user->id, $mapping);

    expect($batch->total_rows)->toBe(2);
    expect((float) $batch->rows()->sum('debit'))->toEqual(150000.0);
    expect((float) $batch->rows()->sum('credit'))->toEqual(150000.0);

    $validator = new ImportValidationService;
    $batch = $validator->validateBatch($batch);

    expect($batch->status)->toBe('validated');
    expect($batch->error_rows)->toBe(0);

    @unlink($tempPath);
});

it('can manage custom import mapping presets', function () {
    $preset = ImportMappingPreset::create([
        'user_id' => $this->user->id,
        'name' => 'Format Bank Mandiri',
        'description' => 'Mapping mutasi rekening',
        'start_row' => 5,
        'mapping_config' => [
            'col_date' => 'A',
            'col_doc_no' => 'B',
            'col_description' => 'C',
            'col_account' => 'D',
            'col_unit' => '',
            'col_debit' => 'E',
            'col_credit' => 'F',
        ],
    ]);

    expect($preset->id)->not->toBeNull();
    expect($preset->name)->toBe('Format Bank Mandiri');
    expect($preset->mapping_config['col_date'])->toBe('A');
    expect($preset->start_row)->toBe(5);
    expect($preset->user->id)->toBe($this->user->id);
});

it('can upload file in livewire component and advance to step 2', function () {
    Storage::fake('local');

    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Sheet1');
    $sheet->setCellValue('A1', 'Tanggal');
    $sheet->setCellValue('B1', 'Deskripsi');

    $tempPath = tempnam(sys_get_temp_dir(), 'livewire_upload_test_').'.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempPath);
    $content = file_get_contents($tempPath);
    @unlink($tempPath);

    $uploadedFile = UploadedFile::fake()->createWithContent(
        'test_upload.xlsx',
        $content
    );

    Livewire::test(JournalImportWizard::class)
        ->set('file', $uploadedFile)
        ->assertSet('wizardStep', 2)
        ->assertSet('originalFilename', 'test_upload.xlsx')
        ->assertSee('Sheet1');
});

it('can switch to settings tab and manage presets in livewire component', function () {
    $preset = ImportMappingPreset::create([
        'user_id' => $this->user->id,
        'name' => 'Format Bank BCA',
        'sheet_name' => 'Mutasi',
        'start_row' => 3,
        'mapping_config' => [
            'col_date' => 'A',
            'col_doc_no' => 'B',
            'col_desc' => 'C',
            'col_account' => 'D',
            'col_unit' => '',
            'col_debit' => 'E',
            'col_credit' => 'F',
        ],
    ]);

    Livewire::test(JournalImportWizard::class)
        ->call('switchTab', 'settings')
        ->assertSet('activeTab', 'settings')
        ->assertSee('Format Bank BCA')
        ->assertSee('Standard Bersih (Cleaned)')
        ->assertSee('Template Mentah (Legacy)')
        ->call('openEditPresetModal', $preset->id)
        ->assertSet('editingPresetId', $preset->id)
        ->assertSet('editPresetName', 'Format Bank BCA')
        ->assertSet('showEditPresetModal', true)
        ->set('editPresetName', 'Format Bank BCA Updated')
        ->set('editStartRow', 4)
        ->call('updatePreset')
        ->assertSet('showEditPresetModal', false);

    expect($preset->fresh()->name)->toBe('Format Bank BCA Updated');
    expect($preset->fresh()->start_row)->toBe(4);

    // Test creating a new preset directly from settings tab
    Livewire::test(JournalImportWizard::class)
        ->call('switchTab', 'settings')
        ->call('openCreatePresetModal')
        ->assertSet('editingPresetId', null)
        ->assertSet('showEditPresetModal', true)
        ->set('editPresetName', 'Format CSV POS Baru')
        ->set('editStartRow', 2)
        ->set('editColDate', 'A')
        ->set('editColDocNo', 'B')
        ->set('editColAccount', 'C')
        ->set('editColSubAccount', 'D')
        ->set('editColDebit', 'E')
        ->set('editColCredit', 'F')
        ->call('updatePreset')
        ->assertSet('showEditPresetModal', false);

    $newPreset = ImportMappingPreset::where('name', 'Format CSV POS Baru')->first();
    expect($newPreset)->not->toBeNull();
    expect($newPreset->mapping_config['col_account'])->toBe('C');
    expect($newPreset->mapping_config['col_sub_account'])->toBe('D');
});

it('supports dynamic import with sub-account column fallback to parent account', function () {
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

    Account::create([
        'company_id' => $this->company->id,
        'code' => '21.01.01',
        'name' => 'Hutang Usaha',
        'type' => 'liability',
        'normal_balance' => 'credit',
        'report_type' => 'neraca',
        'is_group' => false,
        'is_active' => true,
    ]);

    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Sheet1');

    // Row 1: Header
    $sheet->setCellValue('A1', 'Tgl');
    $sheet->setCellValue('B1', 'Akun Induk');
    $sheet->setCellValue('C1', 'Sub Akun');
    $sheet->setCellValue('D1', 'Debit');
    $sheet->setCellValue('E1', 'Kredit');

    // Row 2: Sub-account is present -> should use Sub-account (11.01.01)
    $sheet->setCellValue('A2', '2026-01-15');
    $sheet->setCellValue('B2', '11.01'); // Parent
    $sheet->setCellValue('C2', '11.01.01'); // Sub
    $sheet->setCellValue('D2', 500000);
    $sheet->setCellValue('E2', 0);

    // Row 3: Sub-account is empty -> should fallback to Parent account (21.01.01)
    $sheet->setCellValue('A3', '2026-01-15');
    $sheet->setCellValue('B3', '21.01.01'); // Parent
    $sheet->setCellValue('C3', ''); // Sub empty
    $sheet->setCellValue('D3', 0);
    $sheet->setCellValue('E3', 500000);

    $tempPath = tempnam(sys_get_temp_dir(), 'test_sub_fallback_').'.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempPath);

    $importService = app(ExcelImportService::class);
    $batch = $importService->importFile($tempPath, 'test_sub_fallback.xlsx', $this->user->id, [
        'sheet_name' => 'Sheet1',
        'start_row' => 2,
        'col_date' => 'A',
        'col_doc_no' => '',
        'col_desc' => '',
        'col_account' => 'B',
        'col_sub_account' => 'C',
        'col_unit' => '',
        'col_debit' => 'D',
        'col_credit' => 'E',
    ]);

    @unlink($tempPath);

    expect($batch->total_rows)->toBe(2);

    $rows = $batch->rows()->orderBy('row_index')->get();
    expect($rows[0]->raw_account_code)->toBe('11.01.01');
    expect($rows[1]->raw_account_code)->toBe('21.01.01');
    expect((float) $batch->rows()->sum('debit'))->toBe(500000.0);
    expect((float) $batch->rows()->sum('credit'))->toBe(500000.0);
});
