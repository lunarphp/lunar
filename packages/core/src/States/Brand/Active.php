<?php

namespace Lunar\Core\States\Brand;

class Active extends BrandState
{
    public static string $name = 'active';

    public function label(): string
    {
        return __('lunar::states.brand.active');
    }
}
