<?php

namespace Lunar\Panel\Http\Controllers\Products;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A variant's images are an ordered selection from its product's media pool
 * (the media_product_variant pivot): the posted id list replaces the
 * selection, its order becomes the positions, and the first image is the
 * variant's primary.
 */
class ProductVariantMediaController
{
    public function sync(Request $request, Product $product, ProductVariant $productVariant): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['present', 'array'],
            'ids.*' => [
                'integer',
                Rule::exists((new Media)->getTable(), 'id')
                    ->where('model_type', $product->getMorphClass())
                    ->where('model_id', $product->id),
            ],
        ]);

        $sync = collect($validated['ids'])
            ->values()
            ->mapWithKeys(fn (int $id, int $index) => [$id => [
                'primary' => $index === 0,
                'position' => $index + 1,
            ]])
            ->all();

        $productVariant->images()->sync($sync);

        return back()->with('success', __('panel::products.flash_variant_media_updated'));
    }
}
