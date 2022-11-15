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

    /** @test */
    public function correctly_saves_pricing()
    {
        Currency::factory()->create([
            'default' => true,
        ]);

        $product = Product::factory()->hasVariants(1)->create();
        $variant = $product->variants->first();

        $currencies = Currency::factory(5)->create();

        $pricing = [];

        $prices = [
            1899 => 18.99,
            999 => 9.99,
            1459 => 14.59,
            1099 => 10.99,
            1098 => 10.98,
        ];

        foreach ($prices as $expected => $value) {
            $pricing = [];
            foreach ($currencies as $currency) {
                $pricing[$currency->code] = [
                    'price' => $value,
                    'currency_id' => $currencies->first()->id,
                    'tier' => 1,
                ];
            }

            app(UpdatePrices::class)->execute($variant, collect($pricing));

            $this->assertDatabaseHas((new Price())->getTable(), [
                'price' => $expected,
                'priceable_type' => get_class($variant),
                'priceable_id' => $variant->id,
                'tier' => 1,
            ]);
        }
    }
}
