<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Transaction;
use Lunar\Core\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Core\ValueObjects\Cart\ShippingBreakdownItem;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;
use Lunar\Core\ValueObjects\Cart\TaxBreakdownAmount;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('cross-db');

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
    ]);
});

test('can fetch cart relationship', function () {
    Currency::factory()->create([
        'default' => true,
    ]);
    $cart = Cart::factory()->create();

    $order = Order::factory()->create([
        'cart_id' => $cart->id,
        'user_id' => null,
    ]);

    expect($order->cart->id)->toEqual($cart->id);
});

test('can make an order', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    $order = Order::factory()->create([
        'user_id' => null,
    ]);

    $this->assertDatabaseHas((new Order)->getTable(), [
        'id' => $order->id,
        'reference' => $order->reference,
        'payment_status' => (string) $order->payment_status,
        'sub_total' => $order->sub_total,
        'tax_total' => $order->tax_total,
        'total' => $order->total,
        'currency_code' => $order->currency_code,
    ]);
});

test('order has correct casting', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    $order = Order::factory()->create([
        'user_id' => null,
        'placed_at' => now(),
        'meta' => [
            'foo' => 'bar',
        ],
    ]);

    expect($order->meta)->toBeObject();
    expect($order->tax_breakdown)->toBeInstanceOf(TaxBreakdown::class);
    expect($order->placed_at)->toBeInstanceOf(DateTime::class);
});

test('can create lines', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    $order = Order::factory()->create([
        'user_id' => null,
        'placed_at' => now(),
        'meta' => [
            'foo' => 'bar',
        ],
    ]);

    expect($order->lines)->toHaveCount(0);

    $variant = ProductVariant::factory()->create();

    OrderLine::factory()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'order_id' => $order->id,
    ]);

    expect($order->refresh()->lines)->toHaveCount(1);
});

test('can close and reopen an order', function () {
    $order = Order::factory()->create(['user_id' => null]);

    expect($order->isOpen())->toBeTrue()
        ->and($order->isClosed())->toBeFalse();

    $order->close();

    expect($order->fresh()->isClosed())->toBeTrue()
        ->and($order->fresh()->closed_at)->not->toBeNull();

    $order->reopen();

    expect($order->fresh()->isOpen())->toBeTrue()
        ->and($order->fresh()->closed_at)->toBeNull();
});

test('open and closed scopes filter by archive state', function () {
    Order::factory()->create();
    Order::factory()->closed()->create();

    expect(Order::open()->count())->toBe(1)
        ->and(Order::closed()->count())->toBe(1);
});

test('can create transaction for order', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    $order = Order::factory()->create([
        'user_id' => null,

    ]);

    expect($order->transactions)->toHaveCount(0);

    $transaction = Transaction::factory()->make()->toArray();

    unset($transaction['currency']);

    $order->transactions()->create($transaction);

    expect($order->refresh()->transactions)->toHaveCount(1);
});

test('can retrieve different transaction types for order', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    $order = Order::factory()->create([
        'user_id' => null,

    ]);

    expect($order->transactions)->toHaveCount(0);

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

    expect($order->transactions)->toHaveCount(2);

    expect($order->captures)->toHaveCount(1);
    expect($order->refunds)->toHaveCount(1);

    expect($order->captures->first()->id)->toEqual($charge->id);
    expect($order->refunds->first()->id)->toEqual($refund->id);
});

test('can have user and customer associated', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@domain.com',
        'email_verified_at' => now(),
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'remember_token' => Str::random(10),
    ]);

    $customer = $user->customers()->create(
        Customer::factory()->make()->toArray()
    );

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'user_id' => $user->getKey(),
    ]);

    expect($order->customer->id)->toEqual($customer->id);
    expect($order->user->getKey())->toEqual($user->getKey());
});

test('can check order is placed', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    $order = Order::factory()->create([
        'user_id' => null,
    ]);

    expect($order->isDraft())->toBeTrue();

    $order->placed_at = now();

    expect($order->isPlaced())->toBeTrue();
});

test('can cast and store shipping breakdown', function () {
    $order = Order::factory()->create();

    $breakdown = new ShippingBreakdown(
        items: collect([
            new ShippingBreakdownItem(
                name: 'Breakdown A',
                identifier: 'BA',
                price: $shippingPrice = new PriceValue(123, $currency = Currency::getDefault(), 1)
            ),
        ])
    );

    $order->shipping_breakdown = $breakdown;

    $order->save();

    $breakdown = $order->refresh()->shipping_breakdown;

    expect($breakdown->items)->toHaveCount(1);

    $breakdownItem = $breakdown->items->first();

    expect($breakdownItem->name)->toEqual('Breakdown A');
    expect($breakdownItem->identifier)->toEqual('BA');
    expect($breakdownItem->price)->toBeInstanceOf(PriceValue::class);
    expect($breakdownItem->price->value)->toEqual(123);
});

test('can delete an order', function () {
    Currency::factory()->create([
        'default' => false,
    ]);
    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    $order = Order::factory()->create([
        'user_id' => null,
        'currency_code' => $currency->code,
    ]);

    OrderLine::factory(4)->create([
        'order_id' => $order->id,
        'tax_breakdown' => new TaxBreakdown(collect([
            new TaxBreakdownAmount(
                price: new PriceValue(10, $currency),
                identifier: 'VAT',
                description: 'VAT',
                percentage: 20
            ),
        ])),
    ]);

    assertDatabaseCount((new OrderLine)->getTable(), 4);

    foreach ($order->refresh()->lines as $line) {
        $line->delete();
    }

    assertDatabaseCount(OrderLine::class, 0);

    assertDatabaseHas(Order::class, [
        'id' => $order->id,
    ]);

    $order->delete();

    assertDatabaseMissing(Order::class, [
        'id' => $order->id,
    ]);
});
