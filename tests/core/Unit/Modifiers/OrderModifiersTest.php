<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Carts\CreateOrder;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Modifiers\OrderModifier;
use Lunar\Core\Modifiers\OrderModifiers;
use Lunar\Tests\Core\TestCase;

use function Pest\Laravel\assertDatabaseCount;

uses(TestCase::class);
uses(RefreshDatabase::class);

function calculatedCartForOrder(): Cart
{
    TaxClass::factory()->create(['default' => true]);
    CustomerGroup::factory()->create(['default' => true]);

    $currency = Currency::factory()->create(['decimal_places' => 2]);
    $cart = Cart::factory()->create(['currency_id' => $currency->id]);
    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    return $cart->calculate();
}

test('creating sees the cart before the pipeline and created sees the finished order', function () {
    $cart = calculatedCartForOrder();
    $seen = new ArrayObject;

    app(OrderModifiers::class)->add(new class($seen) extends OrderModifier
    {
        public function __construct(protected ArrayObject $seen) {}

        public function creating(Cart $cart, Closure $next): Cart
        {
            $this->seen['creating'] = Order::count();
            $cart->meta = ['from_modifier' => true];

            return $next($cart);
        }

        public function created(Order $order, Closure $next): Order
        {
            $this->seen['created'] = [$order->exists, $order->lines()->count(), $order->sub_total];

            return $next($order);
        }
    });

    $order = app(CreateOrder::class)->execute($cart);

    expect($seen->getArrayCopy())->toBe([
        'creating' => 0,
        'created' => [true, 1, 100],
    ])->and($order->meta['from_modifier'])->toBeTrue();
});

test('an exception from a modifier rolls the order back', function () {
    $cart = calculatedCartForOrder();

    app(OrderModifiers::class)->add(new class extends OrderModifier
    {
        public function created(Order $order, Closure $next): Order
        {
            throw new RuntimeException('Modifier failed');
        }
    });

    expect(fn () => app(CreateOrder::class)->execute($cart))->toThrow(RuntimeException::class, 'Modifier failed');

    assertDatabaseCount((new Order)->getTable(), 0);
});
