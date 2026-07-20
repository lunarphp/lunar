<?php

namespace Lunar\Core\Contracts\Actions\Regions;

use Lunar\Core\Models\Region;

interface CreatesRegion
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Region;
}
