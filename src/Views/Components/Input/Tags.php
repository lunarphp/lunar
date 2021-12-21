<?php

namespace GetCandy\Hub\Views\Components\Input;

use Illuminate\View\Component;

class Tags extends Component
{
    /**
     * Whether or not the input has an error to show.
     *
     * @var bool
     */
    public bool $error = false;

    public array $tags = [];

    /**
     * Initialise the component.
     *
     * @param  bool  $error
     */
    public function __construct($error = false, $tags = [])
    {
        $this->error = $error;
        $this->tags = $tags;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.input.tags');
    }
}
