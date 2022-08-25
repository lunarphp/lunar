<?php

namespace GetCandy\Tests\Unit\Base\Traits;

use GetCandy\Models\Product;
use GetCandy\Models\ProductOption;
use GetCandy\Tests\Unit\Base\Extendable\ExtendableTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;

class HasExtendableModelsTest extends ExtendableTestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function can_get_new_instance_of_the_registered_model()
    {
        $this->expectNotToPerformAssertions();
    }

    /** @test */
    public function new_instance_matches_core_model()
    {
        $data = Product::factory()->make();
        $product = Product::create($data->toArray());

        $this->assertEquals($data->toArray(), [
            'product_type_id' => $product->id,
            'status' => $product->status,
            'brand' => $product->brand,
            'attribute_data' => $product->attribute_data->toArray(),
        ]);
    }

    /** @test */
    public function can_forward_calls_to_extended_model()
    {
        $sizeOption = ProductOption::with('sizes')->find(1);

        $this->assertInstanceOf(Collection::class, $sizeOption->sizes);
        $this->assertCount(1, $sizeOption->sizes);
    }

    /** @test */
    public function can_forward_static_method_calls_to_extended_model()
    {
        $newStaticMethod = ProductOption::getSizesStatic();

        $this->assertInstanceOf(Collection::class, $newStaticMethod);
        $this->assertCount(3, $newStaticMethod);
    }

    /** @test */
    public function can_swap_registered_model_implementation()
    {
        $this->expectNotToPerformAssertions();
    }

    /** @test */
    public function can_get_morph_class_base_model()
    {
        $this->expectNotToPerformAssertions();
    }
}
