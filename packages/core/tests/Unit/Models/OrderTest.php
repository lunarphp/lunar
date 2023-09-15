<?php

namespace Lunar\Tests\Unit\Models;

use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdownItem;
use Lunar\Base\OrderReferenceGenerator;
use Lunar\DataTypes\Price;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;
use Lunar\Models\Transaction;
use Lunar\Tests\Stubs\User;
use Lunar\Tests\TestCase;
use Ramsey\Uuid\Type\Integer;

/**
 * @group lunar.orders
 */
class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Language::factory()->create([
            'default' => true,
            'code' => 'en',
        ]);

        Currency::factory()->create([
            'default' => true,
            'decimal_places' => 2,
        ]);
    }

    /** @test */
    public function can_fetch_cart_relationship()
    {
        Currency::factory()->create([
            'default' => true,
        ]);
        $cart = Cart::factory()->create();

        $order = Order::factory()->create([
            'cart_id' => $cart->id,
            'user_id' => null,
        ]);

        $this->assertEquals($cart->id, $order->cart->id);
    }

    /** @test */
    public function can_make_an_order()
    {
        Currency::factory()->create([
            'default' => true,
        ]);

        $order = Order::factory()->create([
            'user_id' => null,
        ]);

        $data = $order->getRawOriginal();

        $this->assertDatabaseHas((new Order())->getTable(), $data);
    }

    /** @test */
    public function can_serialize_an_order()
    {
        Currency::factory()->create([
            'default' => true,
        ]);

        $order = Order::factory()->create([
            'user_id' => null,
            'tax_breakdown' => [
                [
                    'description' => 'VAT',
                    'total' => 99,
                    'percentage' => 20,
                ],
            ],
        ]);

        $data = $order->toArray();

        $this->assertIsInt($data['total']['value']);
    }

    /** @test */
    public function order_has_correct_casting()
    {
        Currency::factory()->create([
            'default' => true,
        ]);

        $order = Order::factory()->create([
            'user_id' => null,
            'placed_at' => now(),
            'meta' => [
                'foo' => 'bar',
            ],
            'tax_breakdown' => [
                ['name' => 'VAT', 'percentage' => 20, 'total' => 200],
            ],
        ]);

        $this->assertIsObject($order->meta);
        $this->assertIsIterable($order->tax_breakdown);
        $this->assertInstanceOf(DateTime::class, $order->placed_at);
    }

    /** @test */
    public function can_create_lines()
    {
        Currency::factory()->create([
            'default' => true,
        ]);

        $order = Order::factory()->create([
            'user_id' => null,
            'placed_at' => now(),
            'meta' => [
                'foo' => 'bar',
            ],
            'tax_breakdown' => [
                ['description' => 'VAT', 'percentage' => 20, 'total' => 200],
            ],
        ]);

        $this->assertCount(0, $order->lines);

        OrderLine::factory()->create([
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => ProductVariant::factory()->create()->id,
            'order_id' => $order->id,
        ]);

        $this->assertCount(1, $order->refresh()->lines);
    }

    /** @test */
    public function can_update_status()
    {
        $order = Order::factory()->create([
            'user_id' => null,
            'status' => 'status_a',
        ]);

        $this->assertEquals('status_a', $order->status);

        $order->update([
            'status' => 'status_b',
        ]);

        $this->assertEquals('status_b', $order->status);
    }

    /** @test */
    public function can_create_transaction_for_order()
    {
        Currency::factory()->create([
            'default' => true,
        ]);

        $order = Order::factory()->create([
            'user_id' => null,
            'status' => 'status_a',
        ]);

        $this->assertCount(0, $order->transactions);

        $transaction = Transaction::factory()->make()->toArray();

        unset($transaction['currency']);

        $order->transactions()->create($transaction);

        $this->assertCount(1, $order->refresh()->transactions);
    }

    /** @test */
    public function can_retrieve_different_transaction_types_for_order()
    {
        Currency::factory()->create([
            'default' => true,
        ]);

        $order = Order::factory()->create([
            'user_id' => null,
            'status' => 'status_a',
        ]);

        $this->assertCount(0, $order->transactions);

        $charge = Transaction::factory()->create([
            'order_id' => $order->id,
            'amount' => 200,
            'type' => 'capture',
        ]);

        $refund = Transaction::factory()->create([
            'order_id' => $order->id,
            'type' => 'refund',
        ]);

        $order = $order->refresh();

        $this->assertCount(2, $order->transactions);

        $this->assertCount(1, $order->captures);
        $this->assertCount(1, $order->refunds);

        $this->assertEquals($charge->id, $order->captures->first()->id);
        $this->assertEquals($refund->id, $order->refunds->first()->id);
    }

    /** @test */
    public function can_have_user_and_customer_associated()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@domain.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        $customer = $user->customers()->create(
            Customer::factory()->make()->toArray()
        );

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'user_id' => $user->getKey(),
        ]);

        $this->assertEquals($customer->id, $order->customer->id);
        $this->assertEquals($user->getKey(), $order->user->getKey());
    }

    /** @test */
    public function can_check_order_is_placed()
    {
        Currency::factory()->create([
            'default' => true,
        ]);

        $order = Order::factory()->create([
            'user_id' => null,
        ]);

        $this->assertTrue($order->isDraft());

        $order->placed_at = now();

        $this->assertTrue($order->isPlaced());
    }

    /** @test */
    public function can_cast_and_store_shipping_breakdown()
    {
        $order = Order::factory()->create();

        $breakdown = new ShippingBreakdown(
            items: collect([
                new ShippingBreakdownItem(
                    name: 'Breakdown A',
                    identifier: 'BA',
                    price: $shippingPrice = new Price(123, $currency = Currency::getDefault(), 1)
                ),
            ])
        );

        $order->shipping_breakdown = $breakdown;

        $order->save();

        $this->assertDatabaseHas((new Order)->getTable(), [
            'shipping_breakdown' => json_encode([[
                'name' => 'Breakdown A',
                'identifier' => 'BA',
                'value' => 123,
                'formatted' => $shippingPrice->formatted,
                'currency' => $currency->toArray(),
            ]]),
        ]);

        $breakdown = $order->refresh()->shipping_breakdown;

        $this->assertCount(1, $breakdown->items);

        $breakdownItem = $breakdown->items->first();

        $this->assertEquals('Breakdown A', $breakdownItem->name);
        $this->assertEquals('BA', $breakdownItem->identifier);
        $this->assertInstanceOf(Price::class, $breakdownItem->price);
        $this->assertEquals(123, $breakdownItem->price->value);
    }
}
