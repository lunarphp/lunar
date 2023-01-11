<?php

namespace Lunar\Hub\Views\Components\Menus\AppSide;

use Illuminate\View\Component;
use Lunar\Hub\Menu\MenuSection;

class Section extends Component
{
    public MenuSection $section;

    public $active = false;

    public $current = null;

    public function __construct(MenuSection $section, $current = null)
    {
        $this->section = $section;
        $this->current = $current;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.menus.app-side.section');
    }
}
