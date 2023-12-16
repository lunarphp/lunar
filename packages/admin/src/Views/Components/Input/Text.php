<?php

namespace Lunar\Hub\Views\Components\Input;

use Illuminate\View\Component;

class Text extends Component
{
    /**
     * Whether or not the input has an error to show.
     */
    public bool $error = false;

    /**
     * Initialise the component.
     *
     * @param  bool  $error
     */
    public function __construct($error = false)
    {
        $this->error = $error;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.input.text');
    }
}
