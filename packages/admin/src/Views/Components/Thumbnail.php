<?php

namespace Lunar\Hub\Views\Components;

use Illuminate\View\Component;

class Thumbnail extends Component
{
    /**
     * The src URL of the image.
     *
     * @var string
     */
    public $src = '';

    /**
     * Initialise the component.
     *
     * @param  string  $src
     */
    public function __construct($src = null)
    {
        $this->src = $src;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.thumbnail');
    }
}
