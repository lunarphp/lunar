<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Attributes;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Http\Livewire\Traits\WithLanguages;
use GetCandy\Models\AttributeGroup;
use Illuminate\Support\Str;
use Livewire\Component;

class AttributeGroupCreate extends Component
{
    use WithLanguages, Notifies;

    /**
     * The type of attributable this is.
     *
     * @var string
     */
    public $attributableType;

    /**
     * The handle for the attributable type.
     *
     * @var string
     */
    public $typeHandle;

    /**
     * The new attribute group.
     *
     * @var AttributeGroup
     */
    public AttributeGroup $attributeGroup;

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            "attributeGroup.name.{$this->defaultLanguage->code}" => 'required|string|max:255',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->attributeGroup = new AttributeGroup;
    }

    public function create()
    {
        $this->validate();
        $this->attributeGroup->attributable_type = $this->attributableType;
        $this->attributeGroup->position = AttributeGroup::whereAttributableType(
            $this->attributableType
        )->count() + 1;

        $this->attributeGroup->handle = Str::snake("{$this->typeHandle}_{$this->attributeGroup->translate('name')}");
        $this->attributeGroup->save();

        $this->emit('attribute-group-create.created', $this->attributeGroup->id);

        $this->notify('Attribute group created');
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.attributes.attribute-group-create')
            ->layout('adminhub::layouts.base');
    }
}
