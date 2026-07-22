<?php

namespace Lunar\Panel\Http\Controllers\Catalog;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;

/**
 * Shared product options with their canonical values, for the variant
 * builder's Add-option menu. Exclusive (product-local) options never
 * surface here — they are created inline on their product.
 */
class ProductOptionSearchController
{
    public function search(Request $request): JsonResponse
    {
        $term = $request->string('q')->value();

        $collection = config('lunar.media.collection');

        $options = ProductOption::query()
            ->shared()
            ->with('values.media')
            ->when($term !== '', function ($query) use ($term) {
                $like = "%{$term}%";

                $query->where(function ($query) use ($like) {
                    // Both columns hold {locale: text} maps.
                    $query->where('name', 'like', $like)
                        ->orWhere('label', 'like', $like);
                });
            })
            ->limit(20)
            ->get()
            ->map(fn (ProductOption $option) => [
                'id' => $option->id,
                'name' => $option->translate('name'),
                'type' => $option->type,
                'values' => $option->values
                    ->sortBy('position')
                    ->map(fn (ProductOptionValue $value) => [
                        'id' => $value->id,
                        'name' => $value->translate('name'),
                        'colour' => $value->meta['colour'] ?? null,
                        'swatch' => $value->getFirstMediaUrl($collection, 'small') ?: ($value->getFirstMediaUrl($collection) ?: null),
                    ])
                    ->values(),
            ])
            ->values();

        return response()->json(['data' => $options]);
    }
}
