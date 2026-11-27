<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Upgrade\TestCase;

uses(TestCase::class);

const SPEC0082_PREFIX = 'upg0082_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => SPEC0082_PREFIX]);
});

afterEach(function () {
    foreach (['stock_movements', 'stock_levels', 'product_variants', 'locations'] as $table) {
        Schema::dropIfExists(SPEC0082_PREFIX.$table);
    }
});

function sellingUpgradeMigration(string $name): object
{
    $path = glob(dirname(__DIR__, 3)."/packages/upgrade/database/migrations/*{$name}.php");

    return require $path[0];
}

/**
 * A v1 `product_variants` table with the mode in `purchasable`, the allowance in
 * an indexed `backorder` (v1 indexes it; SQLite cannot drop an indexed column),
 * and the flat `stock` column the stock backfill moves into stock levels first.
 */
function simulateV1SellingModes(): void
{
    Schema::create(SPEC0082_PREFIX.'product_variants', function (Blueprint $table) {
        $table->id();
        $table->integer('stock')->default(0)->index();
        $table->integer('backorder')->default(0)->index();
        $table->string('purchasable')->default('always')->index();
        $table->timestamps();
    });

    $rows = [
        [1, 'always', 4, 0],
        [2, 'in_stock', 4, 6],
        [3, 'in_stock_or_on_backorder', 4, 6],
        [4, 'in_stock_or_backorder', 0, 3],
        [5, 'something_rogue', 0, 0],
        [6, 'in_stock_or_on_backorder', 0, 0],
    ];

    DB::table(SPEC0082_PREFIX.'product_variants')->insert(array_map(fn (array $row) => [
        'id' => $row[0],
        'purchasable' => $row[1],
        'stock' => $row[2],
        'backorder' => $row[3],
        'created_at' => now(),
        'updated_at' => now(),
    ], $rows));
}

function runSellingUpgrade(): void
{
    sellingUpgradeMigration('backfill_stock_from_variants')->up();
    sellingUpgradeMigration('selling_policy_to_booleans')->up();
}

test('it maps every v1 mode onto the two flags and drops the old columns', function () {
    simulateV1SellingModes();

    runSellingUpgrade();

    $table = SPEC0082_PREFIX.'product_variants';

    expect(Schema::hasColumn($table, 'purchasable'))->toBeFalse()
        ->and(Schema::hasColumn($table, 'backorder'))->toBeFalse();

    $flags = DB::table($table)->orderBy('id')->get()
        ->mapWithKeys(fn ($variant) => [$variant->id => [(bool) $variant->continue_selling, (bool) $variant->sell_against_incoming]]);

    expect($flags->all())->toBe([
        1 => [true, false],
        2 => [false, false],
        3 => [false, true],
        4 => [false, true],  // typo counts as backorder
        5 => [true, false],  // rogue value falls back to always
        6 => [false, true],
    ]);
});

test('it carries a used backorder allowance into incoming stock at the default location', function () {
    simulateV1SellingModes();

    runSellingUpgrade();

    $incoming = DB::table(SPEC0082_PREFIX.'stock_levels')->pluck('incoming', 'product_variant_id')->map(fn ($value) => (int) $value);
    $rollup = DB::table(SPEC0082_PREFIX.'product_variants')->pluck('stock_incoming', 'id')->map(fn ($value) => (int) $value);

    // Variant 3 already had a level from its stock; variant 4 had none.
    // Variant 2's allowance was never consulted under in_stock, so it is dropped.
    expect($incoming->all())->toEqualCanonicalizing([1 => 0, 2 => 0, 3 => 6, 4 => 3])
        ->and($rollup->only([2, 3, 4, 6])->all())->toBe([2 => 0, 3 => 6, 4 => 3, 6 => 0]);

    $defaultLocation = DB::table(SPEC0082_PREFIX.'locations')->where('default', true)->value('id');

    expect(DB::table(SPEC0082_PREFIX.'stock_levels')->where('product_variant_id', 4)->value('location_id'))->toBe($defaultLocation)
        ->and((int) DB::table(SPEC0082_PREFIX.'stock_levels')->where('product_variant_id', 3)->value('on_hand'))->toBe(4);
});

test('it converts a database already renamed to selling_policy', function () {
    Schema::create(SPEC0082_PREFIX.'product_variants', function (Blueprint $table) {
        $table->id();
        $table->string('selling_policy')->default('always')->index();
        $table->timestamps();
    });

    DB::table(SPEC0082_PREFIX.'product_variants')->insert(['id' => 1, 'selling_policy' => 'in_stock', 'created_at' => now(), 'updated_at' => now()]);

    sellingUpgradeMigration('selling_policy_to_booleans')->up();

    $variant = DB::table(SPEC0082_PREFIX.'product_variants')->first();

    expect(Schema::hasColumn(SPEC0082_PREFIX.'product_variants', 'selling_policy'))->toBeFalse()
        ->and((bool) $variant->continue_selling)->toBeFalse()
        ->and((bool) $variant->sell_against_incoming)->toBeFalse();
});

test('it is idempotent across re-runs', function () {
    simulateV1SellingModes();

    runSellingUpgrade();
    sellingUpgradeMigration('selling_policy_to_booleans')->up();

    expect((int) DB::table(SPEC0082_PREFIX.'stock_levels')->where('product_variant_id', 3)->value('incoming'))->toBe(6);
});
