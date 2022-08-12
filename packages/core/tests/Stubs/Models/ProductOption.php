<?php

namespace GetCandy\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ProductOption extends \GetCandy\Models\ProductOption
{
    use SizesTrait;

    public function sizes(): HasMany
    {
        return $this->values()->where('id', 1);
    }

    public static function getSizesStatic(): Collection
    {
        return static::find(1)->values;
    }
}
