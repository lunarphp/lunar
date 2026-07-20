<?php

namespace Lunar\Core\Contracts\Actions\TaxClasses;

use Lunar\Core\Models\TaxClass;

interface CreatesTaxClass
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): TaxClass;
}
