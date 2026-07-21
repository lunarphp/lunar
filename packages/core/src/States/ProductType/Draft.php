<?php

namespace Lunar\Core\States\ProductType;

class Draft extends ProductTypeState
{
    public static string $name = 'draft';

    public function label(): string
    {
        return __('lunar::states.product_type.draft');
    }
}
