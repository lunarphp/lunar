<?php

namespace GetCandy\Tests\Unit\Managers;

use GetCandy\Base\DataTransferObjects\PricingResponse;
use GetCandy\Managers\PricingManager;
use GetCandy\Models\Currency;
use GetCandy\Models\CustomerGroup;
use GetCandy\Models\Price;
use GetCandy\Models\Product;
use GetCandy\Models\ProductVariant;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.pricing-manager
 */
class PricingManagerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_initialise_the_manager()
    {
        $this->assertInstanceOf(
            PricingManager::class,
            new PricingManager
        );
    }

    /** @test */
    public function can_set_up_available_guest_pricing()
    {
        $manager = new PricingManager;

        $currency = Currency::factory()->create([
            'default' => true,
            'exchange_rate' => 1,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        $base = Price::factory()->create([
            'price' => 100,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $currency->id,
            'tier'           => 1,
        ]);

        Price::factory()->create([
            'price' => 50,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $currency->id,
            'tier'           => 10,
        ]);

        Price::factory()->create([
            'price' => 50,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $currency->id,
            'tier'           => 1,
            'customer_group_id' => CustomerGroup::factory()
        ]);

        $pricing = $manager->for($variant);

        $this->assertInstanceOf(PricingResponse::class, $pricing);
        $this->assertCount(0, $pricing->customerGroupPrices);
        $this->assertCount(1, $pricing->tiered);
        $this->assertEquals($base->id, $pricing->base->id);
        $this->assertEquals($base->id, $pricing->matched->id);
    }

    /**
     * @test
     * @group thisone
     * */
    public function can_fetch_tiered_price()
    {
        $manager = new PricingManager;

        $currency = Currency::factory()->create([
            'default' => true,
            'exchange_rate' => 1,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        $base = Price::factory()->create([
            'price' => 100,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $currency->id,
            'tier'           => 1,
        ]);

        $tiered = Price::factory()->create([
            'price' => 50,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $currency->id,
            'tier'           => 10,
        ]);


        $pricing = $manager->qty(10)->for($variant);

        $this->assertInstanceOf(PricingResponse::class, $pricing);
        $this->assertCount(1, $pricing->tiered);
        $this->assertEquals($base->id, $pricing->base->id);
        $this->assertEquals($$tiered->id, $pricing->matched->id);
    }

    /** @test */
    public function can_get_purchasable_price_with_defaults()
    {
        $manager = new PricingManager;

        $currency = Currency::factory()->create([
            'default' => true,
            'exchange_rate' => 1,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);


        $price = Price::factory()->create([
            'price' => 100,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $currency->id,
            'tier'           => 1,
        ]);

        $pricing = $manager->for($variant);

        $this->assertInstanceOf(PricingResponse::class, $pricing);

        $this->assertEquals($price->id, $pricing->matched->id);
    }

    /** @test */
    public function can_get_price_matched_with_tiers()
    {
        $manager = new PricingManager;

        $currency = Currency::factory()->create([
            'default' => true,
            'exchange_rate' => 1,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);


        Price::factory()->create([
            'price' => 100,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $currency->id,
            'tier'           => 1,
        ]);

        $tieredPrice = Price::factory()->create([
            'price' => 50,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $currency->id,
            'tier'           => 10,
        ]);

        $pricing = $manager->qty(10)->for($variant);

        $this->assertInstanceOf(PricingResponse::class, $pricing);

        $this->assertEquals($tieredPrice->id, $pricing->matched->id);
    }
}
