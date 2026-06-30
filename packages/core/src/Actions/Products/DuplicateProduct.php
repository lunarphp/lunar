<?php

namespace Lunar\Core\Actions\Products;

use Lunar\Core\Contracts\Actions\Products\DuplicatesProduct;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;

/**
 * Replicate a product alongside its variants, base prices, option values,
 * and attribute data. Names are suffixed to avoid collisions; the duplicate
 * starts life as a draft.
 */
class DuplicateProduct implements DuplicatesProduct
{
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
        $replica = $source->replicate();
        $replica->product_id = $newProduct->id;
        $replica->sku = $source->sku ? $source->sku.'-copy' : null;
        $replica->save();

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
