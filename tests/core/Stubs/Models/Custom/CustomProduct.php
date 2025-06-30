<?php

namespace Lunar\Tests\Core\Stubs\Models\Custom;

use Lunar\Tests\Core\Stubs\Models\SearchableTrait;

class CustomProduct extends \Lunar\Models\Product
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
