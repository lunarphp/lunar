<?php

namespace Lunar\Panel\Contracts;

/**
 * Implemented by a DiscountTypeForm whose type matches no cart line while some
 * of its target buckets are empty.
 *
 * For the line-targeting types an empty bucket means "every line", and the
 * panel says so. A type that reads its products only from the buckets listed
 * here does nothing until each holds at least one target, so the edit page and
 * the discounts list warn about it instead. A separate contract rather than a
 * DiscountTypeForm method, so existing forms keep working unchanged.
 */
interface RequiresDiscountTargets
{
    /**
     * The buckets, a subset of targetBuckets(), that must each hold at least
     * one target before the discount can apply.
     *
     * @return array<int, string>
     */
    public function requiredTargetBuckets(): array;
}
