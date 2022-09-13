<?php

namespace Lunar\Hub\Http\Livewire;

use Livewire\Component;

class Hub extends Component
{
    public function render()
    {
        return view('adminhub::livewire.hub')
            ->layout('adminhub::layouts.app');
    }
}
