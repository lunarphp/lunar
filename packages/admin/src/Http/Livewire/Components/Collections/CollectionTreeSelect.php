<?php

namespace Lunar\Hub\Http\Livewire\Components\Collections;

use Livewire\Component;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;

class CollectionTreeSelect extends Component
{
    /**
     * The current collection group.
     *
     * @var CollectionGroup
     */
    public $collectionGroupId = null;

    public array $selectedCollections = [];

    public ?string $searchTerm = null;

    public bool $showOnlySelected = false;

    public function mount()
    {
        $this->collectionGroupId = $this->collectionGroups->first()?->id;
    }

    public function getCollectionGroupsProperty()
    {
        return CollectionGroup::get();
    }

    public function toggleSelected()
    {
        $this->showOnlySelected = !$this->showOnlySelected;
    }

    public function getCollectionsProperty()
    {
        if ($this->showOnlySelected) {
            return Collection::whereIn('id', $this->selectedCollections)->get()->toTree();
        }

        if ($this->searchTerm) {
            return Collection::search($this->searchTerm)->get();
        }
        return Collection::inGroup($this->collectionGroupId)->get()->toTree();
    }

    public function updatedSelectedCollections($val)
    {
        $this->emitUp('collectionTreeSelect.updated', $val);
    }

    public function render()
    {
        return view('adminhub::livewire.components.collections.collection-tree-select');
    }
}
