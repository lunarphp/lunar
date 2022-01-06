<?php

namespace GetCandy\Hub\Tests\Unit\Actions\Pricing;

use GetCandy\Hub\Actions\Pricing\UpdateTieredPricing;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Currency;
use GetCandy\Models\CustomerGroup;
use GetCandy\Models\Price;
use GetCandy\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group hub.actions
 */
class UpdateTieredPricingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function creates_tiered_pricing_models()
    {
        $product = Product::factory()->hasVariants(1)->create();
        $variant = $product->variants->first();

        $customerGroup = CustomerGroup::factory()->create();
        $currencies = Currency::factory(2)->create();

        $tiers = collect([
            [
                'tier'              => 4,
                'customer_group_id' => $customerGroup->id,
                'prices'            => [
                    [
                        'price'       => 1.99,
                        'currency_id' => $currencies->first()->id,
                    ],
                    [
                        'price'       => 2.99,
                        'currency_id' => $currencies->last()->id,
                    ],
                ],
            ],
        ]);

        app(UpdateTieredPricing::class)->execute($variant, $tiers);

        $this->assertDatabaseHas((new Price())->getTable(), [
            'currency_id'       => $currencies->first()->id,
            'customer_group_id' => $customerGroup->id,
            'price'             => 1.99 * $currencies->first()->factor,
            'priceable_type'    => get_class($variant),
            'priceable_id'      => $variant->id,
            'tier'              => 4,
        ]);

        $this->assertDatabaseHas((new Price())->getTable(), [
            'currency_id'       => $currencies->last()->id,
            'customer_group_id' => $customerGroup->id,
            'price'             => 2.99 * $currencies->last()->factor,
            'priceable_type'    => get_class($variant),
            'priceable_id'      => $variant->id,
            'tier'              => 4,
        ]);
    }
}
