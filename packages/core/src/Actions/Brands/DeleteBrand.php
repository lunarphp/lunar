<?php

namespace Lunar\Core\Actions\Brands;

use Lunar\Core\Contracts\Actions\Brands\DeletesBrand;
use Lunar\Core\Exceptions\BrandActionException;
use Lunar\Core\Models\Brand;

/**
 * Delete a brand. Refused while the brand still has products — reassign or
 * remove them first (the model's deleting hook enforces the same rule for
 * every other delete path). Discount and collection pivots detach via the
 * same hook.
 */
class DeleteBrand implements DeletesBrand
{
    public static function isProtected(Brand $brand): bool
    {
        return $brand->products()->exists();
    }

    public function execute(Brand $brand): void
    {
        if (static::isProtected($brand)) {
            throw new BrandActionException(
                'Brand has products — reassign or remove them before deleting.'
            );
        }

        $brand->delete();
    }
}
