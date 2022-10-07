<?php

namespace Lunar\LivewireTables\Components\Concerns;

trait HasSortableColumns
{
    /**
     * The field to sort on.
     *
     * @var string|null
     */
    public $sortField = null;

    /**
     * The sort direction.
     *
     * @var string|null
     */
    public $sortDir = null;

    /**
     * Apply the sorting to the query string.
     *
     * @param  array|null  $event
     * @return void
     */
    public function sort($event)
    {
        if ($event) {
            [$sortField, $sortDir] = explode(':', $event);
            $this->sortField = $sortField;
            $this->sortDir = $sortDir;
        } else {
            $this->sortField = null;
            $this->sortDir = null;
        }
    }
}
