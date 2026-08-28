<?php

namespace Lunar\Panel\Support;

use Lunar\Core\Actions\Discounts\UpdateDiscount;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;

/**
 * Serializes a discount's targeting into draftable per-bucket fields
 * (`target:limitation`, `target:reward`, …), each holding an id list per kind.
 *
 * Reading is the awkward half: core spreads targeting over four tables and
 * which one an entity sits in depends on its bucket. Writing is not — the
 * whole map goes back to UpdatesDiscount, which owns the routing.
 */
class DiscountTargetSchema
{
    public const PREFIX = 'target:';

    /** Buckets whose collections live on the collection_discount pivot. */
    private const PIVOT_COLLECTION_BUCKETS = ['limitation', 'exclusion'];

    /**
     * Every bucket and the kinds it can target, taken from core rather than
     * restated here so the picker cannot drift from what the action accepts.
     *
     * @return array<string, array<int, string>>
     */
    public function buckets(): array
    {
        return UpdateDiscount::BUCKET_KINDS;
    }

    /** @return array<int, string> */
    public function fields(): array
    {
        return array_map(fn (string $bucket) => static::PREFIX.$bucket, array_keys($this->buckets()));
    }

    /**
     * Current targeting per draft field key, as id lists.
     *
     * @return array<string, array<string, int[]>>
     */
    public function values(Discount $discount): array
    {
        $discountables = $discount->discountables()->get();
        $collections = $discount->collections()->get();
        $brands = $discount->brands()->get();
        $customerIds = $this->sorted($discount->customers()->allRelatedIds()->all());

        $values = [];

        foreach ($this->buckets() as $bucket => $kinds) {
            $value = [];

            foreach ($kinds as $kind) {
                $value[$kind] = match (true) {
                    $kind === 'customers' => $customerIds,
                    $kind === 'brands' => $this->sorted(
                        $brands->where('pivot.type', $bucket)->pluck('id')->all()
                    ),
                    $kind === 'collections' && in_array($bucket, self::PIVOT_COLLECTION_BUCKETS, true) => $this->sorted(
                        $collections->where('pivot.type', $bucket)->pluck('id')->all()
                    ),
                    default => $this->sorted(
                        $discountables
                            ->where('type', $bucket)
                            ->where('discountable_type', $this->morphFor($kind))
                            ->pluck('discountable_id')
                            ->all()
                    ),
                };
            }

            $values[static::PREFIX.$bucket] = $value;
        }

        return $values;
    }

    /**
     * The same targeting resolved to display rows, so the edit page can render
     * a chip per target without a second round trip.
     *
     * @return array<string, array<string, array<int, array{id: int, label: string, hint: ?string}>>>
     */
    public function chips(Discount $discount): array
    {
        $values = $this->values($discount);
        $chips = [];

        foreach ($values as $field => $kinds) {
            $chips[$field] = [];

            foreach ($kinds as $kind => $ids) {
                $chips[$field][$kind] = $ids ? $this->resolve($kind, $ids) : [];
            }
        }

        return $chips;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $rules = [];

        foreach ($this->buckets() as $bucket => $kinds) {
            $field = static::PREFIX.$bucket;
            $rules[$field] = ['nullable', 'array'];

            foreach ($kinds as $kind) {
                $rules["{$field}.{$kind}"] = ['nullable', 'array'];
                $rules["{$field}.{$kind}.*"] = ['integer'];
            }
        }

        return $rules;
    }

    /**
     * Canonicalise a submitted bucket so draft equality holds: ints, unique,
     * sorted, and every kind the bucket supports present.
     *
     * @param  array<string, mixed>  $value
     * @return array<string, int[]>
     */
    public function normalizeValue(string $field, array $value): array
    {
        $bucket = substr($field, strlen(static::PREFIX));

        $normalized = [];

        foreach ($this->buckets()[$bucket] ?? [] as $kind) {
            $normalized[$kind] = $this->sorted((array) ($value[$kind] ?? []));
        }

        return $normalized;
    }

    /**
     * Split drafted target fields out of a commit payload into the shape
     * UpdatesDiscount takes, leaving the rest of the values untouched.
     *
     * @param  array<string, mixed>  $values
     * @return array{attributes: array<string, mixed>, targets: ?array<string, array<string, int[]>>}
     */
    public function extract(array $values): array
    {
        $targets = [];
        $attributes = [];

        foreach ($values as $key => $value) {
            if (! str_starts_with($key, static::PREFIX)) {
                $attributes[$key] = $value;

                continue;
            }

            $targets[substr($key, strlen(static::PREFIX))] = $this->normalizeValue($key, (array) $value);
        }

        return [
            'attributes' => $attributes,
            // Null, not an empty array: an untouched bucket must stay untouched
            // rather than being replaced with nothing.
            'targets' => $targets ?: null,
        ];
    }

    /** @return array<int, array{id: int, label: string, hint: ?string}> */
    public function resolve(string $kind, array $ids): array
    {
        return match ($kind) {
            'products' => Product::query()->whereIn('id', $ids)->get()
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'label' => $product->translate('name') ?? '',
                    'hint' => $product->variants()->first()?->sku,
                ])->values()->all(),
            'variants' => ProductVariant::query()->whereIn('id', $ids)->with('product')->get()
                ->map(fn (ProductVariant $variant) => [
                    'id' => $variant->id,
                    'label' => $variant->sku ?? '',
                    'hint' => $variant->product?->translate('name'),
                ])->values()->all(),
            'collections' => Collection::query()->whereIn('id', $ids)->get()
                ->map(fn (Collection $collection) => [
                    'id' => $collection->id,
                    'label' => $collection->translate('name') ?? '',
                    'hint' => null,
                ])->values()->all(),
            'brands' => Brand::query()->whereIn('id', $ids)->get(['id', 'name'])
                ->map(fn (Brand $brand) => [
                    'id' => $brand->id,
                    'label' => $brand->name,
                    'hint' => null,
                ])->values()->all(),
            'customers' => Customer::query()->whereIn('id', $ids)->get()
                ->map(fn (Customer $customer) => [
                    'id' => $customer->id,
                    'label' => trim($customer->full_name) ?: (string) $customer->id,
                    'hint' => $customer->company_name,
                ])->values()->all(),
            default => [],
        };
    }

    /** @return array<string, string> */
    public function labels(): array
    {
        $labels = [];

        foreach (array_keys($this->buckets()) as $bucket) {
            $labels[static::PREFIX.$bucket] = "panel::discounts.bucket_{$bucket}";
        }

        return $labels;
    }

    protected function morphFor(string $kind): string
    {
        return match ($kind) {
            'products' => Product::morphName(),
            'variants' => ProductVariant::morphName(),
            'collections' => Collection::morphName(),
            default => $kind,
        };
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    protected function sorted(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        sort($ids);

        return $ids;
    }
}
