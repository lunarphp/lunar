<?php

namespace Lunar\Core\Actions\Discounts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Lunar\Core\Contracts\Actions\Discounts\UpdatesDiscount;
use Lunar\Core\Exceptions\DiscountActionException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Discountable;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;

/**
 * Update a discount's attributes and, when given, sync its channel and
 * customer-group availability and its targeting.
 *
 * Targeting is why this action exists. Core spreads it over four tables and
 * which one an entity lands in depends on the bucket holding it, so callers
 * pass one id map per bucket and this action does the routing.
 */
class UpdateDiscount implements UpdatesDiscount
{
    /**
     * The kinds each bucket can target. Anything outside this map is a caller
     * mistake rather than a new feature, so it raises instead of writing rows
     * no discount type reads.
     */
    private const BUCKET_KINDS = [
        'limitation' => ['products', 'variants', 'collections', 'brands', 'customers'],
        'exclusion' => ['products', 'variants', 'collections', 'brands'],
        'condition' => ['products', 'variants', 'collections'],
        'reward' => ['products', 'variants', 'collections'],
    ];

    /**
     * Buckets whose collections live on the collection_discount pivot. The
     * line-targeting types read limitation and exclusion collections there,
     * while BuyXGetY reads its condition and reward collections from
     * discountables under a Collection morph.
     */
    private const PIVOT_COLLECTION_BUCKETS = ['limitation', 'exclusion'];

    public function execute(
        Discount $discount,
        array $attributes,
        ?array $channels = null,
        ?array $customerGroups = null,
        ?array $targets = null,
    ): Discount {
        if ($targets !== null) {
            $this->guardTargets($targets);
        }

        return DB::transaction(function () use ($discount, $attributes, $channels, $customerGroups, $targets): Discount {
            $discount->update($attributes);

            if ($channels !== null) {
                $discount->channels()->sync($channels);
            }

            if ($customerGroups !== null) {
                $discount->customerGroups()->sync($customerGroups);
            }

            foreach ($targets ?? [] as $bucket => $ids) {
                $this->syncBucket($discount, $bucket, $ids);
            }

            return $discount;
        });
    }

    /**
     * @param  array<string, array<string, int[]>>  $targets
     *
     * @throws DiscountActionException
     */
    private function guardTargets(array $targets): void
    {
        foreach ($targets as $bucket => $ids) {
            if (! isset(self::BUCKET_KINDS[$bucket])) {
                throw new DiscountActionException(
                    "Unknown discount target bucket [{$bucket}]; expected one of ".implode(', ', array_keys(self::BUCKET_KINDS)).'.'
                );
            }

            $unsupported = array_diff(array_keys($ids), self::BUCKET_KINDS[$bucket]);

            if ($unsupported) {
                throw new DiscountActionException(
                    "The [{$bucket}] bucket cannot target ".implode(', ', $unsupported).'.'
                );
            }
        }
    }

    /**
     * @param  array<string, int[]>  $ids
     */
    private function syncBucket(Discount $discount, string $bucket, array $ids): void
    {
        $morphs = [
            'products' => Product::morphName(),
            'variants' => ProductVariant::morphName(),
        ];

        $collectionsOnPivot = in_array($bucket, self::PIVOT_COLLECTION_BUCKETS, true);

        if (! $collectionsOnPivot) {
            $morphs['collections'] = Collection::morphName();
        }

        $discount->discountables()->where('type', $bucket)->delete();

        $rows = [];

        foreach ($morphs as $kind => $morph) {
            foreach (array_unique($ids[$kind] ?? []) as $id) {
                $rows[] = [
                    'discount_id' => $discount->id,
                    'discountable_type' => $morph,
                    'discountable_id' => $id,
                    'type' => $bucket,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if ($rows) {
            Discountable::insert($rows);
        }

        if ($collectionsOnPivot) {
            $this->replacePivotBucket($discount->collections(), $bucket, $ids['collections'] ?? []);
        }

        if (in_array('brands', self::BUCKET_KINDS[$bucket], true)) {
            $this->replacePivotBucket($discount->brands(), $bucket, $ids['brands'] ?? []);
        }

        if (in_array('customers', self::BUCKET_KINDS[$bucket], true)) {
            $discount->customers()->sync(array_values(array_unique($ids['customers'] ?? [])));
        }
    }

    /**
     * Replace one bucket's rows on a type-discriminated pivot, leaving the
     * other buckets in place.
     *
     * @param  int[]  $ids
     */
    private function replacePivotBucket(BelongsToMany $relation, string $bucket, array $ids): void
    {
        // detach() honours the wherePivot constraint while attach() ignores it,
        // so one relation instance can safely do both.
        $relation->wherePivot('type', $bucket)->detach();

        $ids = array_values(array_unique($ids));

        if ($ids) {
            $relation->attach(array_fill_keys($ids, ['type' => $bucket]));
        }
    }
}
