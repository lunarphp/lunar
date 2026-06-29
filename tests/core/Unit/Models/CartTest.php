<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Core\DataObjects\PriceValue as DataTypesPrice;
use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\DiscountTypes\AmountOff;
use Lunar\Core\Exceptions\Carts\CartException;
use Lunar\Core\Exceptions\FingerprintMismatchException;
use Lunar\Core\Facades\Discounts;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Managers\CartSessionManager;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\TaxRate;
use Lunar\Core\Models\TaxRateAmount;
use Lunar\Core\Models\TaxZone;
use Lunar\Core\Models\TaxZonePostcode;
use Lunar\Tests\Core\Stubs\User as StubUser;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('carts', 'cross-db');

use function Pest\Laravel\assertDatabaseCount;

uses(RefreshDatabase::class);

// function setAuthUserConfig()
// {
//    Config::set('auth.providers.users.model', 'Lunar\Tests\Stubs\User');
// }

test('can make a cart', function () {
    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();

    $cart = Cart::create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'meta' => ['foo' => 'bar'],
    ]);

    $this->assertDatabaseHas((new Cart)->getTable(), [
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    expect((array) $cart->fresh()->meta)->toEqual(['foo' => 'bar']);

    $variant = ProductVariant::factory()->create();

    $cart->lines()->create([
        'purchasable_type' => $variant->getMorphClass(),
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

    expect($cart->refresh()->coupon_code)->toEqual('VALID-COUPON');
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

    $this->assertDatabaseHas((new Cart)->getTable(), [
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
        'fingerprint' => $cart->calculate()->fingerprint(),
        'total' => $cart->calculate()->total->value,
        'placed_at' => null,
    ]);

    expect($cart->currentDraftOrder()->id)->toEqual($draftOrder->id);

    $draftOrder->delete();

    expect($cart->currentDraftOrder())->toBeNull();
})->group('nooo');

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
        'fingerprint' => $cart->calculate()->fingerprint(),
        'total' => $cart->calculate()->total->value,
        'placed_at' => null,
    ]);

    $draftOrderTwo = Order::factory()->create([
        'cart_id' => $cart->id,
        'fingerprint' => $cart->calculate()->fingerprint(),
        'total' => $cart->calculate()->total->value,
        'placed_at' => null,
    ]);

    expect($cart->currentDraftOrder()->id)->toEqual($draftOrder->id);
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

    $this->assertDatabaseHas((new Cart)->getTable(), [
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

    // Add product with unit qty
    $purchasable = ProductVariant::factory()
        ->state([
            'unit_quantity' => 100,
        ])
        ->create();

    Price::factory()->create([
        'price' => 158,
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

    // Set user
    $this->actingAs(
        StubUser::factory()->create()
    );

    expect($cart->isCalculated())->toEqual(false);

    $cart->calculate();

    expect($cart->isCalculated())->toEqual(true);
    expect($cart->lines[0]->unitPrice->value)->toEqual(100);
    expect($cart->lines[1]->unitPrice->value)->toEqual(158);
    expect($cart->lines[1]->unitPriceInclTax->value)->toEqual(190);
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

    // Add product with unit qty
    $purchasable = ProductVariant::factory()
        ->state([
            'unit_quantity' => 100,
        ])
        ->create();

    Price::factory()->create([
        'price' => 158,
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

    // Set user
    $this->actingAs(
        StubUser::factory()->create()
    );

    $cart->calculate();

    expect($cart->lines[0]->unitPrice->value)->toEqual(100);
    expect($cart->lines[1]->unitPrice->value)->toEqual(158);
    expect($cart->subTotal->value)->toEqual(103);
    expect($cart->total->value)->toEqual(103);
    expect($cart->taxBreakdown->amounts)->toHaveCount(2);
});

test('can add cart lines', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->inStock(1)->create();

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
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

    $purchasable = ProductVariant::factory()->inStock(1)->create();

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
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
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
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

    $purchasable = ProductVariant::factory()->inStock(1)->create();

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    expect($cart->lines)->toHaveCount(0);

    $cart->add($purchasable, 1);

    $cartLine = $cart->refresh()->lines->first();

    $this->assertDatabaseHas((new CartLine)->getTable(), [
        'quantity' => 1,
        'id' => $cartLine->id,
    ]);

    $cart->updateLine($cartLine->id, 2);

    $this->assertDatabaseHas((new CartLine)->getTable(), [
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

    $purchasable = ProductVariant::factory()->inStock(1)->create();

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
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
    expect($cart->shippingTaxTotal->value)->toEqual(100);
    expect($cart->shippingTotal->value)->toEqual(600);
    expect($cart->total->value)->toEqual(720);

    expect($cart->shippingAddress->shippingSubTotal->value)->toEqual(500);
    expect($cart->shippingAddress->shippingTaxTotal->value)->toEqual(100);
    expect($cart->shippingAddress->shippingTotal->value)->toEqual(600);

    Config::set('lunar.pricing.stored_inclusive_of_tax', true);

    $cart->recalculate();

    expect($cart->subTotal->value)->toEqual(100);
    expect($cart->shippingSubTotal->value)->toEqual(500);
    expect($cart->shippingTotal->value)->toEqual(500);
    expect($cart->total->value)->toEqual(600);

    expect($cart->shippingAddress->shippingSubTotal->value)->toEqual(500);
    expect($cart->shippingAddress->shippingTaxTotal->value)->toEqual(83);
    expect($cart->shippingAddress->shippingTotal->value)->toEqual(500);
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
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $variant->getMorphClass(),
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
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $variant->getMorphClass(),
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
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
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

    $cart->recalculate();

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
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
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
});

test('can get new draft order when cart changes', function () {
    $currency = Currency::factory()
        ->state([
            'code' => 'USD',
        ])
        ->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $taxClass = TaxClass::factory()->create();

    // Add product with unit qty
    $purchasable = ProductVariant::factory()
        ->state([
            'unit_quantity' => 1,
        ])
        ->create();

    Price::factory()->create([
        'price' => 158,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
    ]);

    CartAddress::factory()->create([
        'type' => 'shipping',
        'cart_id' => $cart->id,
    ]);

    $option = new ShippingOption(
        name: 'Basic Delivery',
        description: 'Basic Delivery',
        identifier: 'BASDEL',
        price: new DataTypesPrice(500, $cart->currency, 1),
        taxClass: $taxClass
    );

    ShippingManifest::addOption($option);

    $cart->setShippingOption($option);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 2,
    ]);

    $order = $cart->createOrder();

    assertDatabaseCount(Order::class, 1);

    expect($order->placed_at)
        ->toBeNull()
        ->and($order->fingerprint)
        ->toBe($cart->fingerprint())
        ->and(
            $cart->currentDraftOrder()->id
        )->toBe($order->id);

    $cart->lines()->first()->update([
        'quantity' => 5,
    ]);

    $orderTwo = $cart->calculate()->createOrder();

    assertDatabaseCount(Order::class, 2);

    expect($orderTwo->placed_at)
        ->toBeNull()
        ->and($orderTwo->fingerprint)
        ->toBe($cart->fingerprint())
        ->and(
            $cart->currentDraftOrder()->id
        )->toBe($orderTwo->id);

})->skip('When order is not placed, no new draft order is created even if cart changes.');

test('can get same draft order when cart does not change', function () {
    $currency = Currency::factory()
        ->state([
            'code' => 'USD',
        ])
        ->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $taxClass = TaxClass::factory()->create();

    // Add product with unit qty
    $purchasable = ProductVariant::factory()
        ->state([
            'unit_quantity' => 1,
        ])
        ->create();

    Price::factory()->create([
        'price' => 158,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
    ]);

    CartAddress::factory()->create([
        'type' => 'shipping',
        'cart_id' => $cart->id,
    ]);

    $option = new ShippingOption(
        name: 'Basic Delivery',
        description: 'Basic Delivery',
        identifier: 'BASDEL',
        price: new DataTypesPrice(500, $cart->currency, 1),
        taxClass: $taxClass
    );

    ShippingManifest::addOption($option);

    $cart->setShippingOption($option);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 2,
    ]);

    $order = $cart->createOrder();

    assertDatabaseCount(Order::class, 1);

    expect($order->placed_at)
        ->toBeNull()
        ->and($order->fingerprint)
        ->toBe($cart->fingerprint())
        ->and(
            $cart->currentDraftOrder()->first()->id
        )->toBe($order->id);

    $newOrder = $cart->createOrder();

    assertDatabaseCount(Order::class, 1);

    expect($newOrder->placed_at)
        ->toBeNull()
        ->and($newOrder->fingerprint)
        ->toBe($cart->fingerprint())
        ->and(
            $cart->currentDraftOrder()->id
        )->toBe($newOrder->id);

});

test('cart tax zone override is applied through the full calculation pipeline', function () {
    // Prices are stored ex-tax; tax is added on top during cart calculation.
    Config::set('lunar.pricing.stored_inclusive_of_tax', false);

    $currency = Currency::factory()->state(['code' => 'GBP'])->create();
    $cart = Cart::factory()->create(['currency_id' => $currency->id]);

    $taxClass = TaxClass::factory()->create(['name' => 'Standard', 'default' => true]);

    // Default zone: 0 % – simulates a store where no tax applies for unknown locations.
    $defaultTaxZone = TaxZone::factory()->state(['default' => true])->create();
    $defaultRate = TaxRate::factory()->state(['tax_zone_id' => $defaultTaxZone])->create(['name' => 'Default Rate']);
    TaxRateAmount::factory()->create([
        'tax_class_id' => $taxClass->id,
        'tax_rate_id' => $defaultRate->id,
        'percentage' => 0,
    ]);

    // UAE zone: 20 % – the override set by IP-detection middleware.
    $uaeZone = TaxZone::factory()->state(['default' => false])->create(['name' => 'UAE']);
    $uaeRate = TaxRate::factory()->state(['tax_zone_id' => $uaeZone])->create(['name' => 'UAE VAT']);
    TaxRateAmount::factory()->create([
        'tax_class_id' => $taxClass->id,
        'tax_rate_id' => $uaeRate->id,
        'percentage' => 20,
    ]);

    $purchasable = ProductVariant::factory(['tax_class_id' => $taxClass->id])->create();

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
        'quantity' => 1,
    ]);

    // Default zone (0 %) – the cart-level zone is passed through the full pipeline:
    // CalculateLines publishes the Blink key; CalculateTax forwards it to the driver.
    $cart->setTaxZone($defaultTaxZone)->calculate();
    expect($cart->taxTotal->value)->toEqual(0);
    expect($cart->total->value)->toEqual(1000);

    // Switch to UAE zone (20 %) – the override is correctly picked up.
    $cart->setTaxZone($uaeZone)->recalculate();
    expect($cart->taxTotal->value)->toEqual(200);   // 20 % of 1000
    expect($cart->total->value)->toEqual(1200);

    // Switch back to the default zone – pipeline correctly reverts to 0 %.
    $cart->setTaxZone($defaultTaxZone)->recalculate();
    expect($cart->taxTotal->value)->toEqual(0);
    expect($cart->total->value)->toEqual(1000);
});

test('setShippingAddress clears the tax zone override by default', function () {
    $currency = Currency::factory()->create();
    $cart = Cart::factory()->create(['currency_id' => $currency->id]);

    $taxZone = TaxZone::factory()->state(['default' => false])->create();
    $cart->setTaxZone($taxZone, refresh: false)->save();

    $cart->setShippingAddress(CartAddress::factory()->make()->toArray());

    expect($cart->fresh()->tax_zone_id)->toBeNull();
});

test('setShippingAddress preserves the tax zone override when opted out', function () {
    $currency = Currency::factory()->create();
    $cart = Cart::factory()->create(['currency_id' => $currency->id]);

    $taxZone = TaxZone::factory()->state(['default' => false])->create();
    $cart->setTaxZone($taxZone, refresh: false)->save();

    $cart->setShippingAddress(CartAddress::factory()->make()->toArray(), clearTaxZone: false);

    expect($cart->fresh()->tax_zone_id)->toEqual($taxZone->id);
});

test('active scope correctly filters unmerged carts and isolates users', function () {
    setAuthUserConfig();

    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();

    $userA = StubUser::factory()->create();
    $userB = StubUser::factory()->create();

    $otherUsersCart = Cart::factory()->create([
        'user_id' => $userB->id,
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $expectedCart = Cart::factory()->create([
        'user_id' => $userA->id,
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'merged_id' => null,
    ]);

    $mergedCart = Cart::factory()->create([
        'user_id' => $userA->id,
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'merged_id' => $expectedCart->id,
    ]);

    $cartId = $userA->carts()
        ->unmerged()
        ->active()
        ->latest('id')
        ->value('id');

    expect($cartId)->toBe($expectedCart->id)
        ->and($cartId)->not->toBe($otherUsersCart->id)
        ->and($cartId)->not->toBe($mergedCart->id);
});

test('cart session manager prefers the latest unmerged cart for an authenticated user', function () {
    setAuthUserConfig();

    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();
    $user = StubUser::factory()->create();

    $older = Cart::factory()->create([
        'user_id' => $user->id,
        'merged_id' => null,
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $expectedCart = Cart::factory()->create([
        'user_id' => $user->id,
        'merged_id' => null,
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $mergedCart = Cart::factory()->create([
        'user_id' => $user->id,
        'merged_id' => $expectedCart->id,
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $this->actingAs($user);

    $manager = app(CartSessionManager::class);
    $foundCart = $manager->current();

    expect($foundCart)->not->toBeNull()
        ->and($foundCart->id)->toBe($expectedCart->id)
        ->and($foundCart->id)->not->toBe($older->id)
        ->and($foundCart->id)->not->toBe($mergedCart->id)
        ->and($foundCart->merged_id)->toBeNull();
});

test('a cart produces a storefront context from its stored selections', function () {
    // Defaults must exist first; the cart then points at distinct non-default
    // records, proving context() reads the cart's own selections.
    Channel::factory()->create(['default' => true]);
    Currency::factory()->create(['default' => true]);
    CustomerGroup::factory()->create(['default' => true]);

    $channel = Channel::factory()->create(['default' => false]);
    $currency = Currency::factory()->create(['default' => false]);
    $trade = CustomerGroup::factory()->create(['default' => false]);
    $customer = Customer::factory()->create();
    $customer->customerGroups()->attach($trade);

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'customer_id' => $customer->id,
    ]);

    $context = $cart->context();

    expect($context)->toBeInstanceOf(StorefrontContext::class)
        ->and($context->channel->id)->toBe($channel->id)
        ->and($context->currency->id)->toBe($currency->id)
        ->and($context->customer->id)->toBe($customer->id)
        ->and($context->customerGroups->pluck('id')->all())->toBe([$trade->id]);
});
