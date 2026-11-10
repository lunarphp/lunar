<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Actions\Carts\CreateOrder;
use Lunar\DataTypes\Price as PriceDataType;
use Lunar\DataTypes\ShippingOption;
use Lunar\DiscountTypes\AmountOff;
use Lunar\Exceptions\DisallowMultipleCartOrdersException;
use Lunar\Facades\Discounts;
use Lunar\Facades\ModelManifest;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Channel;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Discount;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Lunar\Models\OrderLine;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRateAmount;
use Lunar\Tests\Core\Stubs\Models\CustomOrder;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

it('cant create order if already has complete and multiple disabled', function () {
    TaxClass::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $order = Order::factory()->create([
        'cart_id' => $cart->id,
        'placed_at' => now(),
    ]);

    (new CreateOrder)->execute($cart);
})->throws(DisallowMultipleCartOrdersException::class);

test('can create order if multiple enabled', function () {
    TaxClass::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $order = Order::factory()->create([
        'cart_id' => $cart->id,
        'placed_at' => now(),
    ]);

    $newOrder = (new CreateOrder)->execute($cart, allowMultipleOrders: true)->then(
        fn ($order) => $order->refresh()
    );

    $this->assertNotSame($newOrder->id, $order->id);
});

/** @test  */
function can_update_draft_order()
{
    TaxClass::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $updatedAt = now()->setTime('10', '00', '00');

    $orderA = Order::factory()->create([
        'cart_id' => $cart->id,
        'updated_at' => $updatedAt,
        'placed_at' => now(),
    ]);

    $orderB = Order::factory()->create([
        'cart_id' => $cart->id,
        'updated_at' => $updatedAt,
    ]);

    $updatedOrder = (new CreateOrder)->execute($cart, allowMultipleOrders: true)->then(
        fn ($order) => $order->refresh()
    );

    expect($orderB->id)->toBe($updatedOrder->id);
    expect($orderB->updated_at->eq($updatedOrder->updated_at))->toBeFalse();
    expect($orderA->updated_at->eq($updatedAt))->toBeTrue();
}

test('can create order', function () {
    ModelManifest::replace(
        Lunar\Models\Contracts\Order::class,
        CustomOrder::class
    );
    CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $billing = CartAddress::factory()->make([
        'type' => 'billing',
        'country_id' => Country::factory(),
        'first_name' => 'Santa',
        'line_one' => '123 Elf Road',
        'city' => 'Lapland',
        'postcode' => 'BILL',
    ]);

    $shipping = CartAddress::factory()->make([
        'type' => 'shipping',
        'country_id' => Country::factory(),
        'first_name' => 'Santa',
        'line_one' => '123 Elf Road',
        'city' => 'Lapland',
        'postcode' => 'SHIPP',
    ]);

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

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

    $cart->addresses()->createMany([
        $billing->toArray(),
        $shipping->toArray(),
    ]);

    $shippingOption = new ShippingOption(
        name: 'Basic Delivery',
        description: 'Basic Delivery',
        identifier: 'BASDEL',
        price: new PriceDataType(500, $cart->currency, 1),
        taxClass: $taxClass
    );

    ShippingManifest::addOption($shippingOption);

    $cart->shippingAddress->update([
        'shipping_option' => $shippingOption->getIdentifier(),
    ]);

    $cart->shippingAddress->shippingOption = $shippingOption;

    $order = $cart->createOrder();

    $breakdown = $cart->taxBreakdown->amounts->mapWithKeys(function ($tax, $key) {
        return [$key => [
            'description' => $tax->description,
            'identifier' => $tax->identifier,
            'percentage' => $tax->percentage,
            'value' => $tax->price->value,
            'currency_code' => $tax->price->currency->code,
        ]];
    });

    $datacheck = [
        'user_id' => $cart->user_id,
        'channel_id' => $cart->channel_id,
        'status' => config('lunar.orders.draft_status'),
        'customer_reference' => null,
        'sub_total' => $cart->subTotal->value,
        'total' => $cart->total->value,
        'discount_total' => $cart->discountTotal?->value,
        'shipping_total' => $cart->shippingTotal?->value ?: 0,
        'tax_breakdown' => json_encode($breakdown),
    ];

    $cart = $cart->refresh()->calculate();

    expect($cart->currentDraftOrder())->toBeInstanceOf(Order::class)
        ->and($order->cart_id)->toEqual($cart->id)
        ->and($cart->lines)->toHaveCount(1)
        ->and($order->lines)->toHaveCount(2)
        ->and($cart->addresses)->toHaveCount(2)
        ->and($order->addresses)->toHaveCount(2)
        ->and($order->shippingAddress)->toBeInstanceOf(OrderAddress::class)
        ->and($order->billingAddress)->toBeInstanceOf(OrderAddress::class);

    assertDatabaseHas((new Order)->getTable(), $datacheck);
    assertDatabaseHas((new OrderLine)->getTable(), [
        'identifier' => $shippingOption->getIdentifier(),
    ]);

    $order->save();
    $containsCurrency = str_contains($order->fresh()->getRawOriginal('tax_breakdown'), '"currency"');
    expect($containsCurrency)->toBeFalse();
});

test('can create order with customer', function () {
    CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $customer = Customer::factory()->create();

    $billing = CartAddress::factory()->make([
        'type' => 'billing',
        'country_id' => Country::factory(),
        'first_name' => 'Santa',
        'line_one' => '123 Elf Road',
        'city' => 'Lapland',
        'postcode' => 'BILL',
    ]);

    $shipping = CartAddress::factory()->make([
        'type' => 'shipping',
        'country_id' => Country::factory(),
        'first_name' => 'Santa',
        'line_one' => '123 Elf Road',
        'city' => 'Lapland',
        'postcode' => 'SHIPP',
    ]);

    $taxClass = TaxClass::factory()->create();

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'customer_id' => $customer->id,
    ]);

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

    $cart->addresses()->createMany([
        $billing->toArray(),
        $shipping->toArray(),
    ]);

    $shippingOption = new ShippingOption(
        name: 'Basic Delivery',
        description: 'Basic Delivery',
        identifier: 'BASDEL',
        price: new PriceDataType(500, $cart->currency, 1),
        taxClass: $taxClass
    );

    ShippingManifest::addOption($shippingOption);

    $cart->shippingAddress->update([
        'shipping_option' => $shippingOption->getIdentifier(),
    ]);

    $cart->shippingAddress->shippingOption = $shippingOption;

    $order = $cart->createOrder();

    $breakdown = $cart->taxBreakdown->amounts->mapWithKeys(function ($tax, $key) {
        return [$key => [
            'description' => $tax->description,
            'identifier' => $tax->identifier,
            'percentage' => $tax->percentage,
            'value' => $tax->price->value,
            'currency_code' => $tax->price->currency->code,
        ]];
    });

    $datacheck = [
        'user_id' => $cart->user_id,
        'customer_id' => $cart->customer_id,
        'channel_id' => $cart->channel_id,
        'status' => config('lunar.orders.draft_status'),
        'customer_reference' => null,
        'sub_total' => $cart->subTotal->value,
        'total' => $cart->total->value,
        'discount_total' => $cart->discountTotal?->value,
        'shipping_total' => $cart->shippingTotal?->value ?: 0,
        'tax_breakdown' => json_encode($breakdown),
    ];

    $cart = $cart->refresh()->calculate();

    $this->assertDatabaseHas((new Order)->getTable(), $datacheck);
});

test('can keep the discount when the draft order is created again', function () {
    TaxClass::factory()->create([
        'default' => true,
    ]);

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $channel = Channel::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'coupon_code' => 'SAVE10',
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 2,
    ]);

    // A single-use coupon, which is the ordinary shape of a promotional code.
    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Ten off',
        'coupon' => 'SAVE10',
        'uses' => 0,
        'max_uses' => 1,
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                $currency->code => 500,
            ],
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => ['enabled' => true, 'starts_at' => now()->subHour()],
    ]);

    $discount->channels()->sync([
        $channel->id => ['enabled' => true, 'starts_at' => now()->subHour()],
    ]);

    $cart->calculate();

    $orderA = (new CreateOrder)->execute($cart)->then(fn ($order) => $order->refresh());

    expect($orderA->discount_total->value)->toEqual(500);
    expect($discount->refresh()->uses)->toEqual(1);

    // The card is declined and the shopper tries another one. That is a fresh
    // request, so nothing is memoised from the first attempt.
    Discounts::resetDiscounts();

    $cart = Cart::find($cart->id);
    $cart->calculate();

    $orderB = (new CreateOrder)->execute($cart)->then(fn ($order) => $order->refresh());

    expect($orderB->id)->toEqual($orderA->id);
    expect($orderB->discount_total->value)->toEqual(500);

    // The retry must not consume a second use of a single-use coupon.
    expect($discount->refresh()->uses)->toEqual(1);
});

test('can not reuse a discount another cart has exhausted', function () {
    TaxClass::factory()->create([
        'default' => true,
    ]);

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $channel = Channel::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Ten off',
        'coupon' => 'SAVE10',
        'uses' => 0,
        'max_uses' => 1,
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                $currency->code => 500,
            ],
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => ['enabled' => true, 'starts_at' => now()->subHour()],
    ]);

    $discount->channels()->sync([
        $channel->id => ['enabled' => true, 'starts_at' => now()->subHour()],
    ]);

    $makeCart = function () use ($currency, $channel, $purchasable) {
        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
            'coupon_code' => 'SAVE10',
        ]);

        $cart->lines()->create([
            'purchasable_type' => $purchasable->getMorphClass(),
            'purchasable_id' => $purchasable->id,
            'quantity' => 2,
        ]);

        return $cart;
    };

    $cartA = $makeCart();
    $cartA->calculate();
    $orderA = (new CreateOrder)->execute($cartA)->then(fn ($order) => $order->refresh());

    expect($orderA->discount_total->value)->toEqual(500);
    expect($discount->refresh()->uses)->toEqual(1);

    // A different shopper, with the last use already spent.
    Discounts::resetDiscounts();

    $cartB = $makeCart();
    $cartB->calculate();
    $orderB = (new CreateOrder)->execute($cartB)->then(fn ($order) => $order->refresh());

    expect($orderB->id)->not->toEqual($orderA->id);
    expect($orderB->discount_total->value)->toEqual(0);
    expect($discount->refresh()->uses)->toEqual(1);
});

test('can still enforce other conditions on a discount the cart consumed', function () {
    TaxClass::factory()->create([
        'default' => true,
    ]);

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $channel = Channel::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'coupon_code' => 'SAVE10',
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $line = $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 2,
    ]);

    // Spend at least 15.00 to qualify. Two units is 20.00, one is 10.00.
    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Ten off',
        'coupon' => 'SAVE10',
        'uses' => 0,
        'max_uses' => 1,
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                $currency->code => 500,
            ],
            'min_prices' => [
                $currency->code => 1500,
            ],
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => ['enabled' => true, 'starts_at' => now()->subHour()],
    ]);

    $discount->channels()->sync([
        $channel->id => ['enabled' => true, 'starts_at' => now()->subHour()],
    ]);

    $cart->calculate();

    $orderA = (new CreateOrder)->execute($cart)->then(fn ($order) => $order->refresh());

    expect($orderA->discount_total->value)->toEqual(500);

    // The shopper drops a unit, taking the cart under the minimum spend. Being
    // the cart that consumed the discount must not exempt it from that.
    Discounts::resetDiscounts();

    $cart = Cart::find($cart->id);
    $cart->updateLine($line->id, 1);
    $cart->calculate();

    $orderB = (new CreateOrder)->execute($cart)->then(fn ($order) => $order->refresh());

    expect($orderB->id)->toEqual($orderA->id);
    expect($orderB->sub_total->value)->toEqual(1000);
    expect($orderB->discount_total->value)->toEqual(0);
});

test('can not consume a discount twice on one cart instance', function () {
    TaxClass::factory()->create([
        'default' => true,
    ]);

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $channel = Channel::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'coupon_code' => 'SAVE10',
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Ten off',
        'coupon' => 'SAVE10',
        'uses' => 0,
        'max_uses' => 1,
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                $currency->code => 500,
            ],
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => ['enabled' => true, 'starts_at' => now()->subHour()],
    ]);

    $discount->channels()->sync([
        $channel->id => ['enabled' => true, 'starts_at' => now()->subHour()],
    ]);

    // The same instance throughout: no reload between the two attempts. This is
    // what a checkout that retries in one request looks like, and it is the case
    // any memoisation of consumedDiscountIds() has to survive - a set cached
    // before the first order exists would still be empty for the second.
    $cart->calculate();

    $orderA = (new CreateOrder)->execute($cart)->then(fn ($order) => $order->refresh());

    expect($orderA->discount_total->value)->toEqual(500);
    expect($discount->refresh()->uses)->toEqual(1);

    $cart->calculate();

    $orderB = (new CreateOrder)->execute($cart)->then(fn ($order) => $order->refresh());

    expect($orderB->id)->toEqual($orderA->id);
    expect($orderB->discount_total->value)->toEqual(500);
    expect($discount->refresh()->uses)->toEqual(1);
});

test('keeps its own discount when a cart is priced again after order creation', function () {
    TaxClass::factory()->create([
        'default' => true,
    ]);

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $channel = Channel::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'coupon_code' => 'SAVE10',
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Ten off',
        'coupon' => 'SAVE10',
        'uses' => 0,
        'max_uses' => 1,
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                $currency->code => 500,
            ],
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => ['enabled' => true, 'starts_at' => now()->subHour()],
    ]);

    $discount->channels()->sync([
        $channel->id => ['enabled' => true, 'starts_at' => now()->subHour()],
    ]);

    $cart->calculate();

    $orderA = (new CreateOrder)->execute($cart)->then(fn ($order) => $order->refresh());

    expect($orderA->discount_total->value)->toEqual(500);
    expect($discount->refresh()->uses)->toEqual(1);

    // Priced again on the same instance, with the discount set rebuilt: the
    // cart's own use must not read as an exhausted coupon, or the retry is
    // re-priced without the discount the shopper was quoted.
    Discounts::resetDiscounts();

    $cart->recalculate();

    $orderB = (new CreateOrder)->execute($cart)->then(fn ($order) => $order->refresh());

    expect($orderB->id)->toEqual($orderA->id);
    expect($orderB->discount_total->value)->toEqual(500);
    expect($discount->refresh()->uses)->toEqual(1);
});
