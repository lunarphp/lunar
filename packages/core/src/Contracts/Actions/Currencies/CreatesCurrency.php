<?php

namespace Lunar\Core\Contracts\Actions\Currencies;

use Lunar\Core\Models\Currency;

interface CreatesCurrency
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Currency;
}
