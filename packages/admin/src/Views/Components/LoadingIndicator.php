<?php

namespace GetCandy\Hub\Views\Components;

use Illuminate\View\Component;

class LoadingIndicator extends Component
{
    public function render()
    {
        return view('adminhub::components.loading-indicator');
    }
}
