<?php

namespace GetCandy\Tests\Unit\Base;

use GetCandy\Base\AttributeManifest;
use GetCandy\Base\AttributeManifestInterface;
use GetCandy\Models\Channel;
use GetCandy\Models\Collection as ModelsCollection;
use GetCandy\Models\Product;
use GetCandy\Models\Url;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

/**
 * @group models.base
 */
class BaseModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function model_is_macroable()
    {
        Product::macro('foo', function () {
            return 'bar';
        });

        $product = Product::factory()->create();

        $this->assertEquals('bar', $product->foo());
    }

    /** @test */
    public function macros_are_scoped_to_the_correct_model()
    {
        Product::macro('getPermalink', function () {
            return $this->defaultUrl->slug;
        });

        ModelsCollection::macro('getPermalink', function () {
            return $this->defaultUrl->slug;
        });

        $product = Product::factory()->create();

        $collection = ModelsCollection::factory()->create();

        $collection->urls()->create(
            Url::factory()->make([
                'slug' => 'foo-collection',
                'default' => true,
            ])->toArray()
        );

        $product->urls()->create(
            Url::factory()->make([
                'slug' => 'foo-product',
                'default' => true,
            ])->toArray()
        );

        $this->assertEquals('foo-product', $product->getPermalink());

        $this->assertEquals('foo-collection', $collection->getPermalink());
    }
}
