<?php

namespace Lunar\Core\Actions\Collections;

use Lunar\Core\Contracts\Actions\Collections\MovesCollection;
use Lunar\Core\Contracts\CacheInvalidator;
use Lunar\Core\Enums\CacheInvalidationReason;
use Lunar\Core\Exceptions\CollectionActionException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;

/**
 * Re-parent a collection, optionally into a different collection group.
 * Validates the target is not itself (or one of its descendants — which
 * would create a cycle) and sits in the destination group. Same-group moves
 * let the nested-set trait recompute `_lft`/`_rgt` for the moved subtree;
 * cross-group moves re-scope the whole subtree and rebuild both trees from
 * their `parent_id` links.
 */
class MoveCollection implements MovesCollection
{
    public function __construct(
        protected CacheInvalidator $cacheInvalidator,
    ) {}

    public function execute(Collection $collection, ?Collection $target = null, ?CollectionGroup $group = null): Collection
    {
        $destinationGroupId = $group?->getKey() ?? $collection->collection_group_id;

        if ($target !== null) {
            if ($target->is($collection)) {
                throw new CollectionActionException('A collection cannot be moved into itself.');
            }

            /** @var Collection $target */
            if ($target->isDescendantOf($collection)) {
                throw new CollectionActionException('A collection cannot be moved into one of its own descendants.');
            }

            if ($target->collection_group_id !== $destinationGroupId) {
                throw new CollectionActionException('The target parent sits in a different collection group.');
            }
        }

        if ($destinationGroupId === $collection->collection_group_id) {
            return DB::transaction(function () use ($collection, $target): Collection {
                $collection->parent()->associate($target)->save();

                return $collection;
            });
        }

        return DB::transaction(function () use ($collection, $target, $destinationGroupId): Collection {
            $sourceGroupId = $collection->collection_group_id;

            // Re-scope the subtree wholesale, then rebuild both groups' trees
            // from their parent_id links — nested-set bounds are only unique
            // per scope, so cross-scope moves cannot ride a single node save.
            $subtreeIds = $collection->descendants()->pluck('id')->push($collection->getKey());

            Collection::query()->whereIn('id', $subtreeIds)->update(['collection_group_id' => $destinationGroupId]);
            Collection::query()->whereKey($collection->getKey())->update(['parent_id' => $target?->getKey()]);

            Collection::scoped(['collection_group_id' => $sourceGroupId])->fixTree();
            Collection::scoped(['collection_group_id' => $destinationGroupId])->fixTree();

            // The raw updates bypass model events, so record what the saved
            // hook would have: the node changed, and every descendant's
            // ancestry (path / breadcrumb) changed with it.
            $collection->refresh();

            $this->cacheInvalidator->record($collection, CacheInvalidationReason::Updated);

            $collection->descendants()->get()->each(
                fn (Collection $descendant) => $this->cacheInvalidator->record($descendant, CacheInvalidationReason::RelatedChanged)
            );

            return $collection;
        });
    }
}
