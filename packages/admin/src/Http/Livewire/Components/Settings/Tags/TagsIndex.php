<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Tags;

use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Models\Tag;

class TagsIndex extends Component
{
    use WithPagination;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.tags.index')
            ->layout('adminhub::layouts.base');
    }
}
