<?php

namespace Lunar\Base\ValueObjects;

use Lunar\DataTypes\Price;

class Promotion
{
    /**
     * Description of the promotion.
     *
     * @var string
     */
    public string $description = '';

    /**
     * Promotion reference.
     *
     * @var string
     */
    public string $reference = '';

    /**
     * Promotion amount
     *
     * @var Price
     */
    public Price $amount;
}
