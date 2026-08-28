<?php

namespace Lunar\Panel\Http\Controllers\Discounts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Panel\Support\DiscountTargetSchema;

/**
 * One typeahead behind the target picker, returning every kind a bucket can
 * target as `{kind, id, label, hint}` rows.
 *
 * A single search across the allowed kinds rather than a tab per kind: staff
 * usually know the name of the thing they want, not which of five tables it
 * lives in.
 */
class DiscountTargetSearchController
{
    /** How many rows each kind contributes, so no one kind crowds out the rest. */
    private const PER_KIND = 10;

    public function search(Request $request, Discount $discount, DiscountTargetSchema $targets): JsonResponse
    {
        $bucket = $request->string('bucket')->value();

        $allowed = $targets->buckets()[$bucket] ?? [];

        $kinds = $request->has('kinds')
            ? array_intersect($allowed, (array) $request->input('kinds'))
            : $allowed;

        $term = $request->string('q')->value();

        // Whatever the bucket already holds, so the picker never offers a
        // duplicate the action would then dedupe away silently.
        $existing = $targets->values($discount)[DiscountTargetSchema::PREFIX.$bucket] ?? [];

        $results = collect($kinds)
            ->flatMap(fn (string $kind) => $this->searchKind($kind, $term, (array) ($existing[$kind] ?? [])))
            ->values();

        return response()->json(['data' => $results]);
    }

    /**
     * @param  int[]  $exclude
     * @return array<int, array{kind: string, id: int, label: string, hint: ?string}>
     */
    protected function searchKind(string $kind, string $term, array $exclude): array
    {
        $like = "%{$term}%";

        return match ($kind) {
            'products' => Product::query()
                ->whereNotIn('id', $exclude)
                ->when($term !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('name', 'like', $like)
                    ->orWhereHas('variants', fn ($query) => $query->where('sku', 'like', $like))))
                ->with('variants:id,product_id,sku')
                ->limit(self::PER_KIND)
                ->get()
                ->map(fn (Product $product) => [
                    'kind' => 'products',
                    'id' => $product->id,
                    'label' => $product->translate('name') ?? '',
                    'hint' => $product->variants->first()?->sku,
                ])->all(),

            'variants' => ProductVariant::query()
                ->whereNotIn('id', $exclude)
                ->when($term !== '', fn ($query) => $query->where('sku', 'like', $like))
                ->with('product')
                ->limit(self::PER_KIND)
                ->get()
                ->map(fn (ProductVariant $variant) => [
                    'kind' => 'variants',
                    'id' => $variant->id,
                    'label' => $variant->sku ?? '',
                    'hint' => $variant->product?->translate('name'),
                ])->all(),

            'collections' => Collection::query()
                ->whereNotIn('id', $exclude)
                ->when($term !== '', fn ($query) => $query->where('name', 'like', $like))
                ->limit(self::PER_KIND)
                ->get()
                ->map(fn (Collection $collection) => [
                    'kind' => 'collections',
                    'id' => $collection->id,
                    'label' => $collection->translate('name') ?? '',
                    'hint' => null,
                ])->all(),

            'brands' => Brand::query()
                ->whereNotIn('id', $exclude)
                ->when($term !== '', fn ($query) => $query->where('name', 'like', $like))
                ->limit(self::PER_KIND)
                ->get(['id', 'name'])
                ->map(fn (Brand $brand) => [
                    'kind' => 'brands',
                    'id' => $brand->id,
                    'label' => $brand->name,
                    'hint' => null,
                ])->all(),

            'customers' => Customer::query()
                ->whereNotIn('id', $exclude)
                ->when($term !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('company_name', 'like', $like)))
                ->limit(self::PER_KIND)
                ->get()
                ->map(fn (Customer $customer) => [
                    'kind' => 'customers',
                    'id' => $customer->id,
                    'label' => trim($customer->full_name) ?: (string) $customer->id,
                    'hint' => $customer->company_name,
                ])->all(),

            default => [],
        };
    }
}
