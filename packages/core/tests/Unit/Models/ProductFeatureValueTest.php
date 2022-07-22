<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Models\ProductFeature;
use GetCandy\Models\ProductFeatureValue;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductFeatureValueTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_product_feature_value_with_translations()
    {
        $featureValue = ProductFeatureValue::factory()
            ->for(ProductFeature::factory(), 'productFeature')
            ->create();

        $this->assertDatabaseHas((new ProductFeatureValue)->getTable(), [
            'id' => $featureValue->id,
            'product_feature_id' => $featureValue->productFeature->id,
            'name' => json_encode($featureValue->name),
        ]);

        $this->assertDatabaseCount((new ProductFeatureValue)->getTable(), 1);
    }

    /** @test */
    public function can_edit_translated_product_feature_value()
    {
        /** @var ProductFeatureValue $featureValue */
        $featureValue = ProductFeatureValue::factory()
            ->for(ProductFeature::factory(), 'productFeature')
            ->create();

        $featureValue->update(['name' => $updatedName = collect([
            'en' => $featureValue->translate('name').'-edited',
        ])]);

        $this->assertDatabaseHas((new ProductFeatureValue)->getTable(), [
            'id' => $featureValue->id,
            'product_feature_id' => $featureValue->productFeature->id,
            'name' => $updatedName->toJson(),
        ]);
    }

    /** @test */
    public function can_delete_product_feature_value()
    {
        /** @var ProductFeatureValue $featureValue */
        $featureValue = ProductFeatureValue::factory()
            ->for(ProductFeature::factory(), 'productFeature')
            ->create();

        $featureValue->delete();

        $this->assertDatabaseMissing((new ProductFeatureValue)->getTable(), [
            'id' => $featureValue->id,
        ]);
    }

    /** @test */
    public function can_update_all_product_feature_value_positions()
    {
        $productFeatureValues = ProductFeatureValue::factory(10)
            ->for(ProductFeature::factory(), 'productFeature')
            ->create()
            ->each(function ($featureValue) {
                $featureValue->update([
                    'position' => $featureValue->id,
                ]);
            });

        $this->assertDatabaseCount((new ProductFeatureValue)->getTable(), 10);
        $this->assertEquals(range(1, 10), $productFeatureValues->pluck('position')->toArray());
    }
}
