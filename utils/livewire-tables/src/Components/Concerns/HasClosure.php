<?php

namespace Lunar\LivewireTables\Components\Concerns;

use Closure;

trait HasClosure
{
    /**
     * The instance of the closure to render.
     *
     * @var Closure|null
     */
    public $closure = null;

    /**
     * Set the closure when returning the value of the column.
     */
    public function value(Closure $closure): self
    {
        $this->closure = $closure;

        return $this;
    }
}
