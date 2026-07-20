<?php

namespace Lunar\Core\Actions\TaxClasses;

use Lunar\Core\Contracts\Actions\TaxClasses\CreatesTaxClass;
use Lunar\Core\Models\TaxClass;

/**
 * Create a tax class. The model's created hook keeps at most one class
 * marked default.
 */
class CreateTaxClass implements CreatesTaxClass
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): TaxClass
    {
        return TaxClass::create($attributes);
    }
}
