<?php

namespace Lunar\Panel\Http\Controllers\Catalog;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lunar\Core\Models\Product;

/**
 * Lightweight product lookup for relation pickers: id, translated name,
 * lead SKU, thumbnail, brand and status, filtered by a search term across
 * name and variant SKUs. Shared by every catalog screen that attaches
 * products.
 */
class ProductSearchController
{
    public function search(Request $request): JsonResponse
    {
        $term = $request->string('q')->value();

        $products = Product::query()
            ->with(['thumbnail', 'brand:id,name', 'variants:id,product_id,sku'])
            ->when($term !== '', function ($query) use ($term) {
                $like = "%{$term}%";

                $query->where(function ($query) use ($like) {
                    // The dedicated name column holds a {locale: text} map.
                    $query->where('name', 'like', $like)
                        ->orWhereHas('variants', fn ($query) => $query->where('sku', 'like', $like));
                });
            })
            ->limit(20)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->translate('name'),
                'sku' => $product->variants->first()?->sku,
                'variants_count' => $product->variants->count(),
                'thumbnail' => $product->thumbnail?->getAvailableUrl(['small']),
                'brand' => $product->brand?->name,
                'status' => $product->status->getValue(),
            ])
            ->values();

        return response()->json(['data' => $products]);
    }
}
