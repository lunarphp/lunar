<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Attributes;

use Illuminate\Support\Collection;
use Lunar\Facades\DB;
use Lunar\Facades\AttributeManifest;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Http\Livewire\Traits\WithLanguages;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;

class AttributeShow extends AbstractAttribute
{
    use Notifies;
    use WithLanguages;

    /**
     * The type property.
     *
     * @var string
     */
    public $type;

    /**
     * The sorted attribute groups.
     */
    public Collection $sortedAttributeGroups;

    /**
     * Whether we should show the panel to create a new group.
     *
     * @var bool
     */
    public $showGroupCreate = false;

    /**
     * The attribute group id to use for creating an attribute.
     *
     * @var int|null
     */
    public $attributeCreateGroupId = null;

    /**
     * The id of the attribute group to edit.
     *
     * @var int|null
     */
    public $editGroupId;

    /**
     * The id of the attribute group to delete.
     *
     * @var int|null
     */
    public $deleteGroupId;

    /**
     * The id of the attribute to edit.
     *
     * @var int|null
     */
    public $editAttributeId = null;

    /**
     * The ID of the attribute we want to delete.
     *
     * @var int|null
     */
    public $deleteAttributeId = null;

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'attribute-group-edit.created' => 'refreshGroups',
        'attribute-group-edit.updated' => 'resetGroupEdit',
        'attribute-edit.created' => 'resetAttributeEdit',
        'attribute-edit.updated' => 'resetAttributeEdit',
        'attribute-edit.closed' => 'resetAttributeEdit',
    ];

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->sortedAttributeGroups = $this->attributeGroups;
    }

    /**
     * Get the current attribute type class.
     *
     * @return string
     */
    public function getTypeClassProperty()
    {
        return AttributeManifest::getType($this->type);
    }

    /**
     * Return the attribute groups for this type class.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAttributeGroupsProperty()
    {
        return AttributeGroup::whereAttributableType($this->typeClass)
            ->orderBy('position')->get();
    }

    /**
     * Return the group to be used when creating an attribute.
     *
     * @return \Lunar\Models\AttributeGroup
     */
    public function getAttributeCreateGroupProperty()
    {
        return AttributeGroup::find($this->attributeCreateGroupId);
    }

    /**
     * Sort the attribute groups.
     *
     * @param  array  $groups
     * @return void
     */
    public function sortGroups($groups)
    {
        DB::transaction(function () use ($groups) {
            $this->sortedAttributeGroups = $this->attributeGroups->map(function ($group) use ($groups) {
                $updatedOrder = collect($groups['items'])->first(function ($updated) use ($group) {
                    return $updated['id'] == $group->id;
                });
                $group->position = $updatedOrder['order'];
                $group->save();

                return $group;
            })->sortBy('position');
        });
        $this->notify(
            __('adminhub::notifications.attribute-groups.reordered')
        );
    }

    /**
     * Sort the attributes.
     *
     * @param  array  $attributes
     * @return void
     */
    public function sortAttributes($attributes)
    {
        DB::transaction(function () use ($attributes) {
            foreach ($attributes['items'] as $attribute) {
                Attribute::whereId($attribute['id'])->update([
                    'position' => $attribute['order'],
                    'attribute_group_id' => $attributes['owner'],
                ]);
            }
        });

        $this->refreshGroups();

        $this->notify(
            __('adminhub::notifications.attributes.reordered')
        );
    }

    /**
     * Refresh the attribute groups.
     *
     * @return void
     */
    public function refreshGroups()
    {
        $this->sortedAttributeGroups = AttributeGroup::whereAttributableType($this->typeClass)
        ->orderBy('position')->get();

        $this->showGroupCreate = false;
    }

    /**
     * Return the computed property for the group to edit.
     *
     * @return \Lunar\Models\AttributeGroup|null
     */
    public function getAttributeGroupToEditProperty()
    {
        return AttributeGroup::find($this->editGroupId);
    }

    /**
     * Return the attribute marked for deletion.
     *
     * @return void
     */
    public function getAttributeGroupToDeleteProperty()
    {
        return AttributeGroup::find($this->deleteGroupId);
    }

    /**
     * Return the attribute to edit.
     *
     * @return \Lunar\Models\Attribute
     */
    public function getAttributeToEditProperty()
    {
        return Attribute::find($this->editAttributeId);
    }

    /**
     * Return the attribute to delete.
     *
     * @return void
     */
    public function getAttributeToDeleteProperty()
    {
        return Attribute::find($this->deleteAttributeId);
    }

    /**
     * Returns whether the group to delete has system attributes
     * associated to it and therefore protected.
     *
     * @return bool
     */
    public function getGroupProtectedProperty()
    {
        return $this->attributeGroupToDelete ?
            $this->attributeGroupToDelete->attributes->filter(
                fn ($attribute) => (bool) $attribute->system
            )->count() : false;
    }

    /**
     * Reset the group edting state.
     *
     * @return void
     */
    public function resetGroupEdit()
    {
        $this->editGroupId = null;
    }

    /**
     * Reset the attribute edit state.
     *
     * @return void
     */
    public function resetAttributeEdit()
    {
        $this->attributeCreateGroupId = null;
        $this->editAttributeId = null;
        $this->refreshGroups();
    }

    /**
     * Delete the attribute group.
     *
     * @return void
     */
    public function deleteGroup()
    {
        // If the group has system attributes, we can't delete it.
        if ($this->groupProtected) {
            $this->notify(
                __('adminhub::notifications.attribute-groups.delete_protected')
            );

            return;
        }
        DB::transaction(function () {
            DB::connection(config('lunar.database.connection'))
                ->table(config('lunar.database.table_prefix').'attributables')
                ->whereIn(
                    'attribute_id',
                    $this->attributeGroupToDelete->attributes()->pluck('id')->toArray()
                )->delete();
            $this->attributeGroupToDelete->attributes()->delete();
            $this->attributeGroupToDelete->delete();
        });
        $this->deleteGroupId = null;
        $this->refreshGroups();

        $this->notify(
            __('adminhub::notifications.attribute-groups.deleted')
        );
    }

    public function deleteAttribute()
    {
        DB::transaction(function () {
            DB::connection(config('lunar.database.connection'))
                ->table(config('lunar.database.table_prefix').'attributables')
                ->where(
                    'attribute_id',
                    $this->attributeToDelete->id
                )->delete();

            $this->attributeToDelete->delete();
        });

        $this->notify(
            __('adminhub::notifications.attributes.deleted')
        );

        $this->deleteAttributeId = null;
        $this->refreshGroups();
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.attributes.show')
            ->layout('adminhub::layouts.base');
    }
}
