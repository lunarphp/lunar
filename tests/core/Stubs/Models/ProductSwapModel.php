<?php

namespace Lunar\Tests\Core\Stubs\Models;

class ProductSwapModel extends \Lunar\Models\Product
{
    public function shouldBeSearchable()
    {
        return false;
    }
}
