<?php

namespace Lunar\Tests\Unit\Managers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Base\CartLineModifiers;
use Lunar\Base\CartModifiers;
use Lunar\Base\Purchasable;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\DataTypes\Price;
use Lunar\Exceptions\CartLineIdMismatchException;
use Lunar\Exceptions\Carts\BillingAddressIncompleteException;
use Lunar\Exceptions\Carts\BillingAddressMissingException;
use Lunar\Exceptions\Carts\OrderExistsException;
use Lunar\Exceptions\InvalidCartLineQuantityException;
use Lunar\Exceptions\MaximumCartLineQuantityException;
use Lunar\Managers\CartManager;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\CartLine;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Order;
use Lunar\Models\Price as PriceModel;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRateAmount;
use Lunar\Tests\Stubs\TestCartLineModifier;
use Lunar\Tests\Stubs\TestCartModifier;
use Lunar\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

/**
 * @group lunar.cart-manager
 */
class CartManagerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_initialise_the_manager()
    {
        $this->assertInstanceOf(
            CartManager::class,
            new CartManager(
                Cart::factory()->create()
            )
        );
    }

    /** @test */
    public function can_return_cart()
    {
        Mockery::mock(CartManager::class, function (MockInterface $mock) {
            $mock->shouldReceive('calculate')->andReturn(null);
        });

        $manager = new CartManager(
            Cart::factory()->create()
        );

        $this->assertInstanceOf(Cart::class, $manager->getCart());
    }

    /** @test */
    public function cart_calculate_is_called_when_no_total_present()
    {
        $mock = Mockery::mock(
            new CartManager(
                Cart::factory()->create()
            ),
            function (MockInterface $mock) {
                $mock->shouldReceive('calculate')->andReturn(null);
            }
        );
        $this->assertInstanceOf(Cart::class, $mock->getCart());
    }

    /** @test */
    public function can_calculate_empty_totals()
    {
        $cart = Cart::factory()->create();

        $manager = new CartManager($cart);

        $this->assertNull($cart->total);
        $this->assertNull($cart->taxTotal);
        $this->assertNull($cart->subTotal);

        $cart = $manager->getCart();

        $this->assertInstanceOf(Price::class, $cart->total);
        $this->assertInstanceOf(Price::class, $cart->taxTotal);
        $this->assertInstanceOf(Price::class, $cart->subTotal);

        $this->assertEquals(0, $cart->subTotal->value);
        $this->assertEquals(0, $cart->subTotal->value);
        $this->assertEquals(0, $cart->taxTotal->value);
    }

    /** @test */
    public function can_calculate_single_quantity_total()
    {
        $currency = Currency::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $purchasable = ProductVariant::factory()->create();

        PriceModel::factory()->create([
            'price' => 100,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id' => $purchasable->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id' => $purchasable->id,
            'quantity' => 1,
        ]);

        $manager = new CartManager($cart);

        $this->assertNull($cart->total);
        $this->assertNull($cart->taxTotal);
        $this->assertNull($cart->subTotal);

        $cart = $manager->getCart();

        $this->assertEquals(100, $cart->subTotal->value);
        $this->assertEquals(120, $cart->total->value);
        $this->assertEquals(20, $cart->taxTotal->value);
    }

    /** @test */
    public function can_calculate_multiple_quantity_total()
    {
        $currency = Currency::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $purchasable = ProductVariant::factory()->create();

        PriceModel::factory()->create([
            'price' => 100,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id' => $purchasable->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id' => $purchasable->id,
            'quantity' => 2,
        ]);

        $manager = new CartManager($cart);

        $this->assertNull($cart->total);
        $this->assertNull($cart->taxTotal);
        $this->assertNull($cart->subTotal);

        $cart = $manager->getCart();

        $this->assertEquals(200, $cart->subTotal->value);
        $this->assertEquals(240, $cart->total->value);
        $this->assertEquals(40, $cart->taxTotal->value);
    }

    /** @test */
    public function calculating_pipeline_has_effect()
    {
        $currency = Currency::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $purchasable = ProductVariant::factory()->create();

        PriceModel::factory()->create([
            'price' => 100,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id' => $purchasable->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id' => $purchasable->id,
            'quantity' => 1,
        ]);

        // Modifier will set total to 5000
        app(CartModifiers::class)->add(TestCartModifier::class);

        (new CartManager($cart))->getCart();

        $this->assertEquals(5000, $cart->total->value);
    }

    /** @test */
    public function can_handle_non_numeric_quantity()
    {
        $cart = Cart::factory()->create();

        $purchasable = ProductVariant::factory()->create();

        $this->assertCount(0, $cart->lines);

        $this->expectError(\TypeError::class);

        $cart->getManager()->add($purchasable, 'aweadawd');

        $this->assertCount(0, $cart->refresh()->lines);
    }

    /** @test  */
    public function cannot_add_non_purchasable_model_to_cart()
    {
        $cart = Cart::factory()->create();

        $purchasable = Channel::factory()->create();

        $this->assertCount(0, $cart->lines);

        $this->assertFalse($purchasable instanceof Purchasable);

        $this->expectError(\TypeError::class);

        $cart->getManager()->add($purchasable, 1);

        $this->assertCount(0, $cart->refresh()->lines);
    }

    /** @test */
    public function cannot_add_obscene_quantities()
    {
        $cart = Cart::factory()->create();

        $purchasable = ProductVariant::factory()->create();

        $this->assertCount(0, $cart->lines);

        $cart->getManager()->add($purchasable, 1000000);

        $this->expectException(MaximumCartLineQuantityException::class);

        $cart->getManager()->add($purchasable, 100000000000000);

        $this->assertCount(1, $cart->refresh()->lines);
    }

    /** @test */
    public function can_handle_negative_quantities()
    {
        $cart = Cart::factory()->create();

        $purchasable = ProductVariant::factory()->create();

        $this->assertCount(0, $cart->lines);

        $this->expectException(InvalidCartLineQuantityException::class);

        $cart->getManager()->add($purchasable, -1);

        $this->assertCount(0, $cart->refresh()->lines);
    }

    /** @test */
    public function can_add_cart_line()
    {
        $cart = Cart::factory()->create();

        $purchasable = ProductVariant::factory()->create();

        $this->assertCount(0, $cart->lines);

        $cart->getManager()->add($purchasable, 1);

        $this->assertCount(1, $cart->refresh()->lines);
    }

    /** @test */
    public function can_add_multiple_cart_lines_as_collection()
    {
        $cart = Cart::factory()->create();

        $purchasableA = ProductVariant::factory()->create();
        $purchasableB = ProductVariant::factory()->create();

        $lines = collect([
            ['purchasable' => $purchasableA, 'quantity' => 1],
            ['purchasable' => $purchasableB, 'quantity' => 2],
        ]);

        $this->assertCount(0, $cart->lines);

        $cart->getManager()->addLines($lines);

        $this->assertCount(2, $cart->refresh()->lines);

        $this->assertDatabaseHas((new CartLine)->getTable(), [
            'purchasable_id' => $purchasableA->id,
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas((new CartLine)->getTable(), [
            'purchasable_id' => $purchasableB->id,
            'quantity' => 2,
        ]);
    }

    /** @test */
    public function can_add_multiple_cart_lines_as_array()
    {
        $cart = Cart::factory()->create();

        $purchasableA = ProductVariant::factory()->create();
        $purchasableB = ProductVariant::factory()->create();

        $lines = [
            ['purchasable' => $purchasableA, 'quantity' => 1],
            ['purchasable' => $purchasableB, 'quantity' => 2],
        ];

        $this->assertCount(0, $cart->lines);

        $cart->getManager()->addLines($lines);

        $this->assertCount(2, $cart->refresh()->lines);

        $this->assertDatabaseHas((new CartLine)->getTable(), [
            'purchasable_id' => $purchasableA->id,
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas((new CartLine)->getTable(), [
            'purchasable_id' => $purchasableB->id,
            'quantity' => 2,
        ]);
    }

    /** @test */
    public function can_update_cart_line_when_purchasable_exists()
    {
        $cart = Cart::factory()->create();

        $purchasable = ProductVariant::factory()->create();

        $this->assertCount(0, $cart->lines);

        $cart->getManager()->add($purchasable, 1, null);

        $this->assertDatabaseHas((new CartLine)->getTable(), [
            'purchasable_id' => $purchasable->id,
            'quantity' => 1,
            'meta' => null,
        ]);

        $this->assertCount(1, $cart->refresh()->lines);

        $cart->getManager()->add($purchasable, 1, null);

        $this->assertDatabaseHas((new CartLine)->getTable(), [
            'purchasable_id' => $purchasable->id,
            'quantity' => 2,
            'meta' => null,
        ]);
    }

    /** @test */
    public function can_update_cart_line_when_purchasable_exists_with_meta()
    {
        $cart = Cart::factory()->create();

        $purchasable = ProductVariant::factory()->create();

        $this->assertCount(0, $cart->lines);

        $cart->getManager()->add($purchasable, 1, []);

        $this->assertDatabaseHas((new CartLine)->getTable(), [
            'purchasable_id' => $purchasable->id,
            'quantity' => 1,
            'meta' => '[]',
        ]);

        $this->assertDatabaseCount((new CartLine)->getTable(), 1);

        $cart->getManager()->add($purchasable, 1);

        $this->assertDatabaseHas((new CartLine)->getTable(), [
            'purchasable_id' => $purchasable->id,
            'quantity' => 2,
            'meta' => '[]',
        ]);

        $cart->getManager()->add($purchasable, 1, []);

        $this->assertDatabaseHas((new CartLine)->getTable(), [
            'purchasable_id' => $purchasable->id,
            'quantity' => 3,
            'meta' => '[]',
        ]);

        $this->assertDatabaseCount((new CartLine)->getTable(), 1);

        $cart->getManager()->add($purchasable, 1, null);

        $this->assertDatabaseHas((new CartLine)->getTable(), [
            'purchasable_id' => $purchasable->id,
            'quantity' => 4,
            'meta' => '[]',
        ]);

        $this->assertDatabaseCount((new CartLine)->getTable(), 1);

        $cart->getManager()->add($purchasable, 1, ['foo' => 'bar']);

        $this->assertDatabaseHas((new CartLine)->getTable(), [
            'purchasable_id' => $purchasable->id,
            'quantity' => 4,
            'meta' => '[]',
        ]);

        $this->assertDatabaseHas((new CartLine)->getTable(), [
            'purchasable_id' => $purchasable->id,
            'quantity' => 1,
            'meta' => '{"foo":"bar"}',
        ]);

        $this->assertDatabaseCount((new CartLine)->getTable(), 2);
    }

    /** @test */
    public function can_add_same_purchasable_with_different_meta()
    {
        $cart = Cart::factory()->create();

        $purchasable = ProductVariant::factory()->create();

        $this->assertCount(0, $cart->lines);

        $cart->getManager()->add($purchasable, 1);
        $cart->getManager()->add($purchasable, 1, ['foo' => 'bar']);

        $this->assertCount(2, $cart->refresh()->lines);

        $cart->getManager()->add($purchasable, 1);

        $this->assertCount(2, $cart->refresh()->lines);
    }

    /** @test */
    public function can_update_existing_purchasable_when_meta_key_order_differs()
    {
        $cart = Cart::factory()->create();

        $purchasable = ProductVariant::factory()->create();

        $this->assertCount(0, $cart->lines);

        $cart->getManager()->add($purchasable, 1, ['alpha' => 'alpha', 'beta' => 'beta']);

        $cart = $cart->refresh();

        $this->assertEquals($cart->lines->first()->meta, (object) [
            'alpha' => 'alpha',
            'beta' => 'beta',
        ]);

        $this->assertCount(1, $cart->lines);

        $cart->getManager()->add($purchasable, 1, ['beta' => 'beta', 'alpha' => 'alpha']);

        $this->assertCount(1, $cart->refresh()->lines);
    }

    /** @test */
    public function quantity_will_update_when_existing_purchasable_added()
    {
        $cart = Cart::factory()->create();

        $purchasable = ProductVariant::factory()->create();

        $this->assertCount(0, $cart->lines);

        $cart->getManager()->add($purchasable, 1);

        $this->assertCount(1, $cart->refresh()->lines);
        $this->assertEquals(1, $cart->lines()->first()->quantity);

        $cart->getManager()->add($purchasable, 3);

        $this->assertCount(1, $cart->refresh()->lines);
        $this->assertEquals(4, $cart->lines()->first()->quantity);
    }

    /** @test */
    public function can_update_single_cart_line()
    {
        $cart = Cart::factory()->create();

        $purchasable = ProductVariant::factory()->create();

        $this->assertCount(0, $cart->lines);

        $cart->getManager()->add($purchasable, 1);

        $line = $cart->lines()->first();

        $this->assertEquals(1, $line->quantity);

        $cart->getManager()->updateLine($line->id, 4);

        $this->assertEquals(4, $line->refresh()->quantity);
    }

    /** @test */
    public function can_update_multiple_cart_lines()
    {
        $currency = Currency::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $purchasableA = ProductVariant::factory()->create();
        $purchasableB = ProductVariant::factory()->create();

        PriceModel::factory()->create([
            'price' => 100,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        PriceModel::factory()->create([
            'price' => 100,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableB),
            'priceable_id' => $purchasableB->id,
        ]);

        $cart->lines()->createMany([
            ['quantity' => 1, 'purchasable_type' => ProductVariant::class, 'purchasable_id' => $purchasableA->id],
            ['quantity' => 2, 'purchasable_type' => ProductVariant::class, 'purchasable_id' => $purchasableB->id],
        ]);

        $newLines = $cart->lines()->get()->map(function ($line) {
            return [
                'id' => $line->id,
                'quantity' => $line->quantity + 1,
            ];
        });

        $cart->getManager()->updateLines($newLines);

        $dataCheck = $newLines->map(function ($line) {
            return [
                'purchasable_type' => ProductVariant::class,
                'quantity' => (string) $line['quantity'],
            ];
        });

        foreach ($dataCheck as $check) {
            $this->assertDatabaseHas((new CartLine())->getTable(), $check);
        }
    }

    /** @test */
    public function can_remove_a_cart_line()
    {
        $currency = Currency::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $purchasableA = ProductVariant::factory()->create();
        $purchasableB = ProductVariant::factory()->create();

        PriceModel::factory()->create([
            'price' => 100,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        PriceModel::factory()->create([
            'price' => 100,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableB),
            'priceable_id' => $purchasableB->id,
        ]);

        $cart->lines()->createMany([
            ['quantity' => 1, 'purchasable_type' => ProductVariant::class, 'purchasable_id' => $purchasableA->id],
            ['quantity' => 2, 'purchasable_type' => ProductVariant::class, 'purchasable_id' => $purchasableB->id],
        ]);

        $this->assertCount(2, $cart->refresh()->lines);

        $cart->getManager()->removeLine($cart->refresh()->lines->first()->id);

        $this->assertCount(1, $cart->refresh()->lines);
    }

    /** @test */
    public function can_clear_a_cart()
    {
        $currency = Currency::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $purchasableA = ProductVariant::factory()->create();
        $purchasableB = ProductVariant::factory()->create();

        PriceModel::factory()->create([
            'price' => 100,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        PriceModel::factory()->create([
            'price' => 100,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableB),
            'priceable_id' => $purchasableB->id,
        ]);

        $cart->lines()->createMany([
            ['quantity' => 1, 'purchasable_type' => ProductVariant::class, 'purchasable_id' => $purchasableA->id],
            ['quantity' => 2, 'purchasable_type' => ProductVariant::class, 'purchasable_id' => $purchasableB->id],
        ]);

        $this->assertCount(2, $cart->refresh()->lines);

        $cart->getManager()->clear();

        $this->assertCount(0, $cart->refresh()->lines);
    }

    /** @test */
    public function cannot_remove_cart_line_from_another_cart()
    {
        $currency = Currency::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $purchasableA = ProductVariant::factory()->create();
        $purchasableB = ProductVariant::factory()->create();

        $anotherLine = CartLine::factory()->create();

        PriceModel::factory()->create([
            'price' => 100,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        PriceModel::factory()->create([
            'price' => 100,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableB),
            'priceable_id' => $purchasableB->id,
        ]);

        $cart->lines()->createMany([
            ['quantity' => 1, 'purchasable_type' => ProductVariant::class, 'purchasable_id' => $purchasableA->id],
            ['quantity' => 2, 'purchasable_type' => ProductVariant::class, 'purchasable_id' => $purchasableB->id],
        ]);

        $this->assertCount(2, $cart->refresh()->lines);

        $this->expectException(CartLineIdMismatchException::class);

        $cart->getManager()->removeLine($anotherLine->id);

        $this->assertCount(2, $cart->refresh()->lines);
    }

    /** @test */
    public function can_add_addresses_from_addressable_objects()
    {
        $currency = Currency::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $shipping = new CartAddress();
        $shipping->postcode = 'SHI P12';

        $billing = new CartAddress();
        $billing->postcode = 'BIL L12';

        $cart->getManager()->setShippingAddress($shipping);
        $cart->getManager()->setBillingAddress($billing);

        $this->assertDatabaseHas((new CartAddress())->getTable(), [
            'postcode' => $shipping->postcode,
            'cart_id' => $cart->id,
            'type' => 'shipping',
        ]);

        $this->assertDatabaseHas((new CartAddress())->getTable(), [
            'postcode' => $billing->postcode,
            'cart_id' => $cart->id,
            'type' => 'billing',
        ]);
    }

    /** @test */
    public function can_update_shipping_address()
    {
        $currency = Currency::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $shipping = new CartAddress();
        $shipping->postcode = 'SHI P12';

        $billing = new CartAddress();
        $billing->postcode = 'BIL L12';

        $cart->getManager()->setShippingAddress($shipping);
        $cart->getManager()->setBillingAddress($billing);

        $this->assertEquals(1, $cart->addresses()->whereType('shipping')->count());
        $this->assertEquals(1, $cart->addresses()->whereType('billing')->count());

        $shipping->postcode = 'TES T34';

        $cart->getManager()->setShippingAddress($shipping);

        $this->assertDatabaseHas((new CartAddress())->getTable(), [
            'postcode' => $shipping->postcode,
            'cart_id' => $cart->id,
            'type' => 'shipping',
        ]);

        $this->assertEquals(1, $cart->addresses()->whereType('shipping')->count());
        $this->assertEquals(1, $cart->addresses()->whereType('billing')->count());
    }

    /** @test */
    public function can_handle_different_address_type_reuse()
    {
        $currency = Currency::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $shipping = new CartAddress();
        $shipping->postcode = 'SHI P12';

        $cart->getManager()->setShippingAddress($shipping);

        $this->assertEquals(1, $cart->addresses()->whereType('shipping')->count());
        $this->assertEquals(0, $cart->addresses()->whereType('billing')->count());

        $shipping = $cart->addresses()->whereType('shipping')->first();

        $this->assertNotNull($shipping->id);
        $this->assertEquals('shipping', $shipping->type);

        $cart->getManager()->setBillingAddress($shipping);

        $this->assertDatabaseHas((new CartAddress())->getTable(), [
            'postcode' => $shipping->postcode,
            'cart_id' => $cart->id,
            'type' => 'billing',
        ]);

        $this->assertEquals(1, $cart->addresses()->whereType('shipping')->count());
        $this->assertEquals(1, $cart->addresses()->whereType('billing')->count());
    }

    /** @test */
    public function can_have_unit_price_changed_by_a_cart_line_modifier()
    {
        $currency = Currency::factory()->create([
            'decimal_places' => 2,
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);
        $customerGroups = CustomerGroup::factory(2)->create();

        $taxClass = TaxClass::factory()->create([
            'name' => 'Foobar',
        ]);

        $taxClass->taxRateAmounts()->create(
            TaxRateAmount::factory()->make([
                'percentage' => 20,
                'tax_class_id' => $taxClass->id,
            ])->toArray()
        );

        $purchasable = ProductVariant::factory()->create([
            'tax_class_id' => $taxClass->id,
            'unit_quantity' => 1,
        ]);

        PriceModel::factory()->create([
            'price' => 100,
            'currency_id' => $currency->id,
            'tier' => 1,
            'priceable_type' => get_class($purchasable),
            'priceable_id' => $purchasable->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id' => $purchasable->id,
            'quantity' => 1,
        ]);

        // Modifier will set unit price to 1000
        app(CartLineModifiers::class)->add(TestCartLineModifier::class);

        (new CartManager($cart))->getCart();

        $this->assertEquals(1200, $cart->total->value);

        $line = $cart->lines->first();

        $this->assertInstanceOf(Price::class, $line->unitPrice);
        $this->assertEquals(1000, $line->unitPrice->value);

        $this->assertInstanceOf(Price::class, $line->subTotal);
        $this->assertEquals(1000, $line->subTotal->value);

        $this->assertInstanceOf(Price::class, $line->taxAmount);
        $this->assertEquals(200, $line->taxAmount->value);

        $this->assertInstanceOf(Price::class, $line->total);
        $this->assertEquals(1200, $line->total->value);

        $this->assertInstanceOf(Price::class, $line->discountTotal);
        $this->assertEquals(0, $line->discountTotal->value);

        $this->assertInstanceOf(TaxBreakdown::class, $line->taxBreakdown);
        $this->assertCount(1, $line->taxBreakdown->amounts);
    }

    /**
     * @test
     */
    public function can_create_order()
    {
        $currency = Currency::factory()->create([
            'default' => true,
        ]);

        $channel = Channel::factory()->create([
            'default' => true,
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
        ]);

        $shipping = CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'type' => 'shipping',
        ]);

        $billing = CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'type' => 'billing',
        ]);

        $cart->getManager()->setShippingAddress($shipping);
        $cart->getManager()->setBillingAddress($billing);

        $order = $cart->getManager()->createOrder();

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($cart->order_id, $order->id);
    }

    /**
     * @test
     */
    public function cant_create_order_from_incomplete_cart()
    {
        $currency = Currency::factory()->create([
            'default' => true,
        ]);

        $channel = Channel::factory()->create([
            'default' => true,
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
        ]);

        $this->expectException(BillingAddressMissingException::class);

        $cart->getManager()->createOrder();

        Cart::create([
            'cart_id' => $cart->id,
            'postcode' => 'foobar',
            'type' => 'billing',
        ]);

        $this->expectException(BillingAddressIncompleteException::class);

        $cart->getManager()->createOrder();
    }

    /**
     * @test
     */
    public function cant_create_order_for_cart_with_existing_order()
    {
        $currency = Currency::factory()->create([
            'default' => true,
        ]);

        $channel = Channel::factory()->create([
            'default' => true,
        ]);

        $order = Order::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
            'order_id' => $order->id,
        ]);

        CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'type' => 'shipping',
        ]);

        CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'type' => 'billing',
        ]);

        $this->expectException(OrderExistsException::class);

        $cart->getManager()->createOrder();
    }
}
