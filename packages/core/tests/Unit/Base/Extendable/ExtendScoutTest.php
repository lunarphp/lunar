<?php

namespace Lunar\Tests\Unit\Base\Extendable;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Product;
use Lunar\Tests\Stubs\Models\ProductSwapModel;

/**
 * @group core.extendable.scout
 */
class ExtendScoutTest extends ExtendableTestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_add_new_scout_call_via_extended_model_trait()
    {
        $product = Product::first();
        $this->assertFalse($product->shouldBeSomethingElseSearchable());
    }

    /** @test */
    public function can_method_be_overridden_with_new_instance_on_runtime()
    {
        $product = Product::first();;
        $this->assertFalse($product->shouldBeSearchable());
    }

    /** @test */
    public function can_swap_scout_call_with_extended_model()
    {
        $product = Product::first();
        $this->assertFalse($product->swap(ProductSwapModel::class)->shouldBeSearchable());
    }
}
