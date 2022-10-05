<?php

namespace Lunar\Hub\Http\Livewire\Pages\Collections;

use Livewire\Component;
use Livewire\ComponentConcerns\PerformsRedirects;
use Lunar\Models\CollectionGroup;

class CollectionGroupsIndex extends Component
{
    use PerformsRedirects;

    public $shouldSkipRender = false;

    public function boot()
    {
        // If we have collection groups in the database, we redirect
        // and load it up so we're straight into editing.
        if ($group = CollectionGroup::orderBy('name')->first()) {
            $this->redirectRoute('hub.collection-groups.show', [
                'group' => $group->id,
            ]);
        }
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.collections.collection-groups.index')
            ->layout('adminhub::layouts.collection-groups', [
                'title' => __('adminhub::catalogue.collections.index.title'),
            ]);
    }
}
