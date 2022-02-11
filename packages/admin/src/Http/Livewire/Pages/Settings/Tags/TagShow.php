<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Settings\Tags;

use GetCandy\Models\Tag;
use Livewire\Component;

class TagShow extends Component
{
    public Tag $tag;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.tags.show')
            ->layout('adminhub::layouts.settings', [
                'title' => __('adminhub::settings.tags.show.title'),
            ]);
    }
}
