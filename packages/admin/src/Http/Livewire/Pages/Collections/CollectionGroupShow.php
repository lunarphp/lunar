<?php

namespace Lunar\Hub\Http\Livewire\Pages\Collections;

use Livewire\Component;
use Lunar\Models\CollectionGroup;

class CollectionGroupShow extends Component
{
    public CollectionGroup $group;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.collections.collection-groups.show')
            ->layout('adminhub::layouts.collection-groups', [
                'title' => __('adminhub::catalogue.collections.index.title'),
                'group' => $this->group,
            ]);
    }
}
