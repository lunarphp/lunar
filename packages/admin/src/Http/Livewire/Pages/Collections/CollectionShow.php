<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Collections;

use GetCandy\Hub\Http\Livewire\Traits\WithLanguages;
use GetCandy\Models\Collection;
use Livewire\Component;

class CollectionShow extends Component
{
    use WithLanguages;

    public Collection $collection;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.collections.show')
            ->layout('adminhub::layouts.app', [
                'title' => $this->collection->translateAttribute('name'),
            ]);
    }
}
