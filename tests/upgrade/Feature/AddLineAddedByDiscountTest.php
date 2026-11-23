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
const ADDED_BY_DISCOUNT_UPG_PREFIX = 'upgaddedby_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => ADDED_BY_DISCOUNT_UPG_PREFIX]);

    Schema::create(ADDED_BY_DISCOUNT_UPG_PREFIX.'discounts', function (Blueprint $table) {
        $table->id();
    });

    foreach (['cart_lines', 'order_lines'] as $table) {
        Schema::create(ADDED_BY_DISCOUNT_UPG_PREFIX.$table, function (Blueprint $table) {
            $table->id();
        });

        DB::table(ADDED_BY_DISCOUNT_UPG_PREFIX.$table)->insert(['id' => 1]);
    }

    Schema::create(ADDED_BY_DISCOUNT_UPG_PREFIX.'cart_line_discount', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('cart_line_id');
        $table->unsignedBigInteger('discount_id');
    });
});

afterEach(function () {
    foreach (['cart_line_discount', 'cart_lines', 'order_lines', 'discounts'] as $table) {
        Schema::dropIfExists(ADDED_BY_DISCOUNT_UPG_PREFIX.$table);
    }
});

function addedByDiscountUpgradeMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*add_line_added_by_discount.php');

    expect($path)->toHaveCount(1);

    return require $path[0];
}

test('it adds a nullable added_by_discount_id to cart and order lines and drops the empty pivot', function () {
    addedByDiscountUpgradeMigration()->up();
    addedByDiscountUpgradeMigration()->up();

    foreach (['cart_lines', 'order_lines'] as $table) {
        expect(DB::table(ADDED_BY_DISCOUNT_UPG_PREFIX.$table)->value('added_by_discount_id'))->toBeNull();
    }

    expect(Schema::hasTable(ADDED_BY_DISCOUNT_UPG_PREFIX.'cart_line_discount'))->toBeFalse();
});

test('it keeps the pivot when a store has written rows to it', function () {
    DB::table(ADDED_BY_DISCOUNT_UPG_PREFIX.'cart_line_discount')->insert(['cart_line_id' => 1, 'discount_id' => 1]);

    addedByDiscountUpgradeMigration()->up();

    expect(DB::table(ADDED_BY_DISCOUNT_UPG_PREFIX.'cart_line_discount')->count())->toBe(1)
        ->and(Schema::hasColumn(ADDED_BY_DISCOUNT_UPG_PREFIX.'cart_lines', 'added_by_discount_id'))->toBeTrue();
});
