<?php

namespace GetCandy\Hub\Views\Components;

use Illuminate\View\Component;

class Slideover extends Component
{
    public $title = '';

    public $nested = false;

    public function __construct($title = '', $nested = false)
    {
        $this->title = $title;
        $this->nested = $nested;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.slideover');
    }
}
