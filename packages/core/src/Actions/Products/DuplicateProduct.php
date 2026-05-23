<?php

namespace Lunar\Core\Actions\Products;

use Lunar\Core\Actions\AbstractAction;
use Lunar\Core\Facades\DB;
use Lunar\Core\FieldTypes\TranslatedText;
use Lunar\Core\Models\Contracts\Product as ProductContract;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;

/**
 * Replicate a product alongside its variants, base prices, option values,
 * and attribute data. Names are suffixed to avoid collisions; the duplicate
 * starts life as a draft.
 */
final class DuplicateProduct extends AbstractAction
{
    public function execute(ProductContract $source, ?string $nameSuffix = null): Product
    {
        /** @var Product $source */
        $suffix = $nameSuffix ?? (string) __('lunar::products.duplicate.name_suffix');

        return DB::transaction(function () use ($source, $suffix): Product {
            /** @var Product $duplicate */
            $duplicate = $source->replicate();
            $duplicate->status = 'draft';
            $duplicate->attribute_data = $this->suffixTranslatedName($source->attribute_data, $suffix);
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
     * Suffix every translated `name` entry in an attribute_data bag so the
     * duplicate is distinguishable from the source without losing other
     * translations.
     *
     * @param  mixed  $attributeData
     * @return mixed
     */
    protected function suffixTranslatedName($attributeData, string $suffix)
    {
        if ($attributeData === null) {
            return null;
        }

        $bag = collect($attributeData);

        if (! $bag->has('name')) {
            return $attributeData;
        }

        $name = $bag->get('name');

        if ($name instanceof TranslatedText) {
            $values = collect($name->getValue())
                ->map(fn ($value) => trim((string) $value).' '.$suffix)
                ->all();

            $bag->put('name', new TranslatedText($values));
        }

        return $bag;
    }
}
