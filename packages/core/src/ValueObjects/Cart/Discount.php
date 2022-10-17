<?php

namespace Lunar\ValueObjects\Cart;

use Lunar\DataTypes\Price;

final class Discount
{
    /**
     * The discount amount.
     *
     * @var Price
     */
    public Price $amount;

    /**
     * The discount description.
     *
     * @var string
     */
    public string $description;

    /**
     * Discount reference for internal use.
     *
     * @var string
     */
    public string $reference;
}
