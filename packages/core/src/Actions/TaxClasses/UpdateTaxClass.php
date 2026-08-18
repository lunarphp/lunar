<?php

namespace Lunar\Core\Actions\TaxClasses;

use Lunar\Core\Contracts\Actions\TaxClasses\UpdatesTaxClass;
use Lunar\Core\Exceptions\TaxClassActionException;
use Lunar\Core\Models\TaxClass;

/**
 * Update a tax class. The default flag moves by promoting another class,
 * never by unsetting — so a store with tax classes always has a default; the
 * model's updated hook un-defaults whichever class held the flag.
 */
class UpdateTaxClass implements UpdatesTaxClass
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(TaxClass $taxClass, array $attributes): TaxClass
    {
        if ($taxClass->default && array_key_exists('default', $attributes) && ! $attributes['default']) {
            throw new TaxClassActionException('Cannot unset the default tax class. Make another tax class the default instead.');
        }

        $taxClass->update($attributes);

        return $taxClass;
    }
}
