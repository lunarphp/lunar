<?php

namespace Lunar\Tests\Stubs\Models;

class ProductSwapModel extends \Lunar\Models\Product
{
    public function shouldBeSearchable()
    {
        return false;
    }
}
