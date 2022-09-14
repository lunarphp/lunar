<?php

namespace Lunar\Hub\DataTransferObjects;

use Closure;

class TableColumn
{
    public function __construct(
        public string $heading,
        public bool $sortable = false,
        public ?Closure $callback = null
    ) {
        // ..
    }

    /**
     * Set the value for the table column with a closure.
     *
     * @param  Closure  $callback
     * @return void
     */
    public function value(Closure $callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Set whether the column should be sortable.
     *
     * @param  bool  $sortable
     * @return void
     */
    public function sortable(bool $sortable = true)
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * Get the value for the column.
     *
     * @param  mixed  $entity
     * @return void
     */
    public function getValue($entity)
    {
        return call_user_func($this->callback, $entity);
    }
}
