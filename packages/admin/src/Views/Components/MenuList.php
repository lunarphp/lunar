<?php

namespace GetCandy\Hub\Views\Components;

use Illuminate\Support\Collection;
use Illuminate\View\Component;

class MenuList extends Component
{
    /**
     * @param  string  $menuType
     * @param  \Illuminate\Support\Collection  $sections
     * @param  \Illuminate\Support\Collection  $items
     * @param  string  $active
     */
    public function __construct(
        public string $menuType,
        public Collection $sections,
        public Collection $items,
        public string $active
    ) {
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
