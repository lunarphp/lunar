<?php

namespace GetCandy\Tests\Unit\Base;

use GetCandy\Base\ModelManifestInterface;
use GetCandy\Facades\ModelManifest;
use GetCandy\Models\Product;
use GetCandy\Models\ProductOption;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group model-manifest
 */
class ModelManifestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_instantiate_class()
    {
        $manifest = app(ModelManifestInterface::class);

        $this->assertInstanceOf(\GetCandy\Base\ModelManifest::class, $manifest);
    }

    /** @test */
    public function can_register_models()
    {
        $this->assertCount(0, ModelManifest::getRegisteredModels());

        ModelManifest::register(collect([
            Product::class => \GetCandy\Tests\Stubs\Models\Product::class,
            ProductOption::class => \GetCandy\Tests\Stubs\Models\ProductOption::class,
        ]));

        $this->assertCount(2, ModelManifest::getRegisteredModels());
    }

    /** @test */
    public function can_get_registered_model_from_base_model()
    {
        ModelManifest::register(collect([
            Product::class => \GetCandy\Tests\Stubs\Models\Product::class,
        ]));

        $model = ModelManifest::getRegisteredModel(Product::class);

        $this->assertInstanceOf(\GetCandy\Tests\Stubs\Models\Product::class, $model);
    }

    /** @test */
    public function can_get_morph_class_base_model()
    {
        ModelManifest::register(collect([
            Product::class => \GetCandy\Tests\Stubs\Models\Product::class,
        ]));

        $product = \GetCandy\Tests\Stubs\Models\Product::query()->create(
            Product::factory()->raw()
        );

        $this->assertEquals(Product::class, $product->getMorphClass());
    }

    /** @test */
    public function can_get_list_of_registered_base_models()
    {
        ModelManifest::register(collect([
            Product::class => \GetCandy\Tests\Stubs\Models\Product::class,
            ProductOption::class => \GetCandy\Tests\Stubs\Models\ProductOption::class,
        ]));

        $this->assertEquals(collect([
            Product::class,
            ProductOption::class,
        ]), ModelManifest::getBaseModelClasses());
    }
}
