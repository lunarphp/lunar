<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\Models\Product;

interface UpdatesProduct
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  ?array<int, string>  $tags  null leaves tags untouched; an empty array clears them
     * @param  ?array<int, int>  $collectionIds  null leaves collection membership untouched; an empty array clears it
     * @param  ?array<int, array{enabled?: bool, starts_at?: ?string, ends_at?: ?string}>  $channels  channel id keyed pivot rows; null leaves channel availability untouched
     * @param  ?array<int, array{purchasable?: bool, visible?: bool, enabled?: bool, starts_at?: ?string, ends_at?: ?string}>  $customerGroups  customer group id keyed pivot rows; null leaves group availability untouched
     */
    public function execute(
        Product $product,
        array $attributes,
        ?array $tags = null,
        ?array $collectionIds = null,
        ?array $channels = null,
        ?array $customerGroups = null,
    ): Product;
}
