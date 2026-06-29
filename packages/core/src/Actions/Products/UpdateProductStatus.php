<?php

namespace Lunar\Core\Actions\Products;

use Lunar\Core\Contracts\Actions\Products\UpdatesProductStatus;
use Lunar\Core\Events\Products\ProductStatusUpdated;
use Lunar\Core\Exceptions\ProductActionException;
use Lunar\Core\Models\Product;

/**
 * Apply a status change to one product.
 *
 * Canonical seam for product status transitions. Thin in v2; the body
 * becomes a state-machine transition once that subsystem lands without
 * changing this signature.
 */
class UpdateProductStatus implements UpdatesProductStatus
{
    /**
     * Statuses recognised by Lunar's stock admin views.
     *
     * @var array<int, string>
     */
    public const STATUSES = ['draft', 'published', 'archived'];

    public function execute(Product $product, string $status): Product
    {
        /** @var Product $product */
        $allowed = (array) config('lunar.products.statuses', self::STATUSES);

        if (! in_array($status, $allowed, true)) {
            throw new ProductActionException(
                "Status [{$status}] is not a recognised product status."
            );
        }

        $previous = (string) $product->status;

        if ($previous === $status) {
            return $product;
        }

        $product->forceFill(['status' => $status])->save();

        ProductStatusUpdated::dispatch($product, $previous, $status);

        return $product;
    }
}
