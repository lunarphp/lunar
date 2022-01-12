<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Attributes;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Http\Livewire\Traits\WithLanguages;
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
