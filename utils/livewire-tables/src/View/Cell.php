<?php

namespace Lunar\LivewireTables\View;

use Illuminate\View\Component;

class Cell extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('lt::cell');
    }
}
