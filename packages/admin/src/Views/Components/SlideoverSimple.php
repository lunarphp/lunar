<?php

namespace Lunar\Hub\Views\Components;

use Illuminate\View\Component;

class SlideoverSimple extends Component
{
    public $target = '';

    public function __construct($target)
    {
        $this->target = $target;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.slideover-simple');
    }
}
