<?php

namespace Lunar\Actions;

use Closure;

abstract class AbstractAction
{
    /**
     * The property to pass back to the callback
     */
    protected mixed $passThrough = null;

    /**
     * E.g. MyAction::make();
     */
    public static function make()
    {
        return app(static::class);
    }

    /**
     * E.g. MyAction::run($item1, $item2);
     */
    public static function run(...$arguments)
    {
        return static::make()->execute(...$arguments);
    }

    public function then(Closure $callback)
    {
        return $callback($this->passThrough);
    }
}
