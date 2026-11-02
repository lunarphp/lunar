<?php

namespace Lunar\Bundles\Panel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Models\BundleComponent;
use Lunar\Bundles\Panel\BundleSummary;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;

class ProductBundlesController
{
    /**
     * One summary per variant, so the product page's card can show the
     * editor inline for a single-variant product and a per-variant summary
     * otherwise.
     */
    public function show(Product $product, BundleSummary $summary): JsonResponse
    {
        $variants = $product->variants()
            ->with(['product.thumbnail', 'images', 'values.option', 'bundle'])
            ->orderBy('id')
            ->get()
            ->map(fn (ProductVariant $variant) => $summary->for($variant))
            ->values();

        return response()->json(['variants' => $variants]);
    }

    /**
     * Bundles that include any of this product's variants, each linking to
     * its own product: the merchant's warning before deleting a component.
     */
    public function includedIn(Product $product, BundleSummary $summary): JsonResponse
    {
        $variantIds = $product->variants()->pluck('id');

        $bundles = Bundle::query()
            ->whereHas('components', fn ($query) => $query->whereIn('product_variant_id', $variantIds))
            ->with([
                'variant.product.thumbnail',
                'variant.images',
                'variant.values.option',
                'components' => fn ($query) => $query
                    ->whereIn('product_variant_id', $variantIds)
                    ->with(['variant.product.thumbnail', 'variant.images', 'variant.values.option']),
            ])
            ->get()
            ->map(fn (Bundle $bundle) => [
                'id' => (int) $bundle->getKey(),
                'variant' => $summary->variantRow($bundle->variant),
                'product_url' => route('panel.products.edit', $bundle->variant->product_id),
                'components' => $bundle->components->map(fn (BundleComponent $component) => [
                    'quantity' => (int) $component->quantity,
                    'variant' => $summary->variantRow($component->variant),
                ])->values()->all(),
            ])
            ->values();

        return response()->json(['data' => $bundles]);
    }
}
