<?php

namespace GetCandy\Hub\Views\Components;

use Illuminate\View\Component;

class PageMenuLayout extends Component
{
    public function __construct()
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.page-menu-layout');
    }
}
