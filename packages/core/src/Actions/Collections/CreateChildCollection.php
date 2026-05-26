<?php

namespace Lunar\Core\Actions\Collections;

use Lunar\Core\Actions\AbstractAction;
use Lunar\Core\Contracts\Actions\Collections\CreatesChildCollection;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Contracts\Collection as CollectionContract;

/**
 * Create a child collection under a parent, taking care of nested-set
 * `appendNode` linkage so descendants are positioned correctly.
 */
final class CreateChildCollection extends AbstractAction implements CreatesChildCollection
{
    public function execute(CollectionContract $parent, string|array $name): Collection
    {
        return DB::transaction(function () use ($parent, $name): Collection {
            $type = (string) Attribute::whereHandle('name')
                ->whereAttributeType(Collection::morphName())
                ->firstOrFail()
                ->type;

            /** @var Collection $child */
            $child = Collection::create([
                'collection_group_id' => $parent->collection_group_id,
                'attribute_data' => [
                    'name' => new $type($name),
                ],
            ]);

            $parent->appendNode($child);

            return $child;
        });
    }
}
