<?php

namespace Lunar\Core\Contracts\Actions\Regions;

use Lunar\Core\Models\Region;

interface DeletesRegion
{
    public function execute(Region $region): void;
}
