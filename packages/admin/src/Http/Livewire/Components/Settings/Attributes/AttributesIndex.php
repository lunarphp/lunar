<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Attributes;

use Livewire\Component;

class AttributesIndex extends Component
{
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
