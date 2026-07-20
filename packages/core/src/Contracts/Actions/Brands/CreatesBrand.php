<?php

namespace Lunar\Core\Contracts\Actions\Brands;

use Lunar\Core\Models\Brand;

interface CreatesBrand
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  ?array<int, int>  $collectionIds  null leaves collection membership untouched; an empty array clears it
     */
    public function execute(array $attributes, ?array $collectionIds = null): Brand;
}
