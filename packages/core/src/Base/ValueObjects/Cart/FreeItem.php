<?php

namespace Lunar\Base\ValueObjects\Cart;

use Lunar\Models\ProductVariant;

class FreeItem
{
    /**
     * The associated product variant.
     *
     * @var ProductVariant
     */
    public ProductVariant $productVariant;

    /**
     * Quantity available from promotions,
     *
     * @var int
     */
    public int $available = 1;

    /**
     * Group for the item.
     * Allows free items to be grouped together, e.g. by promotion.
     *
     * @var string
     */
    public string $group = '';
}
