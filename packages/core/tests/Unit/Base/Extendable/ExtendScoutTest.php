<?php

namespace GetCandy\Tests\Unit\Base\Extendable;

use GetCandy\Models\Product;
use GetCandy\Tests\Stubs\Models\ProductSwapModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExtendScoutTest extends ExtendableTestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_add_new_scout_call_via_extended_model_trait()
    {
        $product = Product::find(1);
        $this->assertFalse($product->shouldBeSomethingElseSearchable());
    }

    /** @test */
    public function can_method_be_overridden_with_new_instance_on_runtime()
    {
        $product = Product::find(1);
        $this->assertFalse($product->shouldBeSearchable());
    }

    /** @test */
    public function can_swap_scout_call_with_extended_model()
    {
        $product = Product::find(1);
        $this->assertFalse($product->swap(ProductSwapModel::class)->shouldBeSearchable());
    }
}
