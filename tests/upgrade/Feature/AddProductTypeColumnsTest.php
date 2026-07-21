<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Upgrade\TestCase;

uses(TestCase::class);

/**
 * Isolated prefix so this stands up its own throwaway schema without touching
 * the shared lunar_* tables.
 */
const PRODUCT_TYPE_UPG_PREFIX = 'upgpt_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => PRODUCT_TYPE_UPG_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(PRODUCT_TYPE_UPG_PREFIX.'product_types');
    Schema::dropIfExists(PRODUCT_TYPE_UPG_PREFIX.'tax_classes');
});

function productTypeColumnsMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*add_product_type_columns.php');

    return require $path[0];
}

/**
 * Stand up the v1-shaped `product_types` table (name only) plus the
 * `tax_classes` table the new foreign key references.
 */
function simulateV1ProductTypeRows(): void
{
    Schema::create(PRODUCT_TYPE_UPG_PREFIX.'tax_classes', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    Schema::create(PRODUCT_TYPE_UPG_PREFIX.'product_types', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    DB::table(PRODUCT_TYPE_UPG_PREFIX.'product_types')->insert([
        ['id' => 1, 'name' => 'Stationery', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'name' => 'Stationery ', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 3, 'name' => 'Apparel', 'created_at' => now(), 'updated_at' => now()],
    ]);
}

test('it adds the columns and backfills unique handles and active status', function () {
    simulateV1ProductTypeRows();

    productTypeColumnsMigration()->up();

    $table = PRODUCT_TYPE_UPG_PREFIX.'product_types';

    expect(Schema::hasColumn($table, 'handle'))->toBeTrue()
        ->and(Schema::hasColumn($table, 'status'))->toBeTrue()
        ->and(Schema::hasColumn($table, 'description'))->toBeTrue()
        ->and(Schema::hasColumn($table, 'default_tax_class_id'))->toBeTrue()
        ->and(Schema::hasColumn($table, 'attribute_data'))->toBeTrue();

    $rows = DB::table($table)->orderBy('id')->get();

    // Both Stationery rows slug identically; the second gets a suffix.
    expect($rows[0]->handle)->toBe('stationery')
        ->and($rows[1]->handle)->toBe('stationery-2')
        ->and($rows[2]->handle)->toBe('apparel')
        ->and($rows->pluck('status')->unique()->all())->toBe(['active']);
});

test('it is a no-op when the handle column already exists', function () {
    simulateV1ProductTypeRows();

    productTypeColumnsMigration()->up();

    $before = DB::table(PRODUCT_TYPE_UPG_PREFIX.'product_types')->orderBy('id')->get();

    productTypeColumnsMigration()->up();

    expect(DB::table(PRODUCT_TYPE_UPG_PREFIX.'product_types')->orderBy('id')->get())->toEqual($before);
});
