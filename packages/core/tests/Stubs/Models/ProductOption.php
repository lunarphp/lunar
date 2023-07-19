<?php

namespace Lunar\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ProductOption extends \Lunar\Models\ProductOption
{
    use SizesTrait;

    /**
     * Get the tags
     */
    public function sizes(): HasMany
    {
        return $this->values();
    }

    public static function getSizesStatic(): Collection
    {
        return static::first()->values;
    }
}
