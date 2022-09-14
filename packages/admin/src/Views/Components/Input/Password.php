<?php

namespace Lunar\Hub\Views\Components\Input;

class Password extends Text
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.input.password');
    }
}
