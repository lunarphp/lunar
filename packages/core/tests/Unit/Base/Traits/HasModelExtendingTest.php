<?php

namespace Lunar\Tests\Unit\Base\Traits;

use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Tests\Unit\Base\Extendable\ExtendableTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

class HasModelExtendingTest extends ExtendableTestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_new_instance_of_the_registered_model()
    {
        $product = Product::find(1);

        $this->assertInstanceOf(\Lunar\Tests\Stubs\Models\Product::class, $product);
    }

    /** @test */
    public function can_forward_calls_to_extended_model()
    {
        $sizeOption = ProductOption::with('sizes')->find(1);

        $this->assertInstanceOf(\Lunar\Tests\Stubs\Models\ProductOption::class, $sizeOption);

        $this->assertInstanceOf(Collection::class, $sizeOption->sizes);
        $this->assertCount(1, $sizeOption->sizes);
    }

    /** @test */
    public function can_forward_static_method_calls_to_extended_model()
    {
        /** @see \Lunar\Tests\Stubs\Models\ProductOption::getSizesStatic() */
        $newStaticMethod = ProductOption::getSizesStatic();

        $this->assertInstanceOf(Collection::class, $newStaticMethod);
        $this->assertCount(3, $newStaticMethod);
    }

    /** @test */
    public function can_swap_registered_model_implementation()
    {
        /** @var Product $product */
        $product = Product::find(1);

        $newProductModel = $product->swap(
            \Lunar\Tests\Stubs\Models\ProductSwapModel::class
        );

        $this->assertInstanceOf(\Lunar\Tests\Stubs\Models\Product::class, $product);
        $this->assertInstanceOf(\Lunar\Tests\Stubs\Models\ProductSwapModel::class, $newProductModel);
    }

    /** @test */
    public function can_get_base_model_morph_class_name()
    {
        $product = \Lunar\Tests\Stubs\Models\Product::query()->create(
            Product::factory()->raw()
        );

        $this->assertEquals(Product::class, $product->getMorphClass());
    }
}
