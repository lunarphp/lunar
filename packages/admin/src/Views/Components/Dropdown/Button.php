<?php

namespace Lunar\Hub\Views\Components\Dropdown;

use Illuminate\View\Component;

class Button extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.dropdown.button');
    }
}
