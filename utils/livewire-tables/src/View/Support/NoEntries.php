<?php

namespace Lunar\LivewireTables\View\Support;

use Illuminate\View\Component;

class NoEntries extends Component
{
    public $message = null;

    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('tables::support.no-entries');
    }
}
