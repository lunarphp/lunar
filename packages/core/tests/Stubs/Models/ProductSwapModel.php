<?php

namespace GetCandy\Tests\Stubs\Models;

class ProductSwapModel extends \GetCandy\Models\Product
{
    public function shouldBeSearchable()
    {
        return false;
    }
}
