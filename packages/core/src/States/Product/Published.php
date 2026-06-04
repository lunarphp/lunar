<?php

namespace Lunar\Core\States\Product;

class Published extends ProductState
{
    public static string $name = 'published';

    public function label(): string
    {
        return __('lunar::states.product.published');
    }
}
