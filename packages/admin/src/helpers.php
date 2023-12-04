<?php

use Lunar\DataTypes\Price;

if (! function_exists('price')) {
    function price($value, $currency, $unitQty = 1)
    {
        return new Price($value, $currency, $unitQty);
    }
}
