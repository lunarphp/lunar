<?php

namespace Lunar\Tests\Unit\DiscountTypes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DiscountTypes\AmountOff;
use Lunar\Facades\CartSession;
use Lunar\Models\Brand;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
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
    
    /** @test */
    public function will_only_apply_to_lines_with_correct_product_variant()
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
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => $purchasableA->id,
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
    public function can_only_same_discount_to_line_once()
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

        $cart = $cart->calculate()->calculate();

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
            'coupon' => null,
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
        $this->assertTrue($cart->discounts->isEmpty());
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

    /** @test */
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
        $this->assertEquals(10000, $cart->subTotal->value);
        $this->assertEquals(9000, $cart->subTotalDiscounted->value);
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
        $this->assertTrue($cart->discounts->isEmpty());
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
        $this->assertEquals(10000, $cart->subTotal->value);
        $this->assertEquals(9000, $cart->subTotalDiscounted->value);
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
        $customer = Customer::factory()->create();
        $customer->customerGroups()->attach($customerGroup);

        $user->customers()->attach($customer);

        $this->actingAs($user);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
        ]);

        $cart->user()->associate($user);

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
        $this->assertEquals(2000, $cart->subTotal->value);
        $this->assertEquals(1000, $cart->subTotalDiscounted->value);
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
        $customer = Customer::factory()->create();
        $customer->customerGroups()->attach($customerGroup);

        $user->customers()->attach($customer);

        $this->actingAs($user);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
        ]);

        $cart->user()->associate($user);

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
    
    /**
     * @test
     */
    public function fixed_amount_discount_distributes_across_cart_lines_with_different_values()
    {
        $currency = Currency::getDefault();

        $customerGroup = CustomerGroup::getDefault();

        $channel = Channel::getDefault();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
            'coupon_code' => 'DISCOUNTOFF',
        ]);

        $purchasableA = ProductVariant::factory()->create();
        $purchasableB = ProductVariant::factory()->create();
        $purchasableC = ProductVariant::factory()->create();
        $purchasableD = ProductVariant::factory()->create();
        $purchasableE = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 15, // £0.15
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        Price::factory()->create([
            'price' => 20, // £0.20
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableB),
            'priceable_id' => $purchasableB->id,
        ]);

        Price::factory()->create([
            'price' => 40, // £0.40
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableC),
            'priceable_id' => $purchasableC->id,
        ]);

        Price::factory()->create([
            'price' => 40, // £0.40
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableD),
            'priceable_id' => $purchasableD->id,
        ]);

        Price::factory()->create([
            'price' => 40, // £0.40
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableE),
            'priceable_id' => $purchasableE->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 10,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableB),
            'purchasable_id' => $purchasableB->id,
            'quantity' => 10,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableC),
            'purchasable_id' => $purchasableC->id,
            'quantity' => 10,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableD),
            'purchasable_id' => $purchasableD->id,
            'quantity' => 10,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableE),
            'purchasable_id' => $purchasableE->id,
            'quantity' => 9,
        ]);

        $discount = Discount::factory()->create([
            'type' => AmountOff::class,
            'name' => 'Test Coupon',
            'coupon' => 'DISCOUNTOFF',
            'data' => [
                'fixed_value' => true,
                'fixed_values' => [
                    'GBP' => 15.00,
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
        $thirdLine = $cart->lines->skip(2)->first();
        $fourthLine = $cart->lines->skip(3)->first();
        $lastLine = $cart->lines->last();

        $this->assertGreaterThanOrEqual(0, $firstLine->subTotalDiscounted->value);
        $this->assertGreaterThanOrEqual(0, $secondLine->subTotalDiscounted->value);
        $this->assertGreaterThanOrEqual(0, $thirdLine->subTotalDiscounted->value);
        $this->assertGreaterThanOrEqual(0, $fourthLine->subTotalDiscounted->value);
        $this->assertGreaterThanOrEqual(0, $lastLine->subTotalDiscounted->value);

        $this->assertEquals(150, $firstLine->discountTotal->value);
        $this->assertEquals(199, $secondLine->discountTotal->value);
        $this->assertEquals(397, $thirdLine->discountTotal->value);
        $this->assertEquals(397, $fourthLine->discountTotal->value);
        $this->assertEquals(357, $lastLine->discountTotal->value);
        $this->assertEquals(1500, $cart->discountTotal->value);
    }

    /**
     * @test
     */
    public function can_apply_discount_dynamically()
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

        // Calculate method called for the first time
        CartSession::use($cart)->calculate();

        // Update cart with coupon code
        $cart->update([
            'coupon_code' => '10OFF',
        ]);

        // Get current cart which runs the calculate method for the second time
        $cart = CartSession::current();

        // Calculate method called for the third time
        $cart = $cart->calculate();

        $this->assertEquals(1050, $cart->discountTotal->value);
        $this->assertEquals(1140, $cart->total->value);
        $this->assertEquals(190, $cart->taxTotal->value);
        $this->assertCount(1, $cart->discounts);
    }
}
