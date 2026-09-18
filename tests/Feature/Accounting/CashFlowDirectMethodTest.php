<?php

use App\Domain\Accounting\Services\AccountSeederService;
use App\Domain\Accounting\Services\CashFlowService;
use App\Domain\Accounting\Services\JournalPostingService;
use App\Domain\Accounting\Services\YearEndClosingService;
use App\Livewire\Accounting\Reports\CashFlow;
use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\CashFlowRow;
use App\Models\Company;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');

    $this->company = Company::firstOrCreate(
        ['code' => 'ARTALEDGER'],
        [
            'id' => 1,
            'name' => 'PT ArtaLedger Enterprise',
            'fiscal_year_start' => 1,
            'is_active' => true,
        ]
    );

    Unit::firstOrCreate(
        ['id' => 1],
        [
            'company_id' => 1,
            'name' => 'Kantor Pusat',
            'code' => 'KP',
            'is_active' => true,
        ]
    );

    (new AccountSeederService)->seedFromData();
});

test('cash flow service calculates direct method statement structure', function () {
    $service = app(CashFlowService::class);
    $statement = $service->calculateStatement('2025-01-01', '2025-01-31', 'all');

    expect($statement)->toHaveKeys([
        'startDate',
        'endDate',
        'unitFilter',
        'openingCash',
        'sections',
        'totalOperating',
        'totalInvesting',
        'totalFinancing',
        'netCashFlow',
        'endingCash',
    ]);

    expect($statement['sections'])->toHaveKeys(['operating', 'investing', 'financing']);
    expect($statement['sections']['operating']['rows'])->toBeArray();
});

test('cash flow livewire component renders direct method sections and rows', function () {
    Livewire::actingAs($this->user)
        ->test(CashFlow::class)
        ->assertOk()
        ->assertSee('A. ARUS KAS DARI KEGIATAN OPERASI')
        ->assertSee('B. ARUS KAS UNTUK KEGIATAN INVESTASI')
        ->assertSee('C. ARUS KAS PEMBIAYAAN')
        ->assertSee('KENAIKAN BERSIH KAS (A + B + C)')
        ->assertSee('SALDO KAS, AKHIR PERIODE');
});

test('cash flow interactive drilldown toggles row breakdown accounts', function () {
    $row = CashFlowRow::where('section', 'operating')->first();
    if (! $row) {
        $row = CashFlowRow::create([
            'company_id' => 1,
            'section' => 'operating',
            'label' => 'Penerimaan Kas Pelanggan',
            'source_type' => 'account_group',
            'account_group_id' => AccountGroup::first()?->id,
            'calculation_type' => 'net_mutation',
            'operator_sign' => '+',
            'order_index' => 1,
        ]);
    }

    $component = Livewire::actingAs($this->user)
        ->test(CashFlow::class)
        ->call('toggleRow', $row->id);

    expect($component->get('expandedRows'))->toContain($row->id);
    expect($component->get('rowBreakdowns'))->toHaveKey($row->id);

    // Toggle off
    $component->call('toggleRow', $row->id);
    expect($component->get('expandedRows'))->not->toContain($row->id);
});

test('cash flow livewire component supports CRUD operations for rows', function () {
    $group = AccountGroup::firstOrCreate(['name' => 'Grup Uji Arus Kas'], ['code' => 'GRP-TEST', 'company_id' => 1]);

    $component = Livewire::actingAs($this->user)
        ->test(CashFlow::class)
        ->call('openManageModal')
        ->assertSet('showManageModal', true)
        ->call('createRow', 'investing')
        ->assertSet('showRowFormModal', true)
        ->assertSet('row_section', 'investing')
        ->set('row_label', 'Penjualan Aset Tetap Uji')
        ->set('row_source_type', 'account_group')
        ->set('row_account_group_id', $group->id)
        ->set('row_operator_sign', '+')
        ->set('row_order_index', 99)
        ->call('saveRow')
        ->assertSet('showRowFormModal', false);

    $createdRow = CashFlowRow::where('label', 'Penjualan Aset Tetap Uji')->first();
    expect($createdRow)->not->toBeNull();
    expect($createdRow->section)->toBe('investing');

    // Edit row
    $component->call('editRow', $createdRow->id)
        ->assertSet('editingRowId', $createdRow->id)
        ->assertSet('row_label', 'Penjualan Aset Tetap Uji')
        ->set('row_label', 'Penjualan Aset Tetap Diubah')
        ->call('saveRow');

    expect($createdRow->fresh()->label)->toBe('Penjualan Aset Tetap Diubah');

    // Delete row
    $component->call('deleteRow', $createdRow->id);
    expect(CashFlowRow::find($createdRow->id))->toBeNull();
});

test('cash flow export pdf and excel endpoints respond successfully', function () {
    $this->actingAs($this->user);

    $pdfResponse = $this->get(route('accounting.reports.export.pdf', [
        'type' => 'cash-flow',
        'start_date' => '2025-01-01',
        'end_date' => '2025-01-31',
    ]));
    $pdfResponse->assertOk();

    $excelResponse = $this->get(route('accounting.reports.export.excel', [
        'type' => 'cash-flow',
        'start_date' => '2025-01-01',
        'end_date' => '2025-01-31',
    ]));
    $excelResponse->assertOk();
});

test('cash flow opening cash isolates prior year mutations when year-end closing rollover exists', function () {
    $kasAccount = Account::where('type', 'KAS')->where('is_group', false)->first();
    $modalAccount = Account::where('report_type', 'neraca')->where('normal_balance', 'credit')->where('code', 'like', '3%')->where('is_group', false)->first();
    $pendapatanAccount = Account::where('report_type', 'laba_rugi')->where('normal_balance', 'credit')->where('is_group', false)->first();

    $postingService = new JournalPostingService;

    // 1. Transaksi 2025: Setoran Modal Kas 10.000.000
    $postingService->postManualEntry([
        'company_id' => 1,
        'entry_date' => '2025-01-01',
        'document_number' => 'DOC-CAPITAL-2025',
        'description' => 'Setoran Modal Kas',
    ], [
        ['account_id' => $kasAccount->id, 'description' => 'Kas Masuk Modal', 'debit' => 10000000, 'credit' => 0],
        ['account_id' => $modalAccount->id, 'description' => 'Modal Disetor', 'debit' => 0, 'credit' => 10000000],
    ], $this->user->id);

    // 2. Transaksi Operasional 2025: Pendapatan Kas 5.000.000 -> Total Kas 2025 = 15.000.000
    $postingService->postManualEntry([
        'company_id' => 1,
        'entry_date' => '2025-05-15',
        'document_number' => 'DOC-REV-2025',
        'description' => 'Pendapatan Kas 2025',
    ], [
        ['account_id' => $kasAccount->id, 'description' => 'Kas Masuk Pendapatan', 'debit' => 5000000, 'credit' => 0],
        ['account_id' => $pendapatanAccount->id, 'description' => 'Pendapatan', 'debit' => 0, 'credit' => 5000000],
    ], $this->user->id);

    // 3. Jalankan Tutup Buku 2025 -> menghasilkan SA-2026-001 dengan Kas 15.000.000
    YearEndClosingService::rolloverYearEndOpeningBalance(2025, $this->user->id);

    // 4. Transaksi Baru di Januari 2026: Pendapatan Kas 2.000.000
    $postingService->postManualEntry([
        'company_id' => 1,
        'entry_date' => '2026-01-10',
        'document_number' => 'DOC-REV-2026',
        'description' => 'Pendapatan Kas 2026',
    ], [
        ['account_id' => $kasAccount->id, 'description' => 'Kas Masuk 2026', 'debit' => 2000000, 'credit' => 0],
        ['account_id' => $pendapatanAccount->id, 'description' => 'Pendapatan', 'debit' => 0, 'credit' => 2000000],
    ], $this->user->id);

    // 5. Uji Service Cash Flow untuk Januari 2026
    $service = app(CashFlowService::class);
    $statement = $service->calculateStatement('2026-01-01', '2026-01-31', 'all', $this->company->id);

    // Saldo kas awal 2026 harus tepat 15.000.000 (TIDAK boleh double counting menjadi 30.000.000)
    expect((float) $statement['openingCash'])->toBe(15000000.0);

    // Kenaikan bersih kas Januari 2026 adalah 2.000.000
    expect((float) $statement['netCashFlow'])->toBe(2000000.0);

    // Saldo kas akhir periode harus tepat 17.000.000
    expect((float) $statement['endingCash'])->toBe(17000000.0);
    expect((float) $statement['closingBalance'])->toBe(17000000.0);
});
