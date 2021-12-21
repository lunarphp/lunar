<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Attributes;

use GetCandy\Models\Attribute;
use Livewire\Component;
use Livewire\WithPagination;

class AttributesIndex extends Component
{
    use WithPagination;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.attributes.index', [
            'attributes' => Attribute::paginate(5),
        ])->layout('adminhub::layouts.base');
    }
}
