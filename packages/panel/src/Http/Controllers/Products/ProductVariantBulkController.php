<?php

namespace Lunar\Panel\Http\Controllers\Products;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\Products\DeletesProductVariant;
use Lunar\Core\Contracts\Actions\Products\UpdatesProductVariant;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Exceptions\ProductActionException;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Panel\Http\Requests\Products\ProductVariantBulkRequest;

/**
 * The variants-table bulk bar: enable/disable, delete, set the
 * default-currency base price, or set on-hand stock at the default location
 * across a variant selection. Guarded rows (order history, last variant)
 * are skipped and reported rather than failing the whole batch.
 */
class ProductVariantBulkController
{
    public function update(
        ProductVariantBulkRequest $request,
        Product $product,
        UpdatesProductVariant $updatesProductVariant,
        DeletesProductVariant $deletesProductVariant,
    ): RedirectResponse {
        $validated = $request->validated();

        $variants = $product->variants()->whereIn('id', $validated['ids'])->get();

        $skipped = 0;

        foreach ($variants as $variant) {
            switch ($validated['op']) {
                case 'enable':
                case 'disable':
                    $updatesProductVariant->execute($variant, ['enabled' => $validated['op'] === 'enable']);
                    break;

                case 'destroy':
                    try {
                        $deletesProductVariant->execute($variant);
                    } catch (ProductActionException) {
                        $skipped++;
                    }
                    break;

                case 'price':
                    $this->setBasePrice($variant, (int) $validated['value']);
                    break;

                case 'stock':
                    $this->setStock($variant, (int) $validated['value']);
                    break;
            }
        }

        if ($skipped > 0) {
            return back()->with('error', __('panel::products.flash_bulk_skipped', ['count' => $skipped]));
        }

        return back()->with('success', __('panel::products.flash_bulk_updated'));
    }

    /** Upsert the default-currency base price row at the given minor units. */
    protected function setBasePrice(ProductVariant $variant, int $amount): void
    {
        $currency = Currency::getDefault();

        $existing = $variant->prices()
            ->where('currency_id', $currency->id)
            ->whereNull('customer_group_id')
            ->where('min_quantity', 1)
            ->first();

        if ($existing) {
            $existing->update(['price' => $amount]);

            return;
        }

        $variant->prices()->create([
            'currency_id' => $currency->id,
            'customer_group_id' => null,
            'min_quantity' => 1,
            'price' => $amount,
        ]);
    }

    /** Set on-hand at the default location through an adjustment movement. */
    protected function setStock(ProductVariant $variant, int $onHand): void
    {
        $location = Location::getDefault();

        $current = (int) ($variant->stockLevels()
            ->where('location_id', $location->id)
            ->value('on_hand') ?? 0);

        $delta = $onHand - $current;

        if ($delta !== 0) {
            $variant->adjustStock($location, $delta, StockMovementType::Adjustment);
        }
    }
}
