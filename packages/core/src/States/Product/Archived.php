<?php

namespace Lunar\Core\States\Product;

class Archived extends ProductState
{
    public static string $name = 'archived';

    public function label(): string
    {
        return __('lunar::states.product.archived');
    }
}
