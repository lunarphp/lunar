<?php

namespace GetCandy\Hub\Slots;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SlotRegistry
{
    /**
     * The slots which are currently registered.
     *
     * @var array
     */
    protected $slots = [];

    /**
     * The component we want to show slots for.
     *
     * @var string
     */
    protected $component;

    /**
     * Initialise the class.
     */
    public function __construct()
    {
    }

    /**
     * Register a slot against a specific handle.
     *
     * @param  string  $handle
     * @param  class  $slot
     * @return void
     */
    public function register($handle, $slot): void
    {
        if (! array_key_exists($handle, $this->slots)) {
            $this->slots[$handle] = [];
        }

        $this->slots[$handle][] = $slot;
    }

    /**
     * What handle do we want slots for?
     *
     * @param  string  $handle
     * @return self
     */
    public function for($handle): self
    {
        $this->component = $handle;

        return $this;
    }

    /**
     * Get slots for the current component.
     *
     * @param  Model  $model
     * @return \Illuminate\Support\Collection
     */
    public function get(Model $model): Collection
    {
        return collect($this->slots[$this->component] ?? [])->map(function ($class) use ($model) {
            return app($class)->setModel($model);
        });
    }
}
