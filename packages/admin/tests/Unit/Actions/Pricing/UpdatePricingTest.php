<?php

namespace Lunar\Hub\Tests\Unit\Actions\Pricing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Hub\Actions\Pricing\UpdatePrices;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\Product;

/**
 * @group hub.actions
 */
class UpdatePricingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function creates_tiered_pricing_models()
    {
        Currency::factory()->create([
            'default' => true,
        ]);

        $product = Product::factory()->hasVariants(1)->create();
        $variant = $product->variants->first();

        $currencies = Currency::factory(5)->create();

        $pricing = [];

        foreach ($currencies as $currency) {
            $pricing[$currency->code] = [
                'price' => 1.99,
                'currency_id' => $currencies->first()->id,
                'tier' => 1,
            ];
        }

        app(UpdatePrices::class)->execute($variant, collect($pricing));

        foreach ($pricing as $price) {
            $currency = Currency::find($price['currency_id']);
            $this->assertDatabaseHas((new Price())->getTable(), [
                'currency_id' => $price['currency_id'],
                'price' => $price['price'] * $currency->factor,
                'priceable_type' => get_class($variant),
                'priceable_id' => $variant->id,
                'tier' => 1,
            ]);
        }
    }
}
