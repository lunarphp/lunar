<?php

namespace Lunar\Hub\Http\Livewire\Components\Collections;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\MapsCollectionTree;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Models\Collection;

class CollectionTree extends Component
{
    use Notifies, MapsCollectionTree;

    /**
     * The nodes for the tree.
     */
    public array $nodes;

    /**
     * The sort group.
     *
     * @var string
     */
    public $sortGroup;

    /**
     * The collection group.
     *
     * @var CollectionGroup
     */
    public $owner;

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'collectionMoved',
        'collectionsChanged',
        'refreshTree',
    ];

    /**
     * Toggle children visibility.
     *
     * @param  int  $nodeId
     * @return void
     */
    public function toggle($nodeId)
    {
        $index = collect($this->nodes)->search(function ($node) use ($nodeId) {
            return $node['id'] == $nodeId;
        });

        $nodes = [];

        if (! count($this->nodes[$index]['children'])) {
            $nodes = $this->mapCollections(
                Collection::whereParentId($nodeId)->withCount('children')->defaultOrder()->get()
            );
        }

        $this->nodes[$index]['children'] = $nodes;
    }

    /**
     * Sort the collections.
     *
     * @param  array  $payload
     * @return void
     */
    public function sort($payload)
    {
        DB::transaction(function () use ($payload) {
            $ids = collect($payload['items'])->pluck('id')->toArray();

            $objectIdPositions = array_flip($ids);

            $models = Collection::withCount('children')
                ->findMany($ids)
                ->sortBy(function ($model) use ($objectIdPositions) {
                    return $objectIdPositions[$model->getKey()];
                })->values();

            Collection::rebuildSubtree(
                $models->first()->parent,
                $models->map(fn ($model) => ['id' => $model->id])->toArray()
            );

            $this->nodes = $this->mapCollections($models);
        });

        $this->notify(
            __('adminhub::notifications.collections.reordered')
        );
    }

    /**
     * Move a node to the root.
     *
     * @param  string  $nodeId
     * @return void
     */
    public function moveToRoot($nodeId)
    {
        $this->emit('moveToRoot', $nodeId);
    }

    /**
     * Add a new collection to the tree.
     *
     * @param  string  $nodeId
     * @return void
     */
    public function addCollection($nodeId)
    {
        $this->emit('addCollection', $nodeId);
    }

    /**
     * Remove a collection.
     *
     * @param  string  $nodeId
     * @return void
     */
    public function removeCollection($nodeId)
    {
        $this->emit('removeCollection', $nodeId);
    }

    /**
     * Move a collection.
     *
     * @param  string  $nodeId
     * @return void
     */
    public function moveCollection($nodeId)
    {
        $this->emit('moveCollection', $nodeId);
    }

    /**
     * Handle when collections are moved.
     *
     * @param  string  $id
     * @return void
     */
    public function collectionMoved($id)
    {
        $parentId = collect($this->nodes)->first()['parent_id'];
        $this->nodes = $this->mapCollections(
            Collection::whereParentId($parentId)->inGroup($this->owner->id)->withCount('children')->defaultOrder()->get()
        );
    }

    /**
     * Handle when collection state changes.
     *
     * @param  string  $parentId
     * @return void
     */
    public function collectionsChanged($parentId)
    {
        // Do the nodes in this tree share the same parent?
        $parentMatched = collect($this->nodes)->first(fn ($node) => $node['parent_id'] == $parentId);

        if ($parentMatched) {
            $this->nodes = $this->mapCollections(
                Collection::whereParentId($parentId)->inGroup($this->owner->id)->withCount('children')->defaultOrder()->get()
            );
        }

        // Have we just added a collection to one that exists in this tree?
        $nodeMatched = collect($this->nodes)->first(fn ($node) => $node['id'] == $parentId);

        if ($nodeMatched) {
            $this->nodes = $this->mapCollections(
                Collection::whereParentId($nodeMatched['parent_id'])->inGroup($this->owner->id)->withCount('children')->defaultOrder()->get()
            );
        }
    }

    /**
     * Refresh the tree with a new set of nodes.
     *
     * @return void
     */
    public function refreshTree(array $nodes)
    {
        $this->nodes = $nodes;
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.collections.collection-tree')
            ->layout('adminhub::layouts.app');
    }
}
