<?php

namespace GetCandy\Hub\Views\Components;

use Illuminate\View\Component;

class Alert extends Component
{
    /**
     * The level of the alert, used in display logic.
     *
     * @var string
     */
    public $level = 'info';

    /**
     * Initialise the component.
     *
     * @param string $level
     */
    public function __construct($level = null)
    {
        $this->level = $level;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.alert');
    }
}
