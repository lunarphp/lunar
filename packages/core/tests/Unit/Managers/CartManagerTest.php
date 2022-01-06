<?php

namespace GetCandy\Tests\Unit\Managers;

use GetCandy\Base\CartModifiers;
use GetCandy\Base\Purchasable;
use GetCandy\DataTypes\Price;
use GetCandy\Exceptions\CartLineIdMismatchException;
use GetCandy\Exceptions\InvalidCartLineQuantityException;
use GetCandy\Exceptions\MaximumCartLineQuantityException;
use GetCandy\Managers\CartManager;
use GetCandy\Models\Cart;
use GetCandy\Models\CartLine;
use GetCandy\Models\Channel;
use GetCandy\Models\Currency;
use GetCandy\Models\Price as PriceModel;
use GetCandy\Models\ProductVariant;
use GetCandy\Tests\Stubs\TestCartModifier;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;

/**
 * @group getcandy.cart-manager
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
            $this->assertDatabaseHas((new CartLine)->getTable(), $check);
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
}
