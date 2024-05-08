<?php

namespace Lunar\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
use Lunar\Tests\Stubs\DataTypes\TestPurchasable;
use Lunar\Tests\TestCase;

/**
 * @group lunar.orderlines
 */
class OrderLineTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_an_order_line()
    {
        $order = Order::factory()->create();

        Currency::factory()->create([
            'default' => true,
        ]);

        $data = [
            'order_id' => $order->id,
            'quantity' => 1,
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => ProductVariant::factory()->create()->id,
        ];

        OrderLine::factory()->create($data);

        $this->assertDatabaseHas(
            (new OrderLine())->getTable(),
            $data
        );
    }

    /** @test */
    public function check_unit_price_casts_correctly()
    {
        $order = Order::factory()->create();

        Currency::factory()->create([
            'default' => true,
        ]);

        $data = [
            'order_id' => $order->id,
            'quantity' => 1,
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => ProductVariant::factory()->create()->id,
            'unit_price' => 507,
            'unit_quantity' => 100,
        ];

        $orderLine = OrderLine::factory()->create($data);

        $this->assertDatabaseHas(
            (new OrderLine())->getTable(),
            $data
        );

        $this->assertEquals(5.07, $orderLine->unit_price->decimal);
        $this->assertEquals(0.05, $orderLine->unit_price->unitDecimal);
        $this->assertEquals(0.0507, $orderLine->unit_price->unitDecimal(false));
    }

    /** @test */
    public function only_purchasables_can_be_added_to_an_order()
    {
        $order = Order::factory()->create();

        $this->expectException(NonPurchasableItemException::class);

        $data = [
            'order_id' => $order->id,
            'quantity' => 1,
            'purchasable_type' => Channel::class,
            'purchasable_id' => Channel::factory()->create()->id,
        ];

        OrderLine::factory()->create($data);

        $this->assertDatabaseMissing((new CartLine())->getTable(), $data);
    }

    /** @test */
    public function purchasable_non_eloquent_models_can_be_added_to_an_order()
    {
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

        $this->assertDatabaseHas(
            (new OrderLine())->getTable(),
            $data
        );

        $this->assertEquals(5.0, $orderLine->unit_price->decimal);
        $this->assertEquals(5.0, $orderLine->unit_price->unitDecimal);

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

        $this->assertDatabaseHas(
            (new OrderLine())->getTable(),
            $data
        );

        $this->assertEquals(6.5, $orderLine->unit_price->decimal);
        $this->assertEquals(6.5, $orderLine->unit_price->unitDecimal);
    }
}
