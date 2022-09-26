<?php

namespace Lunar\LivewireTables\Components;

use Livewire\Component;

class Head extends Component
{
    /**
     * Whether the column should be sortable.
     *
     * @var bool
     */
    public bool $sortable = false;

    /**
     * The sort direction.
     *
     * @var string
     */
    public $sortDir = null;

    /**
     * The current sort field.
     *
     * @var string
     */
    public $sortField = null;

    /**
     * The heading for the column.
     *
     * @var string
     */
    public $heading;

    public $field;

    public function sort()
    {
        $this->sortField = $this->field;

        if ($this->sortDir == 'desc') {
            $this->sortDir = null;
            $this->sortField = null;
            $this->emitUp('sort', null);

            return;
        }

        $this->sortDir = $this->sortDir == 'asc' ? 'desc' : 'asc';

        $this->emitUp('sort', "{$this->field}:{$this->sortDir}");
    }

    public function render()
    {
        return view('lt::head');
    }
}
