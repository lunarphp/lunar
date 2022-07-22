<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Models\ProductFeature;
use GetCandy\Models\ProductFeatureValue;
use GetCandy\Tests\TestCase;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class ProductFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_product_feature_with_translations()
    {
        $productFeature = ProductFeature::factory()->create();

        $this->assertDatabaseHas((new ProductFeature)->getTable(), [
            'id' => $productFeature->id,
            'name' => json_encode($productFeature->name),
            'handle' => $productFeature->handle,
            'position' => $productFeature->position,
        ]);

        $this->assertDatabaseCount((new ProductFeature)->getTable(), 1);
    }

    /** @test */
    public function handle_matches_name_default_locale()
    {
        /** @var ProductFeature $productFeature */
        $productFeature = ProductFeature::factory()->create();

        $this->assertEquals($productFeature->handle, Str::slug($productFeature->translate('name')));
    }

    /** @test */
    public function handle_if_not_unique_throw_exception()
    {
        $productFeature = ProductFeature::factory()->create();

        $this->expectException(QueryException::class);
        $this->expectWarningMessage('UNIQUE constraint failed');
        ProductFeature::factory()->create([
            'handle' => $productFeature->handle,
        ]);

        $this->assertDatabaseCount((new ProductFeature)->getTable(), 1);

        ProductFeature::factory()->create([
            'handle' => $productFeature->handle.'-unique',
        ]);

        $this->assertDatabaseCount((new ProductFeature)->getTable(), 2);
    }

    /** @test */
    public function can_update_all_product_feature_positions()
    {
        $productFeatures = ProductFeature::factory(10)->create()->each(function ($productFeature) {
            $productFeature->update([
                'position' => $productFeature->id,
            ]);
        });

        $this->assertEquals(range(1, 10), $productFeatures->pluck('position')->toArray());

        $position = 10;
        foreach ($productFeatures as $productFeature) {
            $productFeature->position = $position;
            $productFeature->save();
            $position--;
        }

        $this->assertEquals(array_reverse(range(1, 10)), $productFeatures->pluck('position')->toArray());
    }

    /** @test */
    public function can_delete_product_feature()
    {
        $productFeature = ProductFeature::factory()->create();
        $this->assertDatabaseCount((new ProductFeature)->getTable(), 1);

        $productFeature->delete();
        $this->assertDatabaseCount((new ProductFeature)->getTable(), 0);
    }

    /** @test */
    public function can_delete_product_feature_by_handle()
    {
        $productFeature = ProductFeature::factory()->create();
        $this->assertDatabaseCount((new ProductFeature)->getTable(), 1);

        ProductFeature::where('handle', $productFeature->handle)->delete();
        $this->assertDatabaseCount((new ProductFeature)->getTable(), 0);
    }

    /** @test */
    public function can_create_feature_value()
    {
        $productFeature = ProductFeature::factory()->create();
        $this->assertDatabaseCount((new ProductFeature)->getTable(), 1);

        $productFeature->values()->create([
            'name' => collect([
                'en' => 'Feature Value 1 (EN)',
                'fr' => 'Feature Value 1 (FR)',
            ]),
        ]);

        $this->assertDatabaseCount((new ProductFeatureValue)->getTable(), 1);
        $this->assertCount(1, ProductFeatureValue::whereRelation(
            'productFeature',
            'product_feature_id',
            $productFeature->id)->get(),
        );
    }
}
