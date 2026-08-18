<?php

namespace Lunar\Core\Contracts\Actions\Collections;

use Lunar\Core\Models\Collection;

interface UpdatesCollection
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  ?array<int, array{enabled?: bool, starts_at?: ?string, ends_at?: ?string}>  $channels  channel id keyed pivot rows; null leaves channel availability untouched
     * @param  ?array<int, array{enabled?: bool, visible?: bool, starts_at?: ?string, ends_at?: ?string}>  $customerGroups  customer group id keyed pivot rows; null leaves group availability untouched
     */
    public function execute(
        Collection $collection,
        array $attributes,
        ?array $channels = null,
        ?array $customerGroups = null,
    ): Collection;
}
