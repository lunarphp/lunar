<?php

namespace GetCandy\Hub\Views\Components;

use Illuminate\View\Component;

class Tooltip extends Component
{
    public $text = '';

    public function __construct($text = '')
    {
        $this->text = $text;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.tooltip');
    }
}
