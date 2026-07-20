<?php

namespace Lunar\Core\Contracts\Actions\Currencies;

use Lunar\Core\Models\Currency;

interface DeletesCurrency
{
    public function execute(Currency $currency): void;
}
