<?php

namespace Lunar\Core\Contracts\Actions\Discounts;

use Lunar\Core\Exceptions\DiscountActionException;
use Lunar\Core\Models\Discount;

interface UpdatesDiscount
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  ?array<int, array{enabled?: bool, starts_at?: ?string, ends_at?: ?string}>  $channels  channel id keyed pivot rows; null leaves channel availability untouched
     * @param  ?array<int, array{enabled?: bool, visible?: bool, starts_at?: ?string, ends_at?: ?string}>  $customerGroups  customer group id keyed pivot rows; null leaves group availability untouched
     * @param  ?array{
     *     limitation?: array{products?: int[], variants?: int[], collections?: int[], brands?: int[], customers?: int[]},
     *     exclusion?: array{products?: int[], variants?: int[], collections?: int[], brands?: int[]},
     *     condition?: array{products?: int[], variants?: int[], collections?: int[]},
     *     reward?: array{products?: int[], variants?: int[], collections?: int[]},
     * }  $targets  null leaves targeting untouched; a present bucket replaces that bucket
     *              wholesale, so an omitted kind within it clears that kind
     *
     * @throws DiscountActionException when a bucket is unknown, or is given a kind it cannot target
     */
    public function execute(
        Discount $discount,
        array $attributes,
        ?array $channels = null,
        ?array $customerGroups = null,
        ?array $targets = null,
    ): Discount;
}
