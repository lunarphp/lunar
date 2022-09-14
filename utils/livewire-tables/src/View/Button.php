<?php

namespace Lunar\LivewireTables\View;

use Illuminate\View\Component;

class Button extends Component
{
    /**
     * Specify the HTML tag the component should render.
     *
     * @var string
     */
    public $tag = 'button';

    /**
     * The button theme.
     *
     * @var string
     */
    public $theme = 'default';

    public $size = 'default';

    /**
     * Initialise the component.
     *
     * @param  string  $tag
     */
    public function __construct($tag = 'button', $theme = 'default', $size = 'default')
    {
        $this->tag = $tag;
        $this->theme = $theme;
        $this->size = $size;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('tables::button');
    }
}
