<?php

namespace Lunar\Core\Contracts\Actions\TaxClasses;

use Lunar\Core\Models\TaxClass;

interface UpdatesTaxClass
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(TaxClass $taxClass, array $attributes): TaxClass;
}
