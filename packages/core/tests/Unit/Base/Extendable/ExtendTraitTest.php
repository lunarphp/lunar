<?php

namespace Lunar\Tests\Unit\Base\Extendable;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Product;

class ExtendTraitTest extends ExtendableTestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_override_scout_should_be_searchable_method()
    {
        $product = Product::find(1);
        $this->assertFalse($product->shouldBeSearchable());
    }
}
