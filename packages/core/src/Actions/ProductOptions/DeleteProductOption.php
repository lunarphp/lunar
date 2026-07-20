<?php

namespace Lunar\Core\Actions\ProductOptions;

use Lunar\Core\Contracts\Actions\ProductOptions\DeletesProductOption;
use Lunar\Core\Exceptions\ProductOptionActionException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;

/**
 * Delete a product option. Options linked to products, or whose values are
 * carried by variants, are kept — detach them first — so no variant loses
 * the option that distinguishes it. The option's values are removed with it.
 */
class DeleteProductOption implements DeletesProductOption
{
    public function execute(ProductOption $productOption): void
    {
        if ($productOption->products()->exists()) {
            throw new ProductOptionActionException('Cannot delete a product option linked to products.');
        }

        $inUse = $productOption->values()
            ->whereHas('variants')
            ->exists();

        if ($inUse) {
            throw new ProductOptionActionException('Cannot delete a product option whose values are carried by variants.');
        }

        DB::transaction(function () use ($productOption): void {
            $productOption->values()->get()->each(fn (ProductOptionValue $value) => $value->delete());
            $productOption->delete();
        });
    }
}
