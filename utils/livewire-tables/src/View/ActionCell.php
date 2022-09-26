<?php

namespace Lunar\LivewireTables\View;

use Illuminate\Support\Collection;
use Illuminate\View\Component;

class ActionCell extends Component
{
    public $actions;

    public $record;

    /**
     * Create the component instance.
     *
     * @param  bool  $sortable
     * @param  null|string  $direction
     * @param  null|string  $multiColumn
     */
    public function __construct(Collection $actions, $record)
    {
        $this->actions = $actions;
        $this->record = $record;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('lt::action-cell');
    }
}
