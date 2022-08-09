<?php

namespace GetCandy\Hub\Http\Livewire\Components\Collections;

use Livewire\Component;

class CollectionGroupsIndex extends Component
{
    protected static $overrideComponentAlias = 'collection-groups.index';

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.collections.collection-groups.index')
            ->layout('adminhub::layouts.app', [
                'title' => __('adminhub::catalogue.collections.index.title'),
            ]);
    }
}
