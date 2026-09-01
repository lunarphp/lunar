<?php

namespace Lunar\Panel\Http\Controllers\Discounts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lunar\Core\Models\Discount;
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
            ->flatMap(fn (string $kind) => $this->searchKind($targets, $kind, $term, (array) ($existing[$kind] ?? [])))
            ->values();

        return response()->json(['data' => $results]);
    }

    /**
     * @param  int[]  $exclude
     * @return array<int, array{kind: string, id: int, label: string, hint: ?string}>
     */
    protected function searchKind(DiscountTargetSchema $targets, string $kind, string $term, array $exclude): array
    {
        return $targets->query($kind)
            ->whereNotIn('id', $exclude)
            ->when($term !== '', fn ($query) => $this->applyTerm($query, $kind, $term))
            ->limit(self::PER_KIND)
            ->get()
            // Rows are shaped by the schema, not here, so a target reads the
            // same in the picker as it does once it is a chip.
            ->map(fn (Model $model) => ['kind' => $kind, ...$targets->row($kind, $model)])
            ->all();
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    protected function applyTerm(Builder $query, string $kind, string $term): Builder
    {
        $like = "%{$term}%";

        return match ($kind) {
            // The dedicated name column holds a {locale: text} map.
            'products' => $query->where(fn ($query) => $query
                ->where('name', 'like', $like)
                ->orWhereHas('variants', fn ($query) => $query->where('sku', 'like', $like))),
            'variants' => $query->where('sku', 'like', $like),
            'collections' => $query->where('name', 'like', $like),
            'brands' => $query->where('name', 'like', $like),
            'customers' => $query->where(fn ($query) => $query
                ->where('first_name', 'like', $like)
                ->orWhere('last_name', 'like', $like)
                ->orWhere('company_name', 'like', $like)),
            default => $query,
        };
    }
}
