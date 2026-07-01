<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Upgrade\TestCase;

uses(TestCase::class);

const SPEC0048_PREFIX = 'upg0048_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => SPEC0048_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(SPEC0048_PREFIX.'product_variants');
});

function sellingPolicyMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*rename_purchasable_to_selling_policy.php');

    return require $path[0];
}

function simulateV1VariantsWithPurchasable(): void
{
    Schema::create(SPEC0048_PREFIX.'product_variants', function (Blueprint $table) {
        $table->id();
        $table->string('purchasable')->default('always');
        $table->timestamps();
    });

    DB::table(SPEC0048_PREFIX.'product_variants')->insert([
        ['id' => 1, 'purchasable' => 'always', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'purchasable' => 'in_stock', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 3, 'purchasable' => 'in_stock_or_on_backorder', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 4, 'purchasable' => 'in_stock_or_backorder', 'created_at' => now(), 'updated_at' => now()], // typo
        ['id' => 5, 'purchasable' => 'something_rogue', 'created_at' => now(), 'updated_at' => now()],
    ]);
}

test('it renames the column and reconciles every stored value', function () {
    simulateV1VariantsWithPurchasable();

    sellingPolicyMigration()->up();

    expect(Schema::hasColumn(SPEC0048_PREFIX.'product_variants', 'purchasable'))->toBeFalse();
    expect(Schema::hasColumn(SPEC0048_PREFIX.'product_variants', 'selling_policy'))->toBeTrue();

    $values = DB::table(SPEC0048_PREFIX.'product_variants')->orderBy('id')->pluck('selling_policy', 'id');

    expect($values[1])->toBe('always');
    expect($values[2])->toBe('in_stock');
    expect($values[3])->toBe('in_stock_or_on_backorder');
    expect($values[4])->toBe('in_stock_or_on_backorder'); // typo corrected
    expect($values[5])->toBe('always');                   // rogue value falls back
});

test('it is idempotent across re-runs', function () {
    simulateV1VariantsWithPurchasable();

    $migration = sellingPolicyMigration();
    $migration->up();
    $migration->up();

    expect(Schema::hasColumn(SPEC0048_PREFIX.'product_variants', 'selling_policy'))->toBeTrue();
    expect(DB::table(SPEC0048_PREFIX.'product_variants')->where('selling_policy', 'in_stock_or_on_backorder')->count())->toBe(2);
});
