<?php

namespace GetCandy\Hub\Http\Livewire\Components\Collections;

use GetCandy\Hub\Http\Livewire\Traits\MapsCollectionTree;
use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Models\Collection;
use Livewire\Component;

class CollectionTree extends Component
{
    use Notifies, MapsCollectionTree;

    /**
     * The nodes for the tree.
     *
     * @var array
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
        $ids = collect($payload['items'])->pluck('id')->toArray();

        $objectIdPositions = array_flip($ids);

        $models = Collection::withCount('children')->findMany($ids)->sortBy(function ($model) use ($objectIdPositions) {
            return $objectIdPositions[$model->getKey()];
        })->values();

        $models->each(function ($collection, $index) use ($models) {
            if ($prev = $models[$index - 1] ?? null) {
                $collection->afterNode($prev)->save();
            }
        });

        $this->nodes = $this->mapCollections($models);

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
        // Was the collection that moved part of this tree?
        $matched = collect($this->nodes)->first(fn ($node) => $node['id'] == $id);

        if ($matched) {
            // Get the first node's parent ID and then load them up.
            $parentId = collect($this->nodes)->first()['parent_id'];
            $this->nodes = $this->mapCollections(
                Collection::whereParentId($parentId)->withCount('children')->defaultOrder()->get()
            );
        }
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
                Collection::whereParentId($parentId)->withCount('children')->defaultOrder()->get()
            );
        }

        // Have we just added a collection to one that exists in this tree?
        $nodeMatched = collect($this->nodes)->first(fn ($node) => $node['id'] == $parentId);

        if ($nodeMatched) {
            $this->nodes = $this->mapCollections(
                Collection::whereParentId($nodeMatched['parent_id'])->withCount('children')->defaultOrder()->get()
            );
        }
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
