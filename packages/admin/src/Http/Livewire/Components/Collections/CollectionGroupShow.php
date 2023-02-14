<?php

namespace Lunar\Hub\Http\Livewire\Components\Collections;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Hub\Http\Livewire\Traits\MapsCollectionTree;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Models\Attribute;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Language;

class CollectionGroupShow extends Component
{
    use Notifies, MapsCollectionTree;

    /**
     * The current collection group.
     *
     * @var CollectionGroup
     */
    public CollectionGroup $group;

    /**
     * The new collection we're making.
     *
     * @var array
     */
    public $collection = null;

    /**
     * Show confirmation if we want to delete the group.
     *
     * @var bool
     */
    public bool $showDeleteConfirm = false;

    /**
     * Failsafe confirmation in order to delete the collection group.
     *
     * @var bool
     */
    public bool $deletionConfirm = false;

    /**
     * The ID of the collection we want to remove.
     *
     * @var int
     */
    public $collectionToRemoveId = null;

    /**
     * Whether we should show the create form.
     *
     * @var bool
     */
    public $showCreateForm = false;

    /**
     * The new collection to creates parent.
     *
     * @var mixed
     */
    public $newCollectionParent = null;

    /**
     * Whether we should be showing search results.
     *
     * @var bool
     */
    public $showCollectionSearchResults = true;

    /**
     * The collections we want to move.
     *
     * @var array
     */
    public $collectionMove = [
        'source' => null,
        'target' => null,
    ];

    /**
     * Search term for searching collections.
     *
     * @var string
     */
    public $searchTerm = null;

    public $slug = null;

    public array $tree = [];

    protected $listeners = [
        'moveToRoot',
        'addCollection',
        'removeCollection',
        'moveCollection' => 'setMoveState',
    ];

    /**
     * Return the validation rules.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'group.name' => 'required|string|max:255|unique:'.CollectionGroup::class.',name,'.$this->group->id,
            'collection.name' => 'required|string|max:255',
            'deletionConfirm' => 'nullable|boolean',
        ];

        if ($this->slugIsRequired) {
            $rules['slug'] = 'required|string|max:255';
        }

        return $rules;
    }

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->loadTree();
    }

    /**
     * Load the tree.
     *
     * @return void
     */
    public function loadTree()
    {
        $this->tree = $this->mapCollections(
            $this->group->collections()->withCount('children')->whereIsRoot()->defaultOrder()->get()
        );
    }

    /**
     * Watcher for when the group name is updated.
     *
     * @return void
     */
    public function updatedGroupName()
    {
        $this->validateOnly('group.name');
        $this->group->handle = Str::slug($this->group->name);
        $this->group->save();
        $this->notify(__('adminhub::notifications.collection-groups.updated'));
    }

    /**
     * Watcher for when the show delete confirm is updated.
     *
     * @return void
     */
    public function updatedShowDeleteConfirm()
    {
        $this->deletionConfirm = false;
    }

    /**
     * Called when component is dehydrated.
     *
     * @return void
     */
    public function dehydrate()
    {
        $this->group->unsetRelations();
    }

    /**
     * Add a collection ready for saving.
     *
     * @param  string  $parent
     * @return void
     */
    public function addCollection($parent = null)
    {
        $this->newCollectionParent = $parent;
        $this->showCreateForm = true;
    }

    /**
     * Set the collection id to remove.
     *
     * @param  string  $nodeId
     * @return void
     */
    public function removeCollection($nodeId)
    {
        $this->collectionToRemoveId = $nodeId;
    }

    /**
     * Delete the collection group.
     *
     * @return void
     */
    public function deleteGroup()
    {
        $this->showDeleteConfirm = false;
        DB::transaction(function () {
            foreach ($this->group->collections as $collection) {
                $collection->products()->detach();
                $collection->customerGroups()->detach();
                $collection->channels()->detach();
                $collection->forceDelete();
            }
            $this->group->forceDelete();
        });

        $this->notify(__('adminhub::notifications.collection-groups.deleted'), 'hub.collection-groups.index');
    }

    /**
     * Move a collection to the root of the tree.
     *
     * @param  string|int  $id
     * @return void
     */
    public function moveToRoot($id)
    {
        $collection = Collection::find($id);

        $collection->makeRoot()->save();

        $this->emit('collectionMoved', $id);

        $this->notify(__('adminhub::notifications.collections.moved_root'));
    }

    /**
     * Set the state to ready collection moving.
     *
     * @param  string  $collectionId
     * @return void
     */
    public function setMoveState($collectionId)
    {
        $this->collectionMove['source'] = $collectionId;
    }

    /**
     * Move a collection.
     *
     * @return void
     */
    public function moveCollection()
    {
        if ($this->targetCollection) {
            $this->targetCollection->appendNode($this->sourceCollection);
        } else {
            $this->sourceCollection->makeRoot()->save();
        }

        $this->searchTerm = null;
        $this->showCollectionSearchResults = true;

        $this->notify(
            __('adminhub::notifications.collections.moved_child', [
                'target' => $this->targetCollection->translateAttribute('name'),
            ])
        );

        $this->emit('collectionsChanged', $this->sourceCollection->parent_id);
        $this->emit('collectionsChanged', $this->targetCollection->parent_id);

        $this->collectionMove = [
            'source' => null,
            'target' => null,
        ];
    }

    /**
     * Listener for when search term updates.
     *
     * @return void
     */
    public function updatedSearchTerm()
    {
        $this->showCollectionSearchResults = true;
    }

    /**
     * Set the target collection to move.
     *
     * @param  int  $id
     * @return void
     */
    public function setMoveTarget($id)
    {
        $this->collectionMove['target'] = $id;
        $this->showCollectionSearchResults = false;
    }

    /**
     * Get the collection search results.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSearchedCollectionsProperty()
    {
        if (! $this->searchTerm) {
            return new \Illuminate\Support\Collection();
        }

        return Collection::search($this->searchTerm)
            ->get()->filter(fn ($collection) => $collection->id != $this->collectionMove['source']);
    }

    /**
     * Get the target collection to move into.
     *
     * @return null|\Lunar\Models\Collection
     */
    public function getTargetCollectionProperty()
    {
        if (! $this->collectionMove['target']) {
            return null;
        }

        return Collection::find($this->collectionMove['target']);
    }

    /**
     * Get the source collection we want to move.
     *
     * @return null|\Lunar\Models\Collection
     */
    public function getSourceCollectionProperty()
    {
        if (! $this->collectionMove['source']) {
            return null;
        }

        return Collection::find($this->collectionMove['source']);
    }

    /**
     * Returns whether the slug should be required.
     *
     * @return bool
     */
    public function getSlugIsRequiredProperty()
    {
        return config('lunar.urls.required', false) &&
            ! config('lunar.urls.generator', null);
    }

    /**
     * Handler for when the slug is updated.
     *
     * @param  string  $value
     * @return void
     */
    public function updatedSlug($value)
    {
        $this->slug = Str::slug($value);
    }

    /**
     * Delete the collection.
     *
     * @return void
     */
    public function deleteCollection()
    {
        DB::transaction(function () {
            foreach ($this->collectionToRemove->descendants()->get() as $descendant) {
                $descendant->products()->detach();
                $descendant->customerGroups()->detach();
                $descendant->channels()->detach();
                $descendant->urls()->delete();
                $descendant->forceDelete();
            }
            $this->collectionToRemove->products()->detach();
            $this->collectionToRemove->customerGroups()->detach();
            $this->collectionToRemove->channels()->detach();
            $this->collectionToRemove->urls()->delete();
            $this->collectionToRemove->forceDelete();
            $this->collectionToRemoveId = null;

            $this->emit('collectionsChanged', $this->collectionToRemove->parent_id);

            $this->notify(
                __('adminhub::notifications.collections.deleted')
            );
        });
    }

    /**
     * Create the new collection.
     *
     * @return void
     */
    public function createCollection()
    {
        $rules = Arr::only($this->rules(), ['collection.name', 'slug']);

        $this->validate($rules, [
            'collection.name.required' => __('adminhub::validation.generic_required'),
        ]);

        $attribute = Attribute::whereHandle('name')->whereAttributeType(Collection::class)->first();

        $attributeType = $attribute?->type ?: TranslatedText::class;

        $name = $this->collection['name'];

        if ($attributeType == TranslatedText::class) {
            $name = [
                $this->defaultLanguage => $this->collection['name'],
            ];
        }

        $collection = Collection::create([
            'collection_group_id' => $this->group->id,
            'attribute_data' => collect([
                'name' => new $attributeType($name),
            ]),
        ], $this->collectionParent);

        if ($this->slug) {
            $collection->urls()->create([
                'slug' => $this->slug,
                'default' => true,
                'language_id' => Language::getDefault()->id,
            ]);
        }

        $this->collection = null;
        $this->slug = null;

        $this->showCreateForm = false;

        $this->loadTree();

        $this->emit('refreshTree', $this->tree);

        $this->notify(
            __('adminhub::notifications.collections.added')
        );
    }

    /**
     * Return the collection tree.
     *
     * @return \Kalnoy\Nestedset\Collection
     */
    public function getCollectionTree()
    {
        return $this->group->load('collections')->collections()->defaultOrder()->get()->toTree();
    }

    /**
     * Getter for returning the collection to remove.
     *
     * @return \Lunar\Models\Collection|null
     */
    public function getCollectionToRemoveProperty()
    {
        return $this->collectionToRemoveId ?
            Collection::find($this->collectionToRemoveId) :
            null;
    }

    /**
     * Getter for the collection parent.
     *
     * @return \Lunar\Models\Collection|null
     */
    public function getCollectionParentProperty()
    {
        return $this->newCollectionParent ?
            Collection::find($this->newCollectionParent) :
            null;
    }

    /**
     * Get the default language code.
     *
     * @return void
     */
    public function getDefaultLanguageProperty()
    {
        return Language::getDefault()->code;
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.collections.collection-groups.show')
            ->layout('adminhub::layouts.collection-groups', [
                'title' => __('adminhub::catalogue.collections.index.title'),
            ]);
    }
}
