<?php

namespace Lunar\Core\Contracts\Actions\Currencies;

use Lunar\Core\Models\Currency;

interface UpdatesCurrency
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Currency $currency, array $attributes): Currency;
}
