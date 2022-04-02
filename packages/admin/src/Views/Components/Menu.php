<?php

namespace GetCandy\Hub\Views\Components;

use GetCandy\Hub\Facades\Menu as MenuFacade;
use Illuminate\View\Component;

class Menu extends Component
{
    public $handle;

    public $sections;

    public $items;

    public function __construct($handle = null)
    {
        $slot = MenuFacade::slot($handle);
        $this->items = $slot->getItems();
        $this->sections = $slot->getSections();

        $this->items = collect($this->items)->sortBy('position');
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.menu');
    }
}
