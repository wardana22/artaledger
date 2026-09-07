<?php

use App\Domain\Accounting\Services\AccountSeederService;
use App\Domain\Accounting\Services\CashFlowService;
use App\Livewire\Accounting\Reports\CashFlow;
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

    Company::firstOrCreate(
        ['id' => 1],
        [
            'name' => 'PT Test ArtaLedger',
            'code' => 'AL-TEST',
            'fiscal_year_start' => 1,
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
