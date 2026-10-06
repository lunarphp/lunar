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
const CART_TOTALS_UPG_PREFIX = 'upgcarttotals_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => CART_TOTALS_UPG_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(CART_TOTALS_UPG_PREFIX.'cart_lines');
    Schema::dropIfExists(CART_TOTALS_UPG_PREFIX.'carts');
});

function cartTotalsColumnsMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*add_cart_totals_columns.php');

    return require $path[0];
}

/**
 * Stand up the v1-shaped `carts` / `cart_lines` tables: no totals columns.
 */
function simulateV1CartRows(): void
{
    Schema::create(CART_TOTALS_UPG_PREFIX.'carts', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('currency_id');
        $table->unsignedBigInteger('channel_id');
        $table->timestamps();
    });

    Schema::create(CART_TOTALS_UPG_PREFIX.'cart_lines', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('cart_id');
        $table->string('purchasable_type');
        $table->unsignedBigInteger('purchasable_id');
        $table->unsignedInteger('quantity');
        $table->timestamps();
    });

    DB::table(CART_TOTALS_UPG_PREFIX.'carts')->insert([
        ['id' => 1, 'currency_id' => 1, 'channel_id' => 1, 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::table(CART_TOTALS_UPG_PREFIX.'cart_lines')->insert([
        ['id' => 1, 'cart_id' => 1, 'purchasable_type' => 'product_variant', 'purchasable_id' => 1, 'quantity' => 2, 'created_at' => now(), 'updated_at' => now()],
    ]);
}

test('it adds the totals columns and leaves existing carts uncalculated', function () {
    simulateV1CartRows();

    cartTotalsColumnsMigration()->up();

    $carts = CART_TOTALS_UPG_PREFIX.'carts';
    $lines = CART_TOTALS_UPG_PREFIX.'cart_lines';

    foreach (['sub_total', 'total', 'tax_breakdown', 'free_items', 'revision', 'calculated_revision', 'calculated_at'] as $column) {
        expect(Schema::hasColumn($carts, $column))->toBeTrue("carts.{$column} missing");
    }

    foreach (['unit_price', 'total', 'tax_breakdown', 'promotion_description'] as $column) {
        expect(Schema::hasColumn($lines, $column))->toBeTrue("cart_lines.{$column} missing");
    }

    $cart = DB::table($carts)->find(1);

    expect((int) $cart->revision)->toBe(0)
        ->and($cart->calculated_revision)->toBeNull()
        ->and($cart->calculated_at)->toBeNull()
        ->and($cart->total)->toBeNull();
});

test('it is a no-op when the columns already exist', function () {
    simulateV1CartRows();

    cartTotalsColumnsMigration()->up();

    DB::table(CART_TOTALS_UPG_PREFIX.'carts')->where('id', 1)->update(['revision' => 3, 'total' => 1200]);

    $before = DB::table(CART_TOTALS_UPG_PREFIX.'carts')->orderBy('id')->get();

    cartTotalsColumnsMigration()->up();

    expect(DB::table(CART_TOTALS_UPG_PREFIX.'carts')->orderBy('id')->get())->toEqual($before);
});
