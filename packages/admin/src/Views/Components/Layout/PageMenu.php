<?php

namespace GetCandy\Hub\Views\Components\Layout;

use Illuminate\View\Component;

class PageMenu extends Component
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
        return view('adminhub::components.layout.page-menu');
    }
}
