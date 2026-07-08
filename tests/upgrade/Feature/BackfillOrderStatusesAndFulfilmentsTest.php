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
const ORDERS_UPG_PREFIX = 'upgorders_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => ORDERS_UPG_PREFIX]);
});

afterEach(function () {
    foreach ([
        'fulfilment_trackings', 'fulfilment_lines', 'fulfilments',
        'transactions', 'order_lines', 'orders', 'locations',
    ] as $table) {
        Schema::dropIfExists(ORDERS_UPG_PREFIX.$table);
    }
});

function orderStatusMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*backfill_order_statuses_and_fulfilments.php');

    return require $path[0];
}

/**
 * Stand up the v1-shaped tables as they exist by this point in the upgrade:
 * orders still carry the v1 headline `status`, order_lines already have
 * `requires_fulfilment` (backfilled by the earlier data step), and the
 * transaction ledger is present. No fulfilment tables, no locations.
 */
function simulateV1Orders(): void
{
    Schema::create(ORDERS_UPG_PREFIX.'orders', function (Blueprint $table) {
        $table->id();
        $table->string('status');
        $table->unsignedBigInteger('sub_total')->default(0);
        $table->unsignedBigInteger('total')->default(0);
        $table->dateTime('placed_at')->nullable();
        $table->timestamps();
    });

    Schema::create(ORDERS_UPG_PREFIX.'order_lines', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id');
        $table->string('type');
        $table->boolean('requires_fulfilment')->default(false);
        $table->unsignedInteger('quantity')->default(1);
        $table->timestamps();
    });

    Schema::create(ORDERS_UPG_PREFIX.'transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id');
        $table->string('type');
        $table->boolean('success')->default(true);
        $table->unsignedBigInteger('amount')->default(0);
        $table->timestamps();
    });
}

function makeOrder(int $id, string $status, int $total = 1000, ?string $placedAt = '2024-03-01 10:00:00'): void
{
    DB::table(ORDERS_UPG_PREFIX.'orders')->insert([
        'id' => $id,
        'status' => $status,
        'sub_total' => $total,
        'total' => $total,
        'placed_at' => $placedAt,
        'created_at' => '2024-03-01 09:00:00',
        'updated_at' => '2024-03-02 12:00:00',
    ]);
}

function makeLine(int $id, int $orderId, bool $fulfillable = true, int $quantity = 1, string $type = 'physical'): void
{
    DB::table(ORDERS_UPG_PREFIX.'order_lines')->insert([
        'id' => $id,
        'order_id' => $orderId,
        'type' => $type,
        'requires_fulfilment' => $fulfillable,
        'quantity' => $quantity,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function makeTransaction(int $orderId, string $type, int $amount, bool $success = true): void
{
    DB::table(ORDERS_UPG_PREFIX.'transactions')->insert([
        'order_id' => $orderId,
        'type' => $type,
        'success' => $success,
        'amount' => $amount,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('it adds the v2 status columns and drops the v1 headline', function () {
    simulateV1Orders();
    makeOrder(1, 'awaiting-payment');
    makeLine(1, 1, fulfillable: true);

    orderStatusMigration()->up();

    $orders = ORDERS_UPG_PREFIX.'orders';

    foreach (['payment_status', 'fulfilment_status', 'closed_at', 'cancelled_at', 'cancel_reason', 'cancel_note'] as $column) {
        expect(Schema::hasColumn($orders, $column))->toBeTrue();
    }

    expect(Schema::hasColumn($orders, 'status'))->toBeFalse();

    $order = DB::table($orders)->find(1);
    expect($order->payment_status)->toBe('pending')
        ->and($order->fulfilment_status)->toBe('unfulfilled')
        ->and($order->closed_at)->toBeNull();
});

test('it creates a whole-order shipped fulfilment for dispatched orders', function () {
    simulateV1Orders();
    makeOrder(1, 'dispatched');
    makeLine(1, 1, fulfillable: true, quantity: 2);
    makeLine(2, 1, fulfillable: true, quantity: 3);
    makeLine(3, 1, fulfillable: false, type: 'shipping');
    makeOrder(2, 'awaiting-payment');
    makeLine(4, 2, fulfillable: true);

    orderStatusMigration()->up();

    // The dispatched order: fulfilled, one shipped fulfilment at the default
    // location covering both fulfillable lines at full quantity.
    expect(DB::table(ORDERS_UPG_PREFIX.'orders')->find(1)->fulfilment_status)->toBe('fulfilled');

    $fulfilment = DB::table(ORDERS_UPG_PREFIX.'fulfilments')->where('order_id', 1)->first();
    expect($fulfilment->state)->toBe('shipped')
        ->and($fulfilment->method)->toBe('shipping')
        ->and($fulfilment->shipped_at)->toBe('2024-03-01 10:00:00')
        ->and(strlen($fulfilment->public_id))->toBe(26)
        ->and((int) $fulfilment->location_id)->toBe((int) DB::table(ORDERS_UPG_PREFIX.'locations')->value('id'));

    $lines = DB::table(ORDERS_UPG_PREFIX.'fulfilment_lines')->where('fulfilment_id', $fulfilment->id)->orderBy('order_line_id')->get();
    expect($lines)->toHaveCount(2)
        ->and((int) $lines[0]->quantity)->toBe(2)
        ->and((int) $lines[1]->quantity)->toBe(3)
        ->and(strlen($lines[0]->public_id))->toBe(26);

    // The unshipped order: untouched.
    expect(DB::table(ORDERS_UPG_PREFIX.'orders')->find(2)->fulfilment_status)->toBe('unfulfilled')
        ->and(DB::table(ORDERS_UPG_PREFIX.'fulfilments')->where('order_id', 2)->count())->toBe(0);
});

test('it marks orders with nothing to fulfil as fulfilled without a fulfilment', function () {
    simulateV1Orders();
    makeOrder(1, 'awaiting-payment');
    makeLine(1, 1, fulfillable: false, type: 'digital');

    orderStatusMigration()->up();

    expect(DB::table(ORDERS_UPG_PREFIX.'orders')->find(1)->fulfilment_status)->toBe('fulfilled')
        ->and(DB::table(ORDERS_UPG_PREFIX.'fulfilments')->count())->toBe(0);
});

test('it derives payment status from the transaction ledger', function () {
    simulateV1Orders();

    makeOrder(1, 'payment-received');                       // paid: captured == total
    makeTransaction(1, 'capture', 1000);

    makeOrder(2, 'payment-received');                       // partially-paid
    makeTransaction(2, 'capture', 400);

    makeOrder(3, 'refunded');                               // refunded: refunds >= captures
    makeTransaction(3, 'capture', 1000);
    makeTransaction(3, 'refund', 1000);

    makeOrder(4, 'payment-received');                       // partially-refunded
    makeTransaction(4, 'capture', 1000);
    makeTransaction(4, 'refund', 300);

    makeOrder(5, 'awaiting-payment');                       // authorized: successful intent only
    makeTransaction(5, 'intent', 1000);

    makeOrder(6, 'failed');                                 // voided: only failed transactions
    makeTransaction(6, 'capture', 1000, success: false);

    makeOrder(7, 'awaiting-payment');                       // pending: empty ledger
    makeOrder(8, 'dispatched', total: 0);                   // zero-total settles as paid

    orderStatusMigration()->up();

    $status = fn (int $id): string => DB::table(ORDERS_UPG_PREFIX.'orders')->find($id)->payment_status;

    expect($status(1))->toBe('paid')
        ->and($status(2))->toBe('partially-paid')
        ->and($status(3))->toBe('refunded')
        ->and($status(4))->toBe('partially-refunded')
        ->and($status(5))->toBe('authorized')
        ->and($status(6))->toBe('voided')
        ->and($status(7))->toBe('pending')
        ->and($status(8))->toBe('paid');
});

test('it stamps closed_at and cancelled_at from the v1 headline', function () {
    simulateV1Orders();
    makeOrder(1, 'complete');
    makeOrder(2, 'cancelled');
    makeOrder(3, 'refunded');
    makeOrder(4, 'dispatched');

    orderStatusMigration()->up();

    $order = fn (int $id) => DB::table(ORDERS_UPG_PREFIX.'orders')->find($id);

    expect($order(1)->closed_at)->toBe('2024-03-02 12:00:00')
        ->and($order(2)->closed_at)->not->toBeNull()
        ->and($order(2)->cancelled_at)->toBe('2024-03-02 12:00:00')
        ->and($order(3)->closed_at)->not->toBeNull()
        ->and($order(4)->closed_at)->toBeNull()
        ->and($order(4)->cancelled_at)->toBeNull();
});

test('it honours a customised v1 status map', function () {
    simulateV1Orders();
    config(['lunar.upgrade.orders.fulfilled_statuses' => ['sent']]);
    config(['lunar.upgrade.orders.closed_statuses' => ['archived']]);

    makeOrder(1, 'sent');
    makeLine(1, 1, fulfillable: true);
    makeOrder(2, 'archived');
    makeOrder(3, 'dispatched');
    makeLine(2, 3, fulfillable: true);

    orderStatusMigration()->up();

    $order = fn (int $id) => DB::table(ORDERS_UPG_PREFIX.'orders')->find($id);

    expect($order(1)->fulfilment_status)->toBe('fulfilled')
        ->and(DB::table(ORDERS_UPG_PREFIX.'fulfilments')->where('order_id', 1)->count())->toBe(1)
        ->and($order(2)->closed_at)->not->toBeNull()
        ->and($order(3)->fulfilment_status)->toBe('unfulfilled');
});

test('it does not duplicate fulfilments for orders that already have one', function () {
    simulateV1Orders();
    makeOrder(1, 'dispatched');
    makeLine(1, 1, fulfillable: true);

    // First run creates the fulfilment tables and the row; simulate a re-run
    // (e.g. after a mid-migration failure) by restoring the status column.
    orderStatusMigration()->up();

    Schema::table(ORDERS_UPG_PREFIX.'orders', function (Blueprint $table) {
        $table->string('status')->default('dispatched');
    });

    orderStatusMigration()->up();

    expect(DB::table(ORDERS_UPG_PREFIX.'fulfilments')->where('order_id', 1)->count())->toBe(1);
});

test('it is a no-op on an already-migrated database', function () {
    Schema::create(ORDERS_UPG_PREFIX.'orders', function (Blueprint $table) {
        $table->id();
        $table->string('payment_status')->default('pending');
        $table->string('fulfilment_status')->default('unfulfilled');
        $table->timestamps();
    });

    orderStatusMigration()->up();

    expect(Schema::hasTable(ORDERS_UPG_PREFIX.'fulfilments'))->toBeFalse();
});
