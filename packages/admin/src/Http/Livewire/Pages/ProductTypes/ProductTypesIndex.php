<?php

namespace GetCandy\Hub\Http\Livewire\Pages\ProductTypes;

use GetCandy\Hub\Http\Livewire\PageComponent;
use Livewire\Component;

class ProductTypesIndex extends PageComponent
{
    protected static $overrideComponentAlias = 'collection-groups.index';

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.product-types.index')
            ->layout('adminhub::layouts.app', [
                'title' => 'Product Types',
            ]);
    }
}
