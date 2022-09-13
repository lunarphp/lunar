<?php

namespace Lunar\Hub\Views\Components\GetCandy;

use Illuminate\View\Component;

class Stamp extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.lunar.stamp');
    }
}
