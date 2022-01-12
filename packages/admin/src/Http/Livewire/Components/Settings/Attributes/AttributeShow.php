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
     * Validates the LiveWire request, updates the model and dispatches and event.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        $this->attribute->save();

        $this->notify(
            'Attribute successfully updated.',
            'hub.attributes.index'
        );
    }

    /**
     * Soft deletes a channel.
     *
     * @return void
     */
    public function delete()
    {
        if (!$this->canDelete) {
            return;
        }

        DB::transaction(function () {
            $this->attribute->delete();
        });

        $this->notify(
            'Attribute successfully deleted.',
            'hub.attributes.index'
        );
    }

    public function getCanDeleteProperty()
    {
        return $this->deleteConfirm === $this->attribute->handle;
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
