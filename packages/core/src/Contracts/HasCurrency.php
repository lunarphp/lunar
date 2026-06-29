<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\Models\Currency;

interface HasCurrency
{
    public function resolveCurrency(): Currency;
}
