<?php

namespace Lunar\Hub\Views\Components;

use Illuminate\View\Component;

class Slideover extends Component
{
    public $title = '';

    public $nested = false;

    public $form = true;

    public function __construct($title = '', $nested = false, $form = null)
    {
        $this->title = $title;
        $this->nested = $nested;
        $this->form = $form;
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
