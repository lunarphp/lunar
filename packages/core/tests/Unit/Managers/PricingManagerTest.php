<?php

namespace GetCandy\Tests\Unit\Managers;

use GetCandy\Base\DataTransferObjects\PricingResponse;
use GetCandy\Managers\PricingManager;
use GetCandy\Models\Currency;
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
}
