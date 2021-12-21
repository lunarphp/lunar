<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Settings\Attributes;

use GetCandy\Models\Attribute;
use Livewire\Component;

class AttributeShow extends Component
{
    /**
     * The instance of the attribute model.
     *
     * @var \GetCandy\Models\Attribute
     */
    public Attribute $attribute;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.attributes.show')
            ->layout('adminhub::layouts.settings', [
                'title' => __('adminhub::settings.attributes.show.title'),
            ]);
    }
}
