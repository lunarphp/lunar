<?php

namespace Stubs\Models;

class ProductSwapModel extends \Lunar\Models\Product
{
    public function shouldBeSearchable()
    {
        return false;
    }
}
