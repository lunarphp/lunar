<?php

namespace GetCandy\Hub\Views\Components;

use Illuminate\View\Component;

class Icon extends Component
{
    /**
     * Specify the HTML tag the component should render.
     *
     * @var string
     */
    public $ref;

    /**
     * The style of the icon, either outline or solid.
     *
     * @var string
     */
    public $style = 'outline';

    /**
     * Initialise the component.
     *
     * @param  string  $ref
     * @param  string  $style
     */
    public function __construct($ref, $style = 'outline')
    {
        $this->ref = $ref;
        $this->style = $style;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.icon');
    }
}
