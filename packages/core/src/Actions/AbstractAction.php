<?php

namespace Lunar\Actions;

use Closure;

abstract class AbstractAction
{
    /**
     * The property to pass back to the callback
     */
    protected mixed $passThrough = null;

    public function then(Closure $callback)
    {
        return $callback($this->passThrough);
    }
}
