<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Attributes;

use GetCandy\Facades\FieldTypeManifest;
use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Http\Livewire\Traits\WithLanguages;
use GetCandy\Models\Attribute;
use GetCandy\Models\AttributeGroup;
use Illuminate\Support\Str;
use Livewire\Component;

class AttributeEdit extends Component
{
    use WithLanguages, Notifies;

    /**
     * The attribute group.
     *
     * @var string
     */
    public AttributeGroup $group;

    /**
     * The attribute instance.
     *
     * @var \GetCandy\Models\Attribute
     */
    public Attribute $attribute;

    public function mount()
    {
        $this->attribute = new Attribute([
            'type' => get_class($this->fieldTypes->first()),
        ]);
    }

    /**
     * Return the available field types.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFieldTypesProperty()
    {
        return FieldTypeManifest::getTypes();
    }

    /**
     * Return the selected field type.
     *
     * @return
     */
    public function getFieldType()
    {
        return app()->make($this->attribute->type);
    }

    /**
     * Return the config for the field type.
     *
     * @return void
     */
    public function getFieldTypeConfig()
    {
        return $this->getFieldType()?->getConfig() ?: null;
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.attributes.attribute-edit')
            ->layout('adminhub::layouts.base');
    }
}
