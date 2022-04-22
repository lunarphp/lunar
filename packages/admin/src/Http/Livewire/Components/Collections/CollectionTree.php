<?php

namespace GetCandy\Hub\Http\Livewire\Components\Collections;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Models\Collection;
use Livewire\Component;

class CollectionTree extends Component
{
    use Notifies;

    /**
     * The nodes for the tree.
     *
     * @var array
     */
    public array $nodes;

    public $sortGroup;

    public $owner;

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
            $nodes = Collection::whereParentId($nodeId)->withCount('children')->defaultOrder()->get()->toTree()->map(function ($collection) {
                return [
                    'id' => $collection->id,
                    'parent_id' => $collection->parent_id,
                    'name' => $collection->translateAttribute('name'),
                    'thumbnail' => $collection->thumbnail?->getUrl('small'),
                    'children' => [],
                    'children_count' => $collection->children_count,
                ];
            })->toArray();
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

        $this->nodes = $models->map(function ($collection) {
            return [
                'id' => $collection->id,
                'parent_id' => $collection->parent_id,
                'name' => $collection->translateAttribute('name'),
                'thumbnail' => $collection->thumbnail?->getUrl('small'),
                'children' => [],
                'children_count' => $collection->children_count,
            ];
        })->toArray();

        // dd($this->tree);

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
