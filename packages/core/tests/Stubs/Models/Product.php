<?php

namespace GetCandy\Tests\Stubs\Models;

class Product extends \GetCandy\Models\Product
{
    use SearchableTrait;

    /**
     * Determine if the model should be searchable.
     *
     * @return bool
     */
    public function shouldBeSearchable()
    {
        return false;
    }
}
