<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Attributes;

use GetCandy\Facades\AttributeManifest;
use GetCandy\Hub\Http\Livewire\Traits\ConfirmsDelete;
use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Http\Livewire\Traits\WithLanguages;
use GetCandy\Models\Attribute;
use GetCandy\Models\AttributeGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttributeShow extends AbstractAttribute
{
    use Notifies, WithLanguages;

    /**
     * The type property.
     *
     * @var string
     */
    public $type;

    /**
     * The sorted attribute groups
     *
     * @var Collection
     */
    public Collection $sortedAttributeGroups;

    /**
     * Whether we should show the panel to create a new group.
     *
     * @var boolean
     */
    public $showGroupCreate = false;

    /**
     * The id of the attribute group to edit
     *
     * @var int|null
     */
    public $editGroupId;

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'attribute-group-edit.created' => 'refreshGroups',
        'attribute-group-edit.updated' => 'resetGroupEdit',
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
     * Sort the attribute groups.
     *
     * @param array $groups
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
     * @param array $attributes
     * @return void
     */
    public function sortAttributes($attributes)
    {
        DB::transaction(function () use ($attributes) {
            foreach ($attributes['items'] as $attribute) {
                Attribute::whereId($attribute['id'])->update([
                    'position' => $attribute['order'],
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
     * @return \GetCandy\Models\AttributeGroup|null
     */
    public function getAttributeGroupToEditProperty()
    {
        return AttributeGroup::find($this->editGroupId);
    }


    public function resetGroupEdit()
    {
        $this->editGroupId = null;
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
