<?php

namespace Lunar\Core\Actions\Products;

use Lunar\Core\Contracts\Actions\Products\DuplicatesProduct;
use Lunar\Core\Contracts\Actions\Products\RecomputesStockReserved;
use Lunar\Core\Contracts\Actions\Products\SyncsStockCommitment;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;

/**
 * Replicate a product alongside its variants, base prices, option values,
 * and attribute data. Names are suffixed to avoid collisions; the duplicate
 * starts life as a draft.
 *
 * Stock is not duplicated: the copy has no stock levels, orders or
 * reservations, so its variants' cached stock rollup is rebuilt from those
 * (empty) sources rather than copied from the source variants.
 */
class DuplicateProduct implements DuplicatesProduct
{
    /**
     * Cached stock columns on a variant, derived from its stock levels,
     * reservations and order lines. None of those carry over to a replica.
     *
     * @var list<string>
     */
    protected const CACHED_STOCK_COLUMNS = [
        'stock_on_hand',
        'stock_incoming',
        'stock_committed',
        'stock_reserved',
        'stock_unavailable',
        'stock_available',
    ];

    public function __construct(
        protected SyncsStockCommitment $syncStockCommitment,
        protected RecomputesStockReserved $recomputeStockReserved,
    ) {}

    public function execute(Product $source, ?string $nameSuffix = null): Product
    {
        /** @var Product $source */
        $suffix = $nameSuffix ?? (string) __('lunar::products.duplicate.name_suffix');

        return DB::transaction(function () use ($source, $suffix): Product {
            /** @var Product $duplicate */
            $duplicate = $source->replicate();
            $duplicate->status = 'draft';
            $duplicate->name = $this->suffixName($source->name, $suffix);
            $duplicate->save();

            foreach ($source->variants as $variant) {
                $this->duplicateVariant($variant, $duplicate);
            }

            return $duplicate->fresh();
        });
    }

    protected function duplicateVariant(ProductVariant $source, Product $newProduct): ProductVariant
    {
        /** @var ProductVariant $replica */
        $replica = $source->replicate(self::CACHED_STOCK_COLUMNS);
        $replica->product_id = $newProduct->id;
        $replica->sku = $source->sku ? $source->sku.'-copy' : null;
        $replica->save();

        $this->syncStockCommitment->execute($replica);
        $this->recomputeStockReserved->execute($replica);

        foreach ($source->basePrices as $price) {
            /** @var Price $copy */
            $copy = $price->replicate();
            $copy->priceable_type = $replica->getMorphClass();
            $copy->priceable_id = $replica->id;
            $copy->save();
        }

        foreach ($source->values as $value) {
            $replica->values()->attach($value->id);
        }

        return $replica;
    }

    /**
     * Suffix every locale of the translatable `name` column so the duplicate is
     * distinguishable from the source without losing other translations.
     *
     * @param  mixed  $name
     * @return mixed
     */
    protected function suffixName($name, string $suffix)
    {
        if (blank($name)) {
            return $name;
        }

        return collect($name)
            ->map(fn ($value) => trim((string) $value).' '.$suffix)
            ->all();
    }
}
