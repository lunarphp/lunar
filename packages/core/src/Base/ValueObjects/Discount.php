<?php

namespace Lunar\Base\ValueObjects;

use Lunar\DataTypes\Price;

class Discount
{
    /**
     * Description of the discount.
     *
     * @var string
     */
    public string $description = '';

    /**
     * Discount reference.
     *
     * @var string
     */
    public string $reference = '';

    /**
     * Discount amount
     *
     * @var Price
     */
    public Price $amount;
}
