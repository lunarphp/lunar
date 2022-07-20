<?php

namespace GetCandy\Hub\Views\Components\Tabs;

use Illuminate\View\Component;

class Heading extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.tabs.heading');
    }
}
