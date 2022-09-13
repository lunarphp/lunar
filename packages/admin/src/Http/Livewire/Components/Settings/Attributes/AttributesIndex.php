<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Attributes;

use Lunar\Facades\AttributeManifest;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Livewire\Component;
use Livewire\WithPagination;

class AttributesIndex extends Component
{
    use WithPagination;

    /**
     * Return the attribute types computed property.
     *
     * @return void
     */
    public function getAttributeTypesProperty()
    {
        return AttributeManifest::getTypes();
    }

    /**
     * Returns stats for an attribute type.
     *
     * @param  string  $attributeType
     * @return array
     */
    public function getStats($attributeType)
    {
        $groups = AttributeGroup::whereAttributableType($attributeType)->get();

        return [
            'group_count'     => $groups->count(),
            'attribute_count' => Attribute::whereIn(
                'attribute_group_id',
                $groups->pluck('id')->toArray()
            )->count(),
        ];
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.attributes.index')
            ->layout('adminhub::layouts.base');
    }
}
