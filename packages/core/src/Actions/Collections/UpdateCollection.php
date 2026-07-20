<?php

namespace Lunar\Core\Actions\Collections;

use Lunar\Core\Contracts\Actions\Collections\UpdatesCollection;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Collection;

/**
 * Update a collection's attributes and, when given, sync its channel and
 * customer-group availability pivots — a full id-keyed map replaces the
 * current rows, while null leaves that side of availability untouched.
 * Hierarchy moves are not this action's job; see MovesCollection.
 */
class UpdateCollection implements UpdatesCollection
{
    public function execute(
        Collection $collection,
        array $attributes,
        ?array $channels = null,
        ?array $customerGroups = null,
    ): Collection {
        return DB::transaction(function () use ($collection, $attributes, $channels, $customerGroups): Collection {
            $collection->update($attributes);

            if ($channels !== null) {
                $collection->channels()->sync($channels);
            }

            if ($customerGroups !== null) {
                $collection->customerGroups()->sync($customerGroups);
            }

            return $collection;
        });
    }
}
