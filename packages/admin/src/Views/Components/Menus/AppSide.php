<?php

namespace Lunar\Hub\Views\Components\Menus;

use Illuminate\View\Component;

class AppSide extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.menus.app-side');
    }
}
