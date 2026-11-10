<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Upgrade\TestCase;

uses(TestCase::class)->group('cross-db');

/**
 * Isolated prefix so this stands up its own throwaway schema without touching
 * the shared lunar_* tables.
 */
const REFUNDED_QUANTITY_UPG_PREFIX = 'upgrefundqty_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => REFUNDED_QUANTITY_UPG_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(REFUNDED_QUANTITY_UPG_PREFIX.'order_lines');
});

function orderLineRefundedQuantityMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*add_order_line_refunded_quantity.php');

    expect($path)->toHaveCount(1);

    return require $path[0];
}

/**
 * Stand up the v1-shaped `order_lines` table: no refunded quantity rollup.
 */
function simulateV1RefundedQuantityOrderLines(bool $withRefundedQuantity = false): void
{
    Schema::create(REFUNDED_QUANTITY_UPG_PREFIX.'order_lines', function (Blueprint $table) use ($withRefundedQuantity) {
        $table->id();
        $table->unsignedInteger('quantity');

        if ($withRefundedQuantity) {
            $table->unsignedInteger('refunded_quantity')->default(0);
        }
    });
}

test('it adds refunded quantity and preserves existing order lines', function () {
    simulateV1RefundedQuantityOrderLines();

    DB::table(REFUNDED_QUANTITY_UPG_PREFIX.'order_lines')->insert(['id' => 1, 'quantity' => 3]);

    $migration = orderLineRefundedQuantityMigration();
    $migration->up();
    $migration->up();

    $line = DB::table(REFUNDED_QUANTITY_UPG_PREFIX.'order_lines')->find(1);

    expect(Schema::hasColumn(REFUNDED_QUANTITY_UPG_PREFIX.'order_lines', 'refunded_quantity'))->toBeTrue()
        ->and((int) $line->quantity)->toBe(3)
        ->and((int) $line->refunded_quantity)->toBe(0);
});

test('it is a no-op when refunded quantity already exists', function () {
    simulateV1RefundedQuantityOrderLines(withRefundedQuantity: true);

    DB::table(REFUNDED_QUANTITY_UPG_PREFIX.'order_lines')->insert(['id' => 1, 'quantity' => 3, 'refunded_quantity' => 2]);

    orderLineRefundedQuantityMigration()->up();

    expect((int) DB::table(REFUNDED_QUANTITY_UPG_PREFIX.'order_lines')->value('refunded_quantity'))->toBe(2);
});

test('it is a no-op when the order lines table does not exist', function () {
    orderLineRefundedQuantityMigration()->up();

    expect(Schema::hasTable(REFUNDED_QUANTITY_UPG_PREFIX.'order_lines'))->toBeFalse();
});
