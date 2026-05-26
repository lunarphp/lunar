<?php

namespace Lunar\Core\ValueObjects\Cart;

use Lunar\Core\DataObjects\PriceValue;

class Promotion
{
    /**
     * Description of the promotion.
     */
    public string $description = '';

    /**
     * Promotion reference.
     */
    public string $reference = '';

    /**
     * Discount amount
     */
    public PriceValue $amount;
}
