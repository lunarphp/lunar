<?php

namespace Lunar\Tests\Unit\DiscountTypes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DiscountTypes\AmountOff;
use Lunar\Models\Brand;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Discount;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Stubs\User;
use Lunar\Tests\TestCase;

/**
 * @group lunar.discounts
 * @group lunar.discounts.discounts
 */
class AmountOffTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Currency::factory()->create([
            'code' => 'GBP',
            'decimal_places' => 2,
        ]);

        Channel::factory()->create([
            'default' => true,
        ]);

        CustomerGroup::factory()->create([
            'default' => true,
        ]);
    }

    /** @test */
    public function will_only_apply_to_lines_with_correct_brand()
    {
        $customerGroup = CustomerGroup::getDefault();

        $channel = Channel::getDefault();

        $currency = Currency::getDefault();

        $cart = Cart::factory()->create([
            'channel_id' => $channel->id,
            'currency_id' => $currency->id,
            'coupon_code' => '10OFF',
        ]);

        $brandA = Brand::factory()->create([
            'name' => 'Brand A',
        ]);

        $brandB = Brand::factory()->create([
            'name' => 'Brand B',
        ]);

        $productA = Product::factory()->create([
            'brand_id' => $brandA->id,
        ]);

        $productB = Product::factory()->create([
            'brand_id' => $brandB->id,
        ]);

        $purchasableA = ProductVariant::factory()->create([
            'product_id' => $productA->id,
        ]);
        $purchasableB = ProductVariant::factory()->create([
            'product_id' => $productB->id,
        ]);

        Price::factory()->create([
            'price' => 1000, // £10
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 1,
        ]);

        Price::factory()->create([
            'price' => 1000, // £10
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableB),
            'priceable_id' => $purchasableB->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableB),
            'purchasable_id' => $purchasableB->id,
            'quantity' => 1,
        ]);

        $discount = Discount::factory()->create([
            'type' => AmountOff::class,
            'name' => 'Test Coupon',
            'coupon' => '10OFF',
            'data' => [
                'fixed_value' => false,
                'percentage' => 10,
            ],
        ]);

        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now()->subHour(),
            ],
        ]);

        $discount->brands()->sync([$brandA->id]);

        $cart = $cart->calculate();

        /**
         * Cart has two lines.
         * 1 x $10 / 10% off $9 / 20% tax = $1.8 / Total = 10.80
         * 1 x $10 / 0% off $10 / 20% tax = $2 / Total = 12
         * Cart total = $22.80
         */
        $this->assertEquals(100, $cart->discountTotal->value);
        $this->assertEquals(2280, $cart->total->value);
    }

    /** @test */
    public function will_only_apply_to_lines_with_correct_product()
    {
        $customerGroup = CustomerGroup::getDefault();

        $channel = Channel::getDefault();

        $currency = Currency::getDefault();

        $cart = Cart::factory()->create([
            'channel_id' => $channel->id,
            'currency_id' => $currency->id,
            'coupon_code' => '10OFF',
        ]);

        $brandA = Brand::factory()->create([
            'name' => 'Brand A',
        ]);

        $productA = Product::factory()->create([
            'brand_id' => $brandA->id,
        ]);

        $productB = Product::factory()->create([
            'brand_id' => $brandA->id,
        ]);

        $purchasableA = ProductVariant::factory()->create([
            'product_id' => $productA->id,
        ]);
        $purchasableB = ProductVariant::factory()->create([
            'product_id' => $productB->id,
        ]);

        Price::factory()->create([
            'price' => 1000, // £10
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 1,
        ]);

        Price::factory()->create([
            'price' => 1000, // £10
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableB),
            'priceable_id' => $purchasableB->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableB),
            'purchasable_id' => $purchasableB->id,
            'quantity' => 1,
        ]);

        $discount = Discount::factory()->create([
            'type' => AmountOff::class,
            'name' => 'Test Coupon',
            'coupon' => '10OFF',
            'data' => [
                'fixed_value' => false,
                'percentage' => 10,
            ],
        ]);

        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now()->subHour(),
            ],
        ]);

        $discount->purchasableLimitations()->create([
            'discount_id' => $discount->id,
            'type' => 'limitation',
            'purchasable_type' => Product::class,
            'purchasable_id' => $productA->id,
        ]);

        $cart = $cart->calculate();

        $this->assertEquals(100, $cart->discountTotal->value);
        $this->assertEquals(2280, $cart->total->value);
    }

    /**
     * @test
     */
    public function can_apply_fixed_amount_discount()
    {
        $currency = Currency::getDefault();

        $customerGroup = CustomerGroup::getDefault();

        $channel = Channel::getDefault();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
            'coupon_code' => '10OFF',
        ]);

        $purchasableA = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 1000, // £10
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 2,
        ]);

        $discount = Discount::factory()->create([
            'type' => AmountOff::class,
            'name' => 'Test Coupon',
            'coupon' => '10OFF',
            'data' => [
                'fixed_value' => true,
                'fixed_values' => [
                    'GBP' => 10.5,
                ],
            ],
        ]);

        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now()->subHour(),
            ],
        ]);

        $cart = $cart->calculate();

        $this->assertEquals(1050, $cart->discountTotal->value);
        $this->assertEquals(1140, $cart->total->value);
        $this->assertEquals(190, $cart->taxTotal->value);
        $this->assertCount(1, $cart->discounts);
    }

    /**
     * @test
     */
    public function fixed_amount_discount_distributes_across_cart_lines()
    {
        $currency = Currency::getDefault();

        $customerGroup = CustomerGroup::getDefault();

        $channel = Channel::getDefault();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
            'coupon_code' => '10OFF',
        ]);

        $purchasableA = ProductVariant::factory()->create();
        $purchasableB = ProductVariant::factory()->create();
        $purchasableC = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 1000, // £10
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        Price::factory()->create([
            'price' => 1000, // £10
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableB),
            'priceable_id' => $purchasableB->id,
        ]);

        Price::factory()->create([
            'price' => 1000, // £10
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableC),
            'priceable_id' => $purchasableC->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 1,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableB->id,
            'quantity' => 1,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableC->id,
            'quantity' => 1,
        ]);

        $discount = Discount::factory()->create([
            'type' => AmountOff::class,
            'name' => 'Test Coupon',
            'coupon' => '10OFF',
            'data' => [
                'fixed_value' => true,
                'fixed_values' => [
                    'GBP' => 10,
                ],
            ],
        ]);

        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now()->subHour(),
            ],
        ]);

        $cart = $cart->calculate();

        $firstLine = $cart->lines->first();
        $secondLine = $cart->lines->skip(1)->first();
        $lastLine = $cart->lines->last();

        $this->assertEquals(334, $firstLine->discountTotal->value);
        $this->assertEquals(333, $secondLine->discountTotal->value);
        $this->assertEquals(333, $lastLine->discountTotal->value);
    }

    /** <@test */
    public function can_apply_percentage_discount()
    {
        $customerGroup = CustomerGroup::getDefault();

        $channel = Channel::getDefault();

        $currency = Currency::getDefault();

        $cart = Cart::factory()->create([
            'channel_id' => $channel->id,
            'currency_id' => $currency->id,
            'coupon_code' => '10PERCENTOFF',
        ]);

        $purchasable = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 1000,
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

        $discount = Discount::factory()->create([
            'type' => AmountOff::class,
            'name' => 'Test Coupon',
            'coupon' => '10PERCENTOFF',
            'data' => [
                'percentage' => 10,
                'fixed_value' => false,
            ],
        ]);

        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now()->subHour(),
            ],
        ]);

        $this->assertNull($cart->total);
        $this->assertNull($cart->taxTotal);
        $this->assertNull($cart->subTotal);

        $cart = $cart->calculate();

        $this->assertEquals(100, $cart->discountTotal->value);
        $this->assertEquals(180, $cart->taxTotal->value);
        $this->assertEquals(1080, $cart->total->value);

        $cart->lines()->delete();

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id' => $purchasable->id,
            'quantity' => 2,
        ]);

        $cart = $cart->refresh()->calculate();

        $this->assertEquals(200, $cart->discountTotal->value);
        $this->assertEquals(360, $cart->taxTotal->value);
        $this->assertEquals(2160, $cart->total->value);
    }

    /**
     * @test
     */
    public function can_apply_discount_without_coupon_code()
    {
        $currency = Currency::getDefault();

        $customerGroup = CustomerGroup::getDefault();

        $channel = Channel::getDefault();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
        ]);

        $purchasableA = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 1000, // £10
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 2,
        ]);

        $discount = Discount::factory()->create([
            'type' => AmountOff::class,
            'name' => 'Test Coupon',
            'data' => [
                'fixed_value' => true,
                'fixed_values' => [
                    'GBP' => 10,
                ],
            ],
        ]);

        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now()->subHour(),
            ],
        ]);

        $cart = $cart->calculate();

        $this->assertEquals(1000, $cart->discountTotal->value);
        $this->assertEquals(1200, $cart->total->value);
        $this->assertEquals(200, $cart->taxTotal->value);
        $this->assertCount(1, $cart->discounts);
    }

    /**
     * @test
     */
    public function cannot_apply_discount_coupon_without_coupon_code()
    {
        $currency = Currency::getDefault();

        $customerGroup = CustomerGroup::getDefault();

        $channel = Channel::getDefault();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
        ]);

        $purchasableA = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 1000, // £10
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 2,
        ]);

        $discount = Discount::factory()->create([
            'type' => AmountOff::class,
            'name' => 'Test Coupon',
            'coupon' => 'OFF10',
            'data' => [
                'fixed_value' => true,
                'fixed_values' => [
                    'GBP' => 10,
                ],
            ],
        ]);

        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now()->subHour(),
            ],
        ]);

        $cart = $cart->calculate();

        $this->assertEquals(0, $cart->discountTotal->value);
        $this->assertEquals(2400, $cart->total->value);
        $this->assertEquals(400, $cart->taxTotal->value);
        $this->assertNull($cart->discounts);
    }

    /**
     * @test
     */
    public function can_apply_discount_with_max_uses()
    {
        $currency = Currency::getDefault();

        $customerGroup = CustomerGroup::getDefault();

        $channel = Channel::getDefault();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
        ]);

        $purchasableA = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 1000, // £10
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 2,
        ]);

        $discount = Discount::factory()->create([
            'type' => AmountOff::class,
            'name' => 'Test Coupon',
            'uses' => 2,
            'max_uses' => 10,
            'data' => [
                'fixed_value' => true,
                'fixed_values' => [
                    'GBP' => 10,
                ],
            ],
        ]);

        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now()->subHour(),
            ],
        ]);

        $cart = $cart->calculate();

        $this->assertEquals(1000, $cart->discountTotal->value);
        $this->assertEquals(1200, $cart->total->value);
        $this->assertEquals(200, $cart->taxTotal->value);
        $this->assertCount(1, $cart->discounts);
    }

    /**
     * @test
     */
    public function cannot_apply_discount_with_max_uses()
    {
        $currency = Currency::getDefault();

        $customerGroup = CustomerGroup::getDefault();

        $channel = Channel::getDefault();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
        ]);

        $purchasableA = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 1000, // £10
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 2,
        ]);

        $discount = Discount::factory()->create([
            'type' => AmountOff::class,
            'name' => 'Test Coupon',
            'uses' => 10,
            'max_uses' => 10,
            'data' => [
                'fixed_value' => true,
                'fixed_values' => [
                    'GBP' => 10,
                ],
            ],
        ]);

        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now()->subHour(),
            ],
        ]);

        $cart = $cart->calculate();

        $this->assertEquals(0, $cart->discountTotal->value);
        $this->assertEquals(2400, $cart->total->value);
        $this->assertEquals(2000, $cart->subTotal->value);
    }

    /**
     * @test
     *
     * @group thisone
     */
    public function can_apply_discount_with_min_spend()
    {
        $currency = Currency::getDefault();

        $customerGroup = CustomerGroup::getDefault();

        $channel = Channel::getDefault();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
        ]);

        $purchasableA = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 1000, // £10
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 10,
        ]);

        $discount = Discount::factory()->create([
            'type' => AmountOff::class,
            'name' => 'Test Coupon',
            'uses' => 2,
            'max_uses' => 10,
            'data' => [
                'fixed_value' => true,
                'fixed_values' => [
                    'GBP' => 10,
                ],
                'min_prices' => [
                    'GBP' => 50,
                ],
            ],
        ]);

        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now()->subHour(),
            ],
        ]);

        $cart = $cart->calculate();

        $this->assertEquals(1000, $cart->discountTotal->value);
        $this->assertEquals(9000, $cart->subTotal->value);
        $this->assertEquals(10800, $cart->total->value);
        $this->assertEquals(1800, $cart->taxTotal->value);
        $this->assertCount(1, $cart->discounts);
    }

    /**
     * @test
     */
    public function cannot_apply_discount_with_min_spend()
    {
        $currency = Currency::getDefault();

        $customerGroup = CustomerGroup::getDefault();

        $channel = Channel::getDefault();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
        ]);

        $purchasableA = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 1000, // £10
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 2,
        ]);

        $discount = Discount::factory()->create([
            'type' => AmountOff::class,
            'name' => 'Test Coupon',
            'uses' => 2,
            'max_uses' => 10,
            'data' => [
                'fixed_value' => true,
                'fixed_values' => [
                    'GBP' => 10,
                ],
                'min_prices' => [
                    'GBP' => 50,
                ],
            ],
        ]);

        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now()->subHour(),
            ],
        ]);

        $cart = $cart->calculate();

        $this->assertEquals(0, $cart->discountTotal->value);
        $this->assertEquals(2000, $cart->subTotal->value);
        $this->assertEquals(2400, $cart->total->value);
        $this->assertEquals(400, $cart->taxTotal->value);
        $this->assertNull($cart->discounts);
    }

    /**
     * @test
     */
    public function can_apply_discount_with_conditions()
    {
        $currency = Currency::getDefault();

        $customerGroup = CustomerGroup::getDefault();

        $channel = Channel::getDefault();

        $purchasableA = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 1000, // £10
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
            'coupon_code' => 'OFF10',
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 10,
        ]);

        $discount = Discount::factory()->create([
            'type' => AmountOff::class,
            'name' => 'Test Coupon',
            'coupon' => 'OFF10',
            'uses' => 2,
            'max_uses' => 10,
            'data' => [
                'fixed_value' => true,
                'fixed_values' => [
                    'GBP' => 10,
                ],
                'min_prices' => [
                    'GBP' => 50,
                ],
            ],
        ]);

        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now()->subHour(),
            ],
        ]);

        $cart = $cart->calculate();

        $this->assertEquals(1000, $cart->discountTotal->value);
        $this->assertEquals(9000, $cart->subTotal->value);
        $this->assertEquals(10800, $cart->total->value);
        $this->assertEquals(1800, $cart->taxTotal->value);
        $this->assertCount(1, $cart->discounts);
    }

    /**
     * @test
     */
    public function can_apply_discount_with_max_user_uses()
    {
        $currency = Currency::getDefault();

        $customerGroup = CustomerGroup::getDefault();

        $channel = Channel::getDefault();

        $user = User::factory()->create();

        $this->actingAs($user);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
            'user_id' => $user->getKey(),
        ]);

        $purchasableA = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 1000, // £10
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 2,
        ]);

        $discount = Discount::factory()->create([
            'type' => AmountOff::class,
            'name' => 'Test Coupon',
            'uses' => 0,
            'max_uses' => 10,
            'max_uses_per_user' => 2,
            'data' => [
                'fixed_value' => true,
                'fixed_values' => [
                    'GBP' => 10,
                ],
            ],
        ]);

        $discount->users()->sync([
            $user->id,
        ]);

        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now()->subHour(),
            ],
        ]);

        $cart = $cart->calculate();

        $this->assertEquals(1000, $cart->discountTotal->value);
        $this->assertEquals(1200, $cart->total->value);
        $this->assertEquals(1000, $cart->subTotal->value);
    }

    /**
     * @test
     */
    public function cannot_apply_discount_with_max_user_uses()
    {
        $currency = Currency::getDefault();

        $customerGroup = CustomerGroup::getDefault();

        $channel = Channel::getDefault();

        $user = User::factory()->create();

        $this->actingAs($user);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
        ]);

        $purchasableA = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 1000, // £10
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 2,
        ]);

        $discount = Discount::factory()->create([
            'type' => AmountOff::class,
            'name' => 'Test Coupon',
            'uses' => 0,
            'max_uses' => 10,
            'max_uses_per_user' => 1,
            'data' => [
                'fixed_value' => true,
                'fixed_values' => [
                    'GBP' => 10,
                ],
            ],
        ]);

        $discount->users()->sync([
            $user->id,
        ]);

        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now()->subHour(),
            ],
        ]);

        $cart = $cart->calculate();

        $this->assertEquals(0, $cart->discountTotal->value);
        $this->assertEquals(2400, $cart->total->value);
        $this->assertEquals(2000, $cart->subTotal->value);
    }
}
