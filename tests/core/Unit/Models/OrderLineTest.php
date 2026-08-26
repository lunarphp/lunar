<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Exceptions\NonPurchasableItemException;
use Lunar\Models\CartLine;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Tests\Core\Stubs\TestPurchasable;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

test('can make an order line', function () {
    $order = Order::factory()->create();

    Currency::factory()->create([
        'default' => true,
    ]);

    $variant = ProductVariant::factory()->create();

    $data = [
        'order_id' => $order->id,
        'quantity' => 1,
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
    ];

    OrderLine::factory()->create($data);

    $this->assertDatabaseHas(
        (new OrderLine)->getTable(),
        $data
    );
});

test('check unit price casts correctly', function () {
    $order = Order::factory()->create();

    Currency::factory()->create([
        'default' => true,
    ]);

    $variant = ProductVariant::factory()->create();

    $data = [
        'order_id' => $order->id,
        'quantity' => 1,
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'unit_price' => 507,
        'unit_quantity' => 100,
    ];

    $orderLine = OrderLine::factory()->create($data);

    $this->assertDatabaseHas(
        (new OrderLine)->getTable(),
        $data
    );

    expect($orderLine->unit_price->decimal)->toEqual(5.07);
    expect($orderLine->unit_price->unitDecimal)->toEqual(0.05);
    expect($orderLine->unit_price->unitDecimal(false))->toEqual(0.0507);
});

test('only purchasables can be added to an order', function () {
    $order = Order::factory()->create();

    $this->expectException(NonPurchasableItemException::class);

    $channel = Channel::factory()->create();

    $data = [
        'order_id' => $order->id,
        'quantity' => 1,
        'purchasable_type' => $channel->getMorphClass(),
        'purchasable_id' => $channel->id,
    ];

    OrderLine::factory()->create($data);

    $this->assertDatabaseMissing((new CartLine)->getTable(), $data);
});

test('non eloquent models can be added to an order', function () {
    $order = Order::factory()->create();

    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    $taxClass = TaxClass::factory()->create();

    $shippingOption = new ShippingOption(
        name: 'Basic Delivery',
        description: 'Basic Delivery',
        identifier: 'BASDEL',
        price: new Price(500, $currency, 1),
        taxClass: $taxClass
    );

    $data = [
        'order_id' => $order->id,
        'quantity' => 1,
        'type' => $shippingOption->getType(),
        'purchasable_type' => ShippingOption::class,
        'purchasable_id' => $shippingOption->getIdentifier(),
        'unit_price' => $shippingOption->getPrice()->value,
        'unit_quantity' => $shippingOption->getUnitQuantity(),
    ];

    $orderLine = OrderLine::factory()->create($data);

    assertDatabaseHas(
        (new OrderLine)->getTable(),
        $data
    );

    expect($orderLine->unit_price->decimal)->toEqual(5.0)
        ->and($orderLine->unit_price->unitDecimal)->toEqual(5.0);

    $testPurchasable = new TestPurchasable(
        name: 'Test Purchasable',
        description: 'Test Purchasable',
        identifier: 'TESTPUR',
        price: new Price(650, $currency, 1),
        taxClass: $taxClass
    );

    $data = [
        'order_id' => $order->id,
        'quantity' => 1,
        'type' => $testPurchasable->getType(),
        'purchasable_type' => TestPurchasable::class,
        'purchasable_id' => $testPurchasable->getIdentifier(),
        'unit_price' => $testPurchasable->getPrice()->value,
        'unit_quantity' => $testPurchasable->getUnitQuantity(),
    ];

    $orderLine = OrderLine::factory()->create($data);

    assertDatabaseHas(
        (new OrderLine)->getTable(),
        $data
    );

    expect($orderLine->unit_price->decimal)->toEqual(6.5)
        ->and($orderLine->unit_price->unitDecimal)
        ->toEqual(6.5);
});

test('self-describing lines can be added to an order without a purchasable', function () {
    $order = Order::factory()->create();

    Currency::factory()->create([
        'default' => true,
    ]);

    $data = [
        'order_id' => $order->id,
        'quantity' => 1,
        'type' => 'shipping',
        'purchasable_type' => null,
        'purchasable_id' => null,
        'description' => 'Basic Delivery',
        'identifier' => 'BASDEL',
        'unit_price' => 500,
    ];

    $orderLine = OrderLine::factory()->create($data);

    assertDatabaseHas(
        (new OrderLine)->getTable(),
        $data
    );

    expect($orderLine->purchasable)->toBeNull();
});

test('eager loading purchasable resolves real lines and leaves null-morph lines null', function () {
    $order = Order::factory()->create();

    Currency::factory()->create([
        'default' => true,
    ]);

    $variant = ProductVariant::factory()->create();

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'type' => 'physical',
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'type' => 'shipping',
        'purchasable_type' => null,
        'purchasable_id' => null,
        'description' => 'Basic Delivery',
        'identifier' => 'BASDEL',
    ]);

    // The fake morph stored a non-Eloquent ShippingOption type, so this eager
    // load blew up trying to instantiate it. Both relations must resolve here.
    $order = Order::with('lines.purchasable')->findOrFail($order->id);

    $productLine = $order->lines->firstWhere('type', 'physical');
    $shippingLine = $order->lines->firstWhere('type', 'shipping');

    expect($productLine->relationLoaded('purchasable'))->toBeTrue()
        ->and($shippingLine->relationLoaded('purchasable'))->toBeTrue()
        ->and($productLine->purchasable)->toBeInstanceOf(ProductVariant::class)
        ->and($productLine->purchasable->id)->toEqual($variant->id)
        ->and($shippingLine->purchasable)->toBeNull();
});

test('the migration nulls legacy fake shipping morphs', function () {
    $order = Order::factory()->create();

    Currency::factory()->create([
        'default' => true,
    ]);

    $table = (new OrderLine)->getTable();

    DB::table($table)->insert([
        'order_id' => $order->id,
        'quantity' => 1,
        'type' => 'shipping',
        'purchasable_type' => ShippingOption::class,
        'purchasable_id' => 1,
        'description' => 'Basic Delivery',
        'identifier' => 'BASDEL',
        'unit_price' => 500,
        'unit_quantity' => 1,
        'sub_total' => 500,
        'discount_total' => 0,
        'tax_breakdown' => '[]',
        'tax_total' => 0,
        'total' => 500,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $migration = require dirname(__DIR__, 4).'/packages/core/database/migrations/2026_08_26_100000_make_order_lines_purchasable_morph_nullable.php';
    $migration->up();

    expect(DB::table($table)->where('type', 'shipping')->whereNotNull('purchasable_type')->count())->toBe(0);
});
