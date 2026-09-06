<?php

use App\Domain\Accounting\Services\AccountSeederService;
use App\Domain\Asset\Services\FixedAssetDepreciationService;
use App\Livewire\Accounting\Assets\DepreciationRun;
use App\Livewire\Accounting\Assets\FixedAssetIndex;
use App\Models\AccountingPeriod;
use App\Models\AssetCategory;
use App\Models\AssetDepreciation;
use App\Models\Company;
use App\Models\FixedAsset;
use App\Models\JournalEntry;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\AssetCategorySeeder;
use Database\Seeders\JournalTypeSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(JournalTypeSeeder::class);
    $this->company = Company::firstOrCreate(['code' => 'ALI'], ['name' => 'PT ARTA LEDGER INDONESIA']);
    $this->unit = Unit::firstOrCreate(['code' => 'KP'], ['name' => 'Kantor Pusat']);
    $this->user = User::factory()->create();
    $adminRole = Role::where('name', 'Super Admin')->first();
    if ($adminRole) {
        $this->user->assignRole($adminRole);
    }
    (new AccountSeederService)->seedFromData();
    $this->seed(AssetCategorySeeder::class);

    // Create 2025 Accounting Periods
    for ($m = 1; $m <= 12; $m++) {
        AccountingPeriod::firstOrCreate(
            ['company_id' => $this->company->id, 'year' => 2025, 'month' => $m],
            [
                'start_date' => sprintf('2025-%02d-01', $m),
                'end_date' => Carbon\Carbon::create(2025, $m, 1)->endOfMonth()->toDateString(),
                'status' => 'open',
            ]
        );
    }
});

test('straight line calculation divides depreciable base correctly', function () {
    $service = new FixedAssetDepreciationService;

    // Kendaraan: 120,000,000, Residu: 24,000,000, Masa Manfaat: 48 bulan
    // Dasar = 96,000,000 / 48 = 2,000,000
    $monthly = $service->calculateMonthlyStraightLine(120000000, 24000000, 48);
    expect($monthly)->toBe(2000000.0);

    // Tanpa residu: 10,000,000 / 12 = 833333.33
    $monthlyZeroSalvage = $service->calculateMonthlyStraightLine(10000000, 0, 12);
    expect($monthlyZeroSalvage)->toBe(833333.33);
});

test('can create fixed asset with automatic code and values', function () {
    $service = new FixedAssetDepreciationService;
    $category = AssetCategory::where('code', 'KAT-KND')->firstOrFail();

    $asset = $service->createAsset([
        'company_id' => $this->company->id,
        'unit_id' => $this->unit->id,
        'asset_category_id' => $category->id,
        'name' => 'Toyota Hilux 2.4 G',
        'acquisition_date' => '2025-01-05',
        'acquisition_cost' => 240000000,
        'salvage_value' => 0,
        'useful_life_months' => 48,
    ], $this->user);

    expect($asset->asset_code)->toContain('AST-KP-2025');
    expect((float) $asset->monthly_depreciation_amount)->toBe(5000000.0);
    expect((float) $asset->book_value)->toBe(240000000.0);
    expect($asset->status)->toBe('active');
});

test('generates accurate amortization schedule up to useful life', function () {
    $service = new FixedAssetDepreciationService;
    $category = AssetCategory::where('code', 'KAT-MSN')->firstOrFail();

    $asset = $service->createAsset([
        'company_id' => $this->company->id,
        'unit_id' => $this->unit->id,
        'asset_category_id' => $category->id,
        'name' => 'Server Dell PowerEdge',
        'acquisition_date' => '2025-01-01',
        'acquisition_cost' => 12000000,
        'salvage_value' => 0,
        'useful_life_months' => 12,
    ], $this->user);

    $schedule = $service->generateSchedule($asset);

    expect(count($schedule))->toBe(12);
    expect($schedule[0]['period'])->toBe('2025-01');
    expect($schedule[0]['depreciation_amount'])->toBe(1000000.0);
    expect($schedule[11]['period'])->toBe('2025-12');
    expect($schedule[11]['accumulated_depreciation'])->toBe(12000000.0);
    expect($schedule[11]['book_value'])->toBe(0.0);
});

test('runs monthly depreciation and creates balanced journal entry', function () {
    $service = new FixedAssetDepreciationService;
    $category = AssetCategory::where('code', 'KAT-KND')->firstOrFail();

    $asset = $service->createAsset([
        'company_id' => $this->company->id,
        'unit_id' => $this->unit->id,
        'asset_category_id' => $category->id,
        'name' => 'Operasional Avanza',
        'acquisition_date' => '2025-01-01',
        'start_depreciation_date' => '2025-01-01',
        'acquisition_cost' => 120000000,
        'salvage_value' => 0,
        'useful_life_months' => 24,
    ], $this->user);

    // Run for 2025-01
    $result = $service->runDepreciationForPeriod('2025-01', $this->unit->id, $this->user);

    expect($result['success'])->toBeTrue();
    expect($result['count'])->toBe(1);
    expect($result['total_amount'])->toBe(5000000.0);

    // Refresh asset
    $asset->refresh();
    expect((float) $asset->accumulated_depreciation)->toBe(5000000.0);
    expect((float) $asset->book_value)->toBe(115000000.0);
    expect($asset->last_depreciated_period)->toBe('2025-01');

    // Verify depreciation log
    $dep = AssetDepreciation::where('fixed_asset_id', $asset->id)->first();
    expect($dep)->not->toBeNull();
    expect($dep->period)->toBe('2025-01');
    expect((float) $dep->amount)->toBe(5000000.0);

    // Verify Journal Entry
    $journal = JournalEntry::find($dep->journal_entry_id);
    expect($journal)->not->toBeNull();
    expect($journal->entry_type)->toBe('adjustment');
    expect($journal->source_type)->toBe('depreciation');
    expect($journal->is_balanced)->toBeTrue();
    expect($journal->total_debit)->toBe(5000000.0);
    expect($journal->total_credit)->toBe(5000000.0);

    // Verify Journal Lines: Debit Beban (68.04), Credit Akumulasi (12.10.5)
    $debitLine = $journal->lines()->where('debit', '>', 0)->first();
    $creditLine = $journal->lines()->where('credit', '>', 0)->first();
    expect($debitLine->account->code)->toBe('68.04');
    expect($creditLine->account->code)->toBe('12.10.5');
});

test('idempotency: running depreciation again for same period does not duplicate', function () {
    $service = new FixedAssetDepreciationService;
    $category = AssetCategory::where('code', 'KAT-KND')->firstOrFail();

    $asset = $service->createAsset([
        'company_id' => $this->company->id,
        'unit_id' => $this->unit->id,
        'asset_category_id' => $category->id,
        'name' => 'Operasional Xenia',
        'acquisition_date' => '2025-01-01',
        'start_depreciation_date' => '2025-01-01',
        'acquisition_cost' => 60000000,
        'salvage_value' => 0,
        'useful_life_months' => 12,
    ], $this->user);

    // Run 1st time
    $result1 = $service->runDepreciationForPeriod('2025-01', $this->unit->id, $this->user);
    expect($result1['count'])->toBe(1);

    // Run 2nd time for same period
    $result2 = $service->runDepreciationForPeriod('2025-01', $this->unit->id, $this->user);
    expect($result2['success'])->toBeFalse();
    expect($result2['count'])->toBe(0);

    // Only 1 depreciation record exists
    expect(AssetDepreciation::where('fixed_asset_id', $asset->id)->count())->toBe(1);
});

test('transitions to fully_depreciated when book value reaches salvage value', function () {
    $service = new FixedAssetDepreciationService;
    $category = AssetCategory::where('code', 'KAT-MSN')->firstOrFail();

    $asset = $service->createAsset([
        'company_id' => $this->company->id,
        'unit_id' => $this->unit->id,
        'asset_category_id' => $category->id,
        'name' => 'Printer HP LaserJet',
        'acquisition_date' => '2025-01-01',
        'start_depreciation_date' => '2025-01-01',
        'acquisition_cost' => 2000000,
        'salvage_value' => 0,
        'useful_life_months' => 1,
    ], $this->user);

    $service->runDepreciationForPeriod('2025-01', $this->unit->id, $this->user);

    $asset->refresh();
    expect((float) $asset->book_value)->toBe(0.0);
    expect($asset->status)->toBe('fully_depreciated');
});

test('fixed asset index page renders successfully and displays assets', function () {
    $category = AssetCategory::where('code', 'KAT-KND')->firstOrFail();
    FixedAsset::create([
        'company_id' => $this->company->id,
        'unit_id' => $this->unit->id,
        'asset_category_id' => $category->id,
        'asset_code' => 'AST-KP-2025-0001',
        'name' => 'Mitsubishi Triton 4x4',
        'acquisition_date' => '2025-01-01',
        'acquisition_cost' => 300000000,
        'salvage_value' => 0,
        'useful_life_months' => 60,
        'monthly_depreciation_amount' => 5000000,
        'book_value' => 300000000,
        'status' => 'active',
    ]);

    $this->actingAs($this->user)
        ->get(route('accounting.fixed-assets.index'))
        ->assertOk()
        ->assertSee('Mitsubishi Triton 4x4')
        ->assertSee('AST-KP-2025-0001');

    Livewire\Livewire::actingAs($this->user)
        ->test(FixedAssetIndex::class)
        ->assertSee('Mitsubishi Triton 4x4')
        ->assertSee('Rp 300.000.000');
});

test('depreciation run page renders and executes via livewire', function () {
    $category = AssetCategory::where('code', 'KAT-KND')->firstOrFail();
    FixedAsset::create([
        'company_id' => $this->company->id,
        'unit_id' => $this->unit->id,
        'asset_category_id' => $category->id,
        'asset_code' => 'AST-KP-2025-0002',
        'name' => 'Daihatsu Gran Max',
        'acquisition_date' => '2025-01-01',
        'start_depreciation_date' => '2025-01-01',
        'acquisition_cost' => 120000000,
        'salvage_value' => 0,
        'useful_life_months' => 24,
        'monthly_depreciation_amount' => 5000000,
        'book_value' => 120000000,
        'status' => 'active',
    ]);

    $this->actingAs($this->user)
        ->get(route('accounting.fixed-assets.depreciation'))
        ->assertOk()
        ->assertSee('Mesin Eksekusi Penyusutan Bulanan');

    Livewire\Livewire::actingAs($this->user)
        ->test(DepreciationRun::class)
        ->set('period', '2025-01')
        ->assertSee('Daihatsu Gran Max')
        ->call('executeDepreciation')
        ->assertHasNoErrors();

    // Verify depreciation was posted
    expect(AssetDepreciation::where('period', '2025-01')->count())->toBe(1);
    expect(JournalEntry::where('source_type', 'depreciation')->count())->toBe(1);
});
