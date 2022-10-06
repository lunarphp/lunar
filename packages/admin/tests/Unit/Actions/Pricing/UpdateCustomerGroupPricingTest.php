<?php

namespace Lunar\Hub\Tests\Unit\Actions\Pricing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Hub\Actions\Pricing\UpdateCustomerGroupPricing;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Price;
use Lunar\Models\Product;

/**
 * @group hub.actions
 */
class UpdateCustomerGroupPricingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function creates_tiered_pricing_models()
    {
        $product = Product::factory()->hasVariants(1)->create();
        $variant = $product->variants->first();

        $customerGroups = CustomerGroup::factory(2)->create();
        Currency::factory()->create([
            'decimal_places' => 2,
            'default'        => true,
        ]);

        $pricing = [];

        foreach ($customerGroups as $group) {
            $pricing[$group->id] = [];

            foreach (Currency::get() as $currency) {
                $pricing[$group->id][$currency->code] = [
                    'price'             => 199,
                    'currency_id'       => $currency->id,
                    'customer_group_id' => $group->id,
                    'tier'              => 1,
                ];
            }
        }

        app(UpdateCustomerGroupPricing::class)->execute($variant, collect($pricing));

        foreach ($pricing as $prices) {
            foreach ($prices as $price) {
                $this->assertDatabaseHas((new Price())->getTable(), [
                    'currency_id'       => $price['currency_id'],
                    'customer_group_id' => $price['customer_group_id'],
                    'price'             => $price['price'] * 100,
                    'priceable_type'    => get_class($variant),
                    'priceable_id'      => $variant->id,
                    'tier'              => 1,
                ]);
            }
        }
    }
}
