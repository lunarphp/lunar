<?php

namespace GetCandy\Hub\DataTransferObjects;

use Closure;
use GetCandy\Models\Order;

class TableColumn
{
    public function __construct(
        public string $heading,
        public bool $sortable = false,
        public ?Closure $callback = null
    ) {
        // ..
    }

    public function value(Closure $callback)
    {
        $this->callback = $callback;
        return $this;
    }

    public function sortable(bool $sortable = true)
    {
        $this->sortable = $sortable;
        return $this;
    }

    public function getValue(Order $order)
    {
        return call_user_func($this->callback, $order);
    }
}
