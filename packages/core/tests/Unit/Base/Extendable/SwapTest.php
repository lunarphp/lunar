<?php

namespace GetCandy\Tests\Unit\Base\Extendable;

use GetCandy\Models\Product;
use GetCandy\Tests\Stubs\Models\ProductSwapModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SwapTest extends ExtendableTestCase
{
    use RefreshDatabase;

    protected Product $product;

    /** @test */
    public function core_model_made_aware_of_swapping_instances()
    {
        $this->product = Product::find(1);
        $this->product->swap(ProductSwapModel::class);

        $this->product = Product::find(3);
        $this->assertFalse($this->product->shouldBeSearchable());
    }
}
