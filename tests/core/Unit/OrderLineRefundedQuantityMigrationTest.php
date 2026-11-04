<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('migrations', 'cross-db');

const REFUNDED_QUANTITY_PREFIX = 'refundqty_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => REFUNDED_QUANTITY_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(REFUNDED_QUANTITY_PREFIX.'order_lines');
});

function refundedQuantityMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/core/database/migrations/*add_refunded_quantity_to_order_lines_table.php');

    return require $path[0];
}

function createExistingOrderLinesTable(bool $withRefundedQuantity = false): void
{
    Schema::create(REFUNDED_QUANTITY_PREFIX.'order_lines', function (Blueprint $table) use ($withRefundedQuantity) {
        $table->id();
        $table->unsignedInteger('quantity');

        if ($withRefundedQuantity) {
            $table->unsignedInteger('refunded_quantity')->default(0);
        }
    });
}

test('it adds refunded quantity and preserves existing order lines', function () {
    createExistingOrderLinesTable();

    DB::table(REFUNDED_QUANTITY_PREFIX.'order_lines')->insert([
        'id' => 1,
        'quantity' => 3,
    ]);

    $migration = refundedQuantityMigration();
    $migration->up();
    $migration->up();

    $line = DB::table(REFUNDED_QUANTITY_PREFIX.'order_lines')->find(1);

    expect(Schema::hasColumn(REFUNDED_QUANTITY_PREFIX.'order_lines', 'refunded_quantity'))->toBeTrue()
        ->and($line->quantity)->toBe(3)
        ->and($line->refunded_quantity)->toBe(0);

    $migration->down();

    expect(Schema::hasColumn(REFUNDED_QUANTITY_PREFIX.'order_lines', 'refunded_quantity'))->toBeTrue();
});

test('it is a no-op when refunded quantity already exists', function () {
    createExistingOrderLinesTable(withRefundedQuantity: true);

    DB::table(REFUNDED_QUANTITY_PREFIX.'order_lines')->insert([
        'id' => 1,
        'quantity' => 3,
        'refunded_quantity' => 2,
    ]);

    refundedQuantityMigration()->up();

    expect(DB::table(REFUNDED_QUANTITY_PREFIX.'order_lines')->value('refunded_quantity'))->toBe(2);
});

test('it is a no-op when the order lines table does not exist', function () {
    $migration = refundedQuantityMigration();

    $migration->up();
    $migration->down();

    expect(Schema::hasTable(REFUNDED_QUANTITY_PREFIX.'order_lines'))->toBeFalse();
});
