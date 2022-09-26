<?php

namespace Lunar\LivewireTables\View\Support;

use Illuminate\View\Component;

class Modal extends Component
{
    public $id = null;

    public $maxWidth = null;

    public function __construct($id = null, $maxWidth = null)
    {
        $this->id = $id;
        $this->maxWidth = $maxWidth;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('lt::support.modal');
    }
}
