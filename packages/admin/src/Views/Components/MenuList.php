<?php

namespace Lunar\Hub\Views\Components;

use Illuminate\View\Component;

class MenuList extends Component
{
    public $sections;

    public $items;

    public $active;

    public function __construct($sections, $items, $active)
    {
        $this->sections = $sections;

        $this->items = $items;

        $this->active = $active;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.menu-list');
    }
}
