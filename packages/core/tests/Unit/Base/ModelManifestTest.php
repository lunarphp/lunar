<?php

namespace Lunar\Tests\Unit\Base;

use Lunar\Base\ModelManifestInterface;
use Lunar\Facades\ModelManifest;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Tests\TestCase;
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

        $this->assertInstanceOf(\Lunar\Base\ModelManifest::class, $manifest);
    }

    /** @test */
    public function can_register_models()
    {
        $this->assertCount(0, ModelManifest::getRegisteredModels());

        ModelManifest::register(collect([
            Product::class => \Lunar\Tests\Stubs\Models\Product::class,
            ProductOption::class => \Lunar\Tests\Stubs\Models\ProductOption::class,
        ]));

        $this->assertCount(2, ModelManifest::getRegisteredModels());
    }

    /** @test */
    public function can_get_registered_model_from_base_model()
    {
        ModelManifest::register(collect([
            Product::class => \Lunar\Tests\Stubs\Models\Product::class,
        ]));

        $model = ModelManifest::getRegisteredModel(Product::class);

        $this->assertInstanceOf(\Lunar\Tests\Stubs\Models\Product::class, $model);
    }

    /** @test */
    public function can_get_morph_class_base_model()
    {
        ModelManifest::register(collect([
            Product::class => \Lunar\Tests\Stubs\Models\Product::class,
        ]));

        $customModels = ModelManifest::getRegisteredModels()->flip();

        $this->assertEquals(
            expected: Product::class,
            actual: $customModels->get(\Lunar\Tests\Stubs\Models\Product::class),
        );
    }

    /** @test */
    public function can_get_list_of_registered_base_models()
    {
        ModelManifest::register(collect([
            Product::class => \Lunar\Tests\Stubs\Models\Product::class,
            ProductOption::class => \Lunar\Tests\Stubs\Models\ProductOption::class,
        ]));

        $this->assertEquals(collect([
            Product::class,
            ProductOption::class,
        ]), ModelManifest::getBaseModelClasses());
    }
}
