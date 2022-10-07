<?php

namespace Lunar\Hub\Views\Components\Table;

use Illuminate\View\Component;

class NoResults extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.table.no-results');
    }
}
