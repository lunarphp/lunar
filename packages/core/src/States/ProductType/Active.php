<?php

namespace Lunar\Core\States\ProductType;

class Active extends ProductTypeState
{
    public static string $name = 'active';

    public function label(): string
    {
        return __('lunar::states.product_type.active');
    }
}
