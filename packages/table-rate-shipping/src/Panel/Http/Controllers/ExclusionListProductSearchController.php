<?php

namespace Lunar\Shipping\Panel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lunar\Core\Models\Product;
use Lunar\Shipping\Models\ShippingExclusionList;
use Lunar\Shipping\Panel\Support\ExclusionRows;

/**
 * The typeahead behind the exclusion list's product picker. Returns the
 * panel's target-option shape so TargetPickerDialog can drive it, and never
 * offers a product the list already excludes.
 */
class ExclusionListProductSearchController
{
    private const LIMIT = 15;

    public function __construct(protected ExclusionRows $exclusionRows) {}

    public function search(Request $request, ShippingExclusionList $shippingExclusionList): JsonResponse
    {
        $term = $request->string('q')->value();
        $like = "%{$term}%";

        $results = Product::query()
            ->with('variants:id,product_id,sku')
            ->whereNotIn('id', $this->exclusionRows->productIds($shippingExclusionList))
            ->when($term !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', $like)
                ->orWhereHas('variants', fn ($query) => $query->where('sku', 'like', $like))))
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Product $product) => ['kind' => 'products', ...$this->exclusionRows->row($product)])
            ->values();

        return response()->json(['data' => $results]);
    }
}
