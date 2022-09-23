<?php

namespace Lunar\Hub\Views\Components\Branding;

use Illuminate\View\Component;

class Favicon extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.branding.favicon');
    }
}
