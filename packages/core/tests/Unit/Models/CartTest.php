<?php

namespace Lunar\Tests\Unit\Models;

use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
use Lunar\Tests\Stubs\User as StubUser;
use Lunar\Tests\TestCase;
use NumberFormatter;

/**
 * @group lunar.carts
 */
class CartTest extends TestCase
{
    use RefreshDatabase;

    private function setAuthUserConfig()
    {
        Config::set('auth.providers.users.model', 'Lunar\Tests\Stubs\User');
    }

    /** @test */
    public function can_make_a_cart()
    {
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

        $this->assertCount(1, $cart->lines()->get());
    }

    /** @test */
    public function can_save_coupon_code()
    {
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

        $this->assertNull($cart->coupon_code);

        $cart->coupon_code = 'valid-coupon';

        Discounts::apply($cart);

        $cart->saveQuietly();

        $this->assertEquals('valid-coupon', $cart->refresh()->coupon_code);
    }

    /** @test */
    public function can_associate_cart_with_user_with_no_customer_attached()
    {
        $this->setAuthUserConfig();

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
    }

    /** @test */
    public function can_associate_cart_with_customer()
    {
        $this->setAuthUserConfig();

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
    }

    /** @test */
    public function ensure_associate_user_belongs_to_customer()
    {
        $this->setAuthUserConfig();

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

        $this->assertTrue($checked);

        $this->assertDatabaseMissing((new Cart)->getTable(), $cartData);

        $user->customers()->attach($customer);

        $cart->associate($user);

        $this->assertDatabaseHas((new Cart)->getTable(), $cartData);
    }

    /** @test */
    public function ensure_associate_customer_belongs_to_user()
    {
        $this->setAuthUserConfig();

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

        $this->assertTrue($checked);

        $this->assertDatabaseMissing((new Cart)->getTable(), $cartData);

        $user->customers()->attach($customer);

        $cart->setCustomer($customer);

        $this->assertDatabaseHas((new Cart)->getTable(), $cartData);
    }

    /** @test */
    public function will_not_retrieve_user_cart_if_order_is_placed()
    {
        $this->setAuthUserConfig();

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

        $this->assertNull(
            Cart::whereUserId($user->getKey())->active()->first()
        );
    }

    /** @test */
    public function can_get_cart_draft_order()
    {
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

        $this->assertEquals($draftOrder->id, $cart->draftOrder->id);

        $draftOrder->delete();

        $this->assertNull($cart->draftOrder()->first());
    }

    /** @test */
    public function can_get_cart_draft_order_by_id()
    {
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

        $this->assertEquals($draftOrder->id, $cart->draftOrder->id);
        $this->assertEquals($draftOrderTwo->id, $cart->draftOrder($draftOrderTwo->id)->first()->id);
    }

    /** @test */
    public function can_check_for_completed_order()
    {
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

        $this->assertFalse($cart->hasCompletedOrders());

        $order->update([
            'placed_at' => now(),
        ]);

        $this->assertTrue($cart->hasCompletedOrders());
    }

    /** @test */
    public function can_retrieve_active_cart()
    {
        $this->setAuthUserConfig();

        $currency = Currency::factory()->create();
        $channel = Channel::factory()->create();
        $user = StubUser::factory()->create();

        $cart = Cart::create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
            'user_id' => $user->getKey(),
        ]);

        $this->assertEquals(
            $cart->id,
            Cart::whereUserId($user->getKey())->active()->first()->id
        );
    }

    /** @test */
    public function can_associate_cart_with_user_with_customer_attached()
    {
        $this->setAuthUserConfig();

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
    }

    /** @test */
    public function can_calculate_the_cart()
    {
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

        $this->assertEquals(100, $cart->lines[0]->unitPrice->value);
        $this->assertEquals('$1.00', $cart->lines[0]->unitPrice->unitFormatted(null, NumberFormatter::CURRENCY, 6));
        $this->assertEquals('$1.000000', $cart->lines[0]->unitPrice->unitFormatted(null, NumberFormatter::CURRENCY, 6, false));
        $this->assertEquals(158, $cart->lines[1]->unitPrice->value);
        $this->assertEquals(0.0158, $cart->lines[1]->unitPrice->unitDecimal(false));
        $this->assertEquals('$0.0158', $cart->lines[1]->unitPrice->unitFormatted(null, NumberFormatter::CURRENCY, 6));
        $this->assertEquals('$0.015800', $cart->lines[1]->unitPrice->unitFormatted(null, NumberFormatter::CURRENCY, 6, false));
        $this->assertEquals(103, $cart->subTotal->value);
        $this->assertEquals(124, $cart->total->value);
        $this->assertCount(2, $cart->taxBreakdown->amounts);
    }

    /** @test */
    public function can_calculate_the_cart_inc_vat()
    {
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

        $this->assertEquals(100, $cart->lines[0]->unitPrice->value);
        $this->assertEquals('$1.00', $cart->lines[0]->unitPrice->unitFormatted(null, NumberFormatter::CURRENCY, 6));
        $this->assertEquals('$1.000000', $cart->lines[0]->unitPrice->unitFormatted(null, NumberFormatter::CURRENCY, 6, false));
        $this->assertEquals(158, $cart->lines[1]->unitPrice->value);
        $this->assertEquals(0.0158, $cart->lines[1]->unitPrice->unitDecimal(false));
        $this->assertEquals('$0.0158', $cart->lines[1]->unitPrice->unitFormatted(null, NumberFormatter::CURRENCY, 6));
        $this->assertEquals('$0.015800', $cart->lines[1]->unitPrice->unitFormatted(null, NumberFormatter::CURRENCY, 6, false));
        $this->assertEquals(103, $cart->subTotal->value);
        $this->assertEquals(103, $cart->total->value);
        $this->assertCount(2, $cart->taxBreakdown->amounts);
    }

    /**
     * @test
     */
    public function can_add_cart_lines()
    {
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

        $this->assertCount(0, $cart->lines);

        $cart->add($purchasable, 1);

        $this->assertCount(1, $cart->lines);
    }

    /**
     * @test
     */
    public function can_remove_cart_lines()
    {
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

        $this->assertCount(0, $cart->lines);

        $cart->add($purchasable, 1);

        $this->assertCount(1, $cart->lines);

        $cart->remove($cart->lines->first()->id);

        $this->assertCount(0, $cart->lines);
    }

    /** @test */
    public function cannot_add_zero_quantity_line()
    {
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

        $this->assertCount(0, $cart->lines);

        $this->expectException(CartException::class);

        $cart->add($purchasable, 0);
    }

    /** @test */
    public function can_update_existing_cart_line()
    {
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

        $this->assertCount(0, $cart->lines);

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
    }

    /** @test */
    public function can_calculate_shipping()
    {
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

        $this->assertCount(0, $cart->lines);

        $cart->add($purchasable, 1);

        $cart->calculate();

        $this->assertEquals(100, $cart->subTotal->value);
        $this->assertEquals(500, $cart->shippingSubTotal->value);
        $this->assertEquals(600, $cart->shippingTotal->value);
        $this->assertEquals(720, $cart->total->value);

        Config::set('lunar.pricing.stored_inclusive_of_tax', true);

        $cart->calculate();

        $this->assertEquals(500, $cart->shippingTotal->value);
        $this->assertEquals(600, $cart->total->value);
    }

    /** @test */
    public function can_create_a_discount_breakdown()
    {
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

        $this->assertNull($cart->coupon_code);

        $cart->coupon_code = 'valid-coupon';

        $cart->calculate();

        $this->assertCount(1, $cart->discountBreakdown);
        $this->assertSame(10, $cart->discountBreakdown->first()->price->value);
    }

    /** @test */
    public function can_validate_fingerprint()
    {
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

        $this->assertTrue(
            $cart->checkFingerprint($fingerprint)
        );

        $cart->update([
            'coupon_code' => 'FOOBAR',
        ]);

        $this->expectException(FingerprintMismatchException::class);

        $cart->checkFingerprint($fingerprint);
    }

    /**
     * @test
     */
    public function can_override_shipping_calculation()
    {
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

        $this->assertNull($cart->shippingTotal);

        $cart->shippingOptionOverride = $shippingOption;

        $cart->calculate();

        $this->assertEquals(500, $cart->shippingSubTotal->value);
    }

    /**
     * @test
     * @group foofoo
     */
    public function can_get_estimated_shipping()
    {
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

        $this->assertInstanceOf(ShippingOption::class, $option);
        $this->assertEquals($shippingOption->identifier, $option->identifier);

        $this->assertNull($cart->shippingOptionOverride);

        $option = $cart->getEstimatedShipping([
            'postcode' => '123',
        ], setOverride: true);

        $this->assertInstanceOf(ShippingOption::class, $cart->shippingOptionOverride);
        $this->assertEquals($cart->shippingOptionOverride->identifier, $shippingOption->identifier);
    }
}
