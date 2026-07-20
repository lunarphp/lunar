<?php

namespace Lunar\Core\Contracts\Actions\Regions;

use Lunar\Core\Models\Region;

interface UpdatesRegion
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Region $region, array $attributes): Region;
}
