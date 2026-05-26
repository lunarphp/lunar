<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\Models\Contracts\Currency;

interface HasCurrency
{
    public function resolveCurrency(): Currency;
}
