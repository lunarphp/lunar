<?php

namespace Lunar\Core\States\Product;

class Draft extends ProductState
{
    public static string $name = 'draft';

    public function label(): string
    {
        return __('lunar::states.product.draft');
    }
}
