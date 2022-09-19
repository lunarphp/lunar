<?php

namespace Lunar\Hub\Views\Components\Input;

use Illuminate\View\Component;

class Toggle extends Component
{
    /**
     * Whether the toggle should be in an on state.
     *
     * @var bool
     */
    public $on = false;

    /**
     * Whether the toggle should be disabled.
     *
     * @var bool
     */
    public $disabled = false;

    /**
     * The Off value.
     *
     * @var int
     */
    public $offValue = 0;

    /**
     * The on value.
     *
     * @var int
     */
    public $onValue = 1;

    /**
     * Create the component instance.
     *
     * @param  bool  $on
     * @param  bool  $disabled
     */
    public function __construct($on = false, $disabled = false, $onValue = 1, $offValue = 0)
    {
        $this->on = $on;
        $this->disabled = $disabled;
        $this->onValue = $onValue;
        $this->offValue = $offValue;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.input.toggle');
    }
}
