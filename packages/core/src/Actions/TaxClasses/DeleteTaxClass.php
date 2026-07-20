<?php

namespace Lunar\Core\Actions\TaxClasses;

use Lunar\Core\Contracts\Actions\TaxClasses\DeletesTaxClass;
use Lunar\Core\Exceptions\TaxClassActionException;
use Lunar\Core\Models\TaxClass;

/**
 * Delete a tax class. Classes with product variants are kept — reassign the
 * variants first — so no variant silently loses its tax treatment. The
 * default class is also kept: make another class the default first. The
 * class's per-zone rate amounts are removed with it.
 */
class DeleteTaxClass implements DeletesTaxClass
{
    public function execute(TaxClass $taxClass): void
    {
        if ($taxClass->default) {
            throw new TaxClassActionException('Cannot delete the default tax class. Make another tax class the default first.');
        }

        if ($taxClass->productVariants()->exists()) {
            throw new TaxClassActionException('Cannot delete a tax class with product variants.');
        }

        $taxClass->taxRateAmounts()->delete();
        $taxClass->delete();
    }
}
