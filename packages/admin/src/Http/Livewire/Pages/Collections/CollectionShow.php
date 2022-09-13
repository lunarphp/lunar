<?php

namespace Lunar\Hub\Http\Livewire\Pages\Collections;

use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\WithLanguages;
use Lunar\Models\Collection;

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
