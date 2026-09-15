<?php

namespace Lunar\Bundles\Panel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Panel\BundleSummary;
use Lunar\Core\Models\ProductVariant;

/**
 * Variant lookup behind the component picker. Rows carry the `{kind, id,
 * label, hint}` shape the panel's TargetPickerDialog renders plus the
 * variant fields the editor keeps once a row is picked. Bundle variants are
 * never offered, so nesting is impossible from the UI as well as the actions.
 */
class VariantSearchController
{
    public function search(Request $request, BundleSummary $summary): JsonResponse
    {
        $term = $request->string('q')->value();

        $variants = ProductVariant::query()
            ->with(['product.thumbnail', 'images', 'values.option'])
            ->whereNotIn('id', Bundle::query()->select('product_variant_id'))
            ->when($request->filled('exclude'), fn ($query) => $query->whereKeyNot($request->integer('exclude')))
            ->when($term !== '', function ($query) use ($term) {
                $like = "%{$term}%";

                $query->where(function ($query) use ($like) {
                    // The dedicated name column holds a {locale: text} map.
                    $query->where('sku', 'like', $like)
                        ->orWhereHas('product', fn ($query) => $query->where('name', 'like', $like));
                });
            })
            ->orderBy('id')
            ->limit(20)
            ->get()
            ->map(function (ProductVariant $variant) use ($summary) {
                $row = $summary->variantRow($variant);

                return [
                    ...$row,
                    'kind' => 'variants',
                    'label' => $row['option'] ? "{$row['name']} ({$row['option']})" : $row['name'],
                    'hint' => $row['sku'],
                    'is_bundle' => false,
                ];
            })
            ->values();

        return response()->json(['data' => $variants]);
    }
}
