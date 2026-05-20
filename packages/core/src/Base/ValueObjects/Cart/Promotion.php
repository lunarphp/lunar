<?php

namespace Lunar\Core\Base\ValueObjects\Cart;

use Lunar\Core\DataTypes\Price;

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
    public Price $amount;
}
