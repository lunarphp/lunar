<?php

namespace Lunar\Core\States\Brand;

class Draft extends BrandState
{
    public static string $name = 'draft';

    public function label(): string
    {
        return __('lunar::states.brand.draft');
    }
}
