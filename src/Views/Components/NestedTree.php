<?php

namespace GetCandy\Hub\Views\Components;

use Illuminate\Support\Collection;
use Illuminate\View\Component;

class NestedTree extends Component
{
    public $tree;

    public $sortGroup = null;

    public $owner;

    public function __construct(Collection $tree, $sortGroup = null, $owner = null)
    {
        $this->tree = $tree;
        $this->sortGroup = $sortGroup;
        $this->owner = $owner;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.nested-tree');
    }
}
