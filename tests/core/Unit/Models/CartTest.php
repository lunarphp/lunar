<?php

uses(\Lunar\Tests\TestCase::class);

use Illuminate\Support\Facades\Config;
use Lunar\DataTypes\Price as DataTypesPrice;
use Lunar\DataTypes\ShippingOption;
use Lunar\DiscountTypes\AmountOff;
use Lunar\Exceptions\Carts\CartException;
use Lunar\Exceptions\FingerprintMismatchException;
use Lunar\Facades\Discounts;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\CartLine;
use Lunar\Models\Channel;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Discount;
use Lunar\Models\Order;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRate;
use Lunar\Models\TaxRateAmount;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZonePostcode;
use Stubs\User as StubUser;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

//function setAuthUserConfig()
//{
//    Config::set('auth.providers.users.model', 'Lunar\Tests\Stubs\User');
//}

test('can make a cart', function () {
    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();

    $cart = Cart::create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'meta' => ['foo' => 'bar'],
    ]);

    $this->assertDatabaseHas((new Cart())->getTable(), [
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'meta' => json_encode(['foo' => 'bar']),
    ]);

    $variant = ProductVariant::factory()->create();

    $cart->lines()->create([
        'purchasable_type' => ProductVariant::class,
        'purchasable_id' => $variant->id,
        'quantity' => 1,
    ]);

    expect($cart->lines()->get())->toHaveCount(1);
});

test('can save coupon code', function () {
    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => 'valid-coupon',
        'data' => [
            'fixed_value' => false,
            'percentage' => 10,
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'visible' => true,
            'starts_at' => now(),
        ],
    ]);

    $cart = Cart::create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'meta' => ['foo' => 'bar'],
    ]);

    expect($cart->coupon_code)->toBeNull();

    $cart->coupon_code = 'valid-coupon';

    Discounts::apply($cart);

    $cart->saveQuietly();

    expect($cart->refresh()->coupon_code)->toEqual('valid-coupon');
});

test('can associate cart with user with no customer attached', function () {
    setAuthUserConfig();

    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();
    $user = StubUser::factory()->create();

    Cart::create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'user_id' => $user->getKey(),
    ]);

    $this->assertDatabaseHas((new Cart())->getTable(), [
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'user_id' => $user->getKey(),
    ]);
});

test('can associate cart with customer', function () {
    setAuthUserConfig();

    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();
    $customer = Customer::factory()->create();

    $cart = Cart::create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $cart->setCustomer($customer);

    $this->assertDatabaseHas((new Cart)->getTable(), [
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'customer_id' => $customer->id,
    ]);
});

test('ensure associate user belongs to customer', function () {
    setAuthUserConfig();

    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();
    $customer = Customer::factory()->create();
    $users = StubUser::factory(5)->create();

    $user = $users->first();

    $cart = Cart::create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $cartData = [
        'id' => $cart->id,
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'customer_id' => $customer->id,
        'user_id' => $user->id,
    ];

    $cart->setCustomer($customer);

    $checked = false;

    try {
        $cart->associate($user);
    } catch (Exception $e) {
        $checked = true;
    }

    expect($checked)->toBeTrue();

    $this->assertDatabaseMissing((new Cart)->getTable(), $cartData);

    $user->customers()->attach($customer);

    $cart->associate($user);

    $this->assertDatabaseHas((new Cart)->getTable(), $cartData);
});

test('ensure associate customer belongs to user', function () {
    setAuthUserConfig();

    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();
    $customer = Customer::factory()->create();
    $users = StubUser::factory(5)->create();

    $user = $users->first();

    $cart = Cart::create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $cartData = [
        'id' => $cart->id,
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'customer_id' => $customer->id,
        'user_id' => $user->id,
    ];

    $cart->associate($user);

    $checked = false;

    try {
        $cart->setCustomer($customer);
    } catch (Exception $e) {
        $checked = true;
    }

    expect($checked)->toBeTrue();

    $this->assertDatabaseMissing((new Cart)->getTable(), $cartData);

    $user->customers()->attach($customer);

    $cart->setCustomer($customer);

    $this->assertDatabaseHas((new Cart)->getTable(), $cartData);
});

test('will not retrieve user cart if order is placed', function () {
    setAuthUserConfig();

    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();
    $user = StubUser::factory()->create();

    $cart = Cart::create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'user_id' => $user->getKey(),
    ]);

    Order::factory()->create([
        'cart_id' => $cart->id,
        'placed_at' => now(),
    ]);

    expect(Cart::whereUserId($user->getKey())->active()->first())->toBeNull();
});

test('can get cart draft order', function () {
    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();
    $user = StubUser::factory()->create();

    $cart = Cart::create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    Order::factory()->create([
        'cart_id' => $cart->id,
        'placed_at' => now(),
    ]);

    $draftOrder = Order::factory()->create([
        'cart_id' => $cart->id,
        'placed_at' => null,
    ]);

    expect($cart->draftOrder->id)->toEqual($draftOrder->id);

    $draftOrder->delete();

    expect($cart->draftOrder()->first())->toBeNull();
});

test('can get cart draft order by id', function () {
    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();
    $user = StubUser::factory()->create();

    $cart = Cart::create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    Order::factory()->create([
        'cart_id' => $cart->id,
        'placed_at' => now(),
    ]);

    $draftOrder = Order::factory()->create([
        'cart_id' => $cart->id,
        'placed_at' => null,
    ]);

    $draftOrderTwo = Order::factory()->create([
        'cart_id' => $cart->id,
        'placed_at' => null,
    ]);

    expect($cart->draftOrder->id)->toEqual($draftOrder->id);
    expect($cart->draftOrder($draftOrderTwo->id)->first()->id)->toEqual($draftOrderTwo->id);
});

test('can check for completed order', function () {
    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();
    $user = StubUser::factory()->create();

    $cart = Cart::create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $order = Order::factory()->create([
        'cart_id' => $cart->id,
        'placed_at' => null,
    ]);

    expect($cart->hasCompletedOrders())->toBeFalse();

    $order->update([
        'placed_at' => now(),
    ]);

    expect($cart->hasCompletedOrders())->toBeTrue();
});

test('can retrieve active cart', function () {
    setAuthUserConfig();

    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();
    $user = StubUser::factory()->create();

    $cart = Cart::create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'user_id' => $user->getKey(),
    ]);

    expect(Cart::whereUserId($user->getKey())->active()->first()->id)->toEqual($cart->id);
});

test('can associate cart with user with customer attached', function () {
    setAuthUserConfig();

    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();
    $user = StubUser::factory()->create();
    $customer = Customer::factory()->create();

    $customer->users()->attach($user);

    $cart = Cart::create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'user_id' => $user->getKey(),
    ]);

    $this->assertDatabaseHas((new Cart())->getTable(), [
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'user_id' => $user->getKey(),
    ]);
});

test('can calculate the cart', function () {
    $currency = Currency::factory()
        ->state([
            'code' => 'USD',
        ])
        ->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    // Add product
    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
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

    // Add product with unit qty
    $purchasable = ProductVariant::factory()
        ->state([
            'unit_quantity' => 100,
        ])
        ->create();

    Price::factory()->create([
        'price' => 158,
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

    // Set user
    $this->actingAs(
        StubUser::factory()->create()
    );

    $cart->calculate();

    expect($cart->lines[0]->unitPrice->value)->toEqual(100);
    expect($cart->lines[0]->unitPrice->unitFormatted(null, NumberFormatter::CURRENCY, 6))->toEqual('$1.00');
    expect($cart->lines[0]->unitPrice->unitFormatted(null, NumberFormatter::CURRENCY, 6, false))->toEqual('$1.000000');
    expect($cart->lines[1]->unitPrice->value)->toEqual(158);
    expect($cart->lines[1]->unitPrice->unitDecimal(false))->toEqual(0.0158);
    expect($cart->lines[1]->unitPrice->unitFormatted(null, NumberFormatter::CURRENCY, 6))->toEqual('$0.0158');
    expect($cart->lines[1]->unitPrice->unitFormatted(null, NumberFormatter::CURRENCY, 6, false))->toEqual('$0.015800');
    expect($cart->subTotal->value)->toEqual(103);
    expect($cart->total->value)->toEqual(124);
    expect($cart->taxBreakdown->amounts)->toHaveCount(2);
});

test('can calculate the cart inc vat', function () {
    Config::set('lunar.pricing.stored_inclusive_of_tax', true);

    $currency = Currency::factory()
        ->state([
            'code' => 'USD',
        ])
        ->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    // Add product
    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
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

    // Add product with unit qty
    $purchasable = ProductVariant::factory()
        ->state([
            'unit_quantity' => 100,
        ])
        ->create();

    Price::factory()->create([
        'price' => 158,
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

    // Set user
    $this->actingAs(
        StubUser::factory()->create()
    );

    $cart->calculate();

    expect($cart->lines[0]->unitPrice->value)->toEqual(100);
    expect($cart->lines[0]->unitPrice->unitFormatted(null, NumberFormatter::CURRENCY, 6))->toEqual('$1.00');
    expect($cart->lines[0]->unitPrice->unitFormatted(null, NumberFormatter::CURRENCY, 6, false))->toEqual('$1.000000');
    expect($cart->lines[1]->unitPrice->value)->toEqual(158);
    expect($cart->lines[1]->unitPrice->unitDecimal(false))->toEqual(0.0158);
    expect($cart->lines[1]->unitPrice->unitFormatted(null, NumberFormatter::CURRENCY, 6))->toEqual('$0.0158');
    expect($cart->lines[1]->unitPrice->unitFormatted(null, NumberFormatter::CURRENCY, 6, false))->toEqual('$0.015800');
    expect($cart->subTotal->value)->toEqual(103);
    expect($cart->total->value)->toEqual(103);
    expect($cart->taxBreakdown->amounts)->toHaveCount(2);
});

test('can add cart lines', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'tier' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasable),
        'priceable_id' => $purchasable->id,
    ]);

    expect($cart->lines)->toHaveCount(0);

    $cart->add($purchasable, 1);

    expect($cart->lines)->toHaveCount(1);
});

test('can remove cart lines', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'tier' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasable),
        'priceable_id' => $purchasable->id,
    ]);

    expect($cart->lines)->toHaveCount(0);

    $cart->add($purchasable, 1);

    expect($cart->lines)->toHaveCount(1);

    $cart->remove($cart->lines->first()->id);

    expect($cart->lines)->toHaveCount(0);
});

test('cannot add zero quantity line', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'tier' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasable),
        'priceable_id' => $purchasable->id,
    ]);

    expect($cart->lines)->toHaveCount(0);

    $this->expectException(CartException::class);

    $cart->add($purchasable, 0);
});

test('can update existing cart line', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'tier' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasable),
        'priceable_id' => $purchasable->id,
    ]);

    expect($cart->lines)->toHaveCount(0);

    $cart->add($purchasable, 1);

    $cartLine = $cart->refresh()->lines->first();

    $this->assertDatabaseHas((new CartLine())->getTable(), [
        'quantity' => 1,
        'id' => $cartLine->id,
    ]);

    $cart->updateLine($cartLine->id, 2);

    $this->assertDatabaseHas((new CartLine())->getTable(), [
        'quantity' => 2,
        'id' => $cartLine->id,
    ]);
});

test('can calculate shipping', function () {
    $country = Country::factory()->create();

    $billing = CartAddress::factory()->make([
        'type' => 'billing',
        'country_id' => $country->id,
        'first_name' => 'Santa',
        'line_one' => '123 Elf Road',
        'city' => 'Lapland',
        'postcode' => 'BILL',
    ]);

    $shipping = CartAddress::factory()->make([
        'type' => 'shipping',
        'country_id' => $country->id,
        'first_name' => 'Santa',
        'line_one' => '123 Elf Road',
        'city' => 'Lapland',
        'postcode' => 'SHIPP',
    ]);

    $taxClass = TaxClass::factory()->create();

    $taxZone = TaxZone::factory()->create();

    TaxZonePostcode::factory()->create([
        'country_id' => $country->id,
        'tax_zone_id' => $taxZone->id,
        'postcode' => 'SHIPP',
    ]);

    $taxRate = TaxRate::factory()->create([
        'tax_zone_id' => $taxZone->id,
    ]);

    TaxRateAmount::factory()->create([
        'tax_rate_id' => $taxRate->id,
        'tax_class_id' => $taxClass->id,
    ]);

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'tier' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasable),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->addresses()->createMany([
        $billing->toArray(),
        $shipping->toArray(),
    ]);

    $shippingOption = new ShippingOption(
        name: 'Basic Delivery',
        description: 'Basic Delivery',
        identifier: 'BASDEL',
        price: new DataTypesPrice(500, $cart->currency, 1),
        taxClass: $taxClass
    );

    ShippingManifest::addOption($shippingOption);

    $cart->shippingAddress->update([
        'shipping_option' => $shippingOption->getIdentifier(),
    ]);

    $cart->shippingAddress->shippingOption = $shippingOption;

    expect($cart->lines)->toHaveCount(0);

    $cart->add($purchasable, 1);

    $cart->calculate();

    expect($cart->subTotal->value)->toEqual(100);
    expect($cart->shippingSubTotal->value)->toEqual(500);
    expect($cart->shippingTotal->value)->toEqual(600);
    expect($cart->total->value)->toEqual(720);

    Config::set('lunar.pricing.stored_inclusive_of_tax', true);

    $cart->calculate();

    expect($cart->shippingTotal->value)->toEqual(500);
    expect($cart->total->value)->toEqual(600);
});

test('can create a discount breakdown', function () {
    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => 'valid-coupon',
        'data' => [
            'fixed_value' => false,
            'percentage' => 10,
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'visible' => true,
            'starts_at' => now(),
        ],
    ]);

    $cart = Cart::create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'meta' => ['foo' => 'bar'],
    ]);

    $variant = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'tier' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($variant),
        'priceable_id' => $variant->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => ProductVariant::class,
        'purchasable_id' => $variant->id,
        'quantity' => 1,
    ]);

    expect($cart->coupon_code)->toBeNull();

    $cart->coupon_code = 'valid-coupon';

    $cart->calculate();

    expect($cart->discountBreakdown)->toHaveCount(1);
    expect($cart->discountBreakdown->first()->price->value)->toBe(10);
});

test('can validate fingerprint', function () {
    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();

    $cart = Cart::create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'meta' => [
            'A' => 'B',
            'C' => 'D',
        ],
    ]);

    $variant = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'tier' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($variant),
        'priceable_id' => $variant->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => ProductVariant::class,
        'purchasable_id' => $variant->id,
        'quantity' => 1,
    ]);

    $fingerprint = $cart->fingerprint();

    expect($cart->checkFingerprint($fingerprint))->toBeTrue();

    $cart->update([
        'coupon_code' => 'FOOBAR',
    ]);

    $this->expectException(FingerprintMismatchException::class);

    $cart->checkFingerprint($fingerprint);
});

test('can override shipping calculation', function () {
    $country = Country::factory()->create();

    $taxClass = TaxClass::factory()->create();

    $taxZone = TaxZone::factory()->create();

    TaxZonePostcode::factory()->create([
        'country_id' => $country->id,
        'tax_zone_id' => $taxZone->id,
        'postcode' => 'SHIPP',
    ]);

    $taxRate = TaxRate::factory()->create([
        'tax_zone_id' => $taxZone->id,
    ]);

    TaxRateAmount::factory()->create([
        'tax_rate_id' => $taxRate->id,
        'tax_class_id' => $taxClass->id,
    ]);

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'tier' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasable),
        'priceable_id' => $purchasable->id,
    ]);

    $shippingOption = new ShippingOption(
        name: 'Basic Delivery',
        description: 'Basic Delivery',
        identifier: 'BASDEL',
        price: new DataTypesPrice(500, $cart->currency, 1),
        taxClass: $taxClass
    );

    ShippingManifest::addOption($shippingOption);

    $cart->calculate();

    expect($cart->shippingTotal)->toBeNull();

    $cart->shippingOptionOverride = $shippingOption;

    $cart->calculate();

    expect($cart->shippingSubTotal->value)->toEqual(500);
});

test('can get estimated shipping', function () {
    $country = Country::factory()->create();

    $taxClass = TaxClass::factory()->create();

    $taxZone = TaxZone::factory()->create();

    TaxZonePostcode::factory()->create([
        'country_id' => $country->id,
        'tax_zone_id' => $taxZone->id,
        'postcode' => 'SHIPP',
    ]);

    $taxRate = TaxRate::factory()->create([
        'tax_zone_id' => $taxZone->id,
    ]);

    TaxRateAmount::factory()->create([
        'tax_rate_id' => $taxRate->id,
        'tax_class_id' => $taxClass->id,
    ]);

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'tier' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasable),
        'priceable_id' => $purchasable->id,
    ]);

    $shippingOption = new ShippingOption(
        name: 'Basic Delivery',
        description: 'Basic Delivery',
        identifier: 'BASDEL',
        price: new DataTypesPrice(500, $cart->currency, 1),
        taxClass: $taxClass
    );

    ShippingManifest::addOption($shippingOption);

    $option = $cart->getEstimatedShipping([
        'postcode' => '123',
    ]);

    expect($option)->toBeInstanceOf(ShippingOption::class);
    expect($option->identifier)->toEqual($shippingOption->identifier);

    expect($cart->shippingOptionOverride)->toBeNull();

    $option = $cart->getEstimatedShipping([
        'postcode' => '123',
    ], setOverride: true);

    expect($cart->shippingOptionOverride)->toBeInstanceOf(ShippingOption::class);
    expect($shippingOption->identifier)->toEqual($cart->shippingOptionOverride->identifier);
})->group('foofoo');
