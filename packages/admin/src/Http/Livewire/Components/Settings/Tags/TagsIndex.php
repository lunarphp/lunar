<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Tags;

use GetCandy\Models\Tag;
use Livewire\Component;
use Livewire\WithPagination;

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
        return view('adminhub::livewire.components.settings.tags.index', [
            'tags' => Tag::paginate(),
        ])->layout('adminhub::layouts.base');
    }
}
