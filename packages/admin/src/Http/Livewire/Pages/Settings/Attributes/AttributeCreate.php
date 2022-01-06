<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Settings\Attributes;

use Livewire\Component;

class AttributeCreate extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.attributes.create')
            ->layout('adminhub::layouts.settings', [
                'title' => __('adminhub::settings.attributes.create.title'),
            ]);
    }
}
