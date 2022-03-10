<?php

namespace GetCandy\Hub\Slots;

use Illuminate\View\View;

abstract class AbstractSlot
{
    /**
     * The model the slot is handling
     *
     * @var string
     */
    protected $model;
    
    /**
     * The menu slot handle.
     *
     * @var string
     */
    protected $handle;
    
    /**
     * The menu slot location.
     *
     * @var string
     */
    protected $location;
    
    /**
     * The menu slot title.
     *
     * @var string
     */
    protected $title;

    /**
     * Set the model we are affecting
     *
     * @param  string  $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * Get validation rules the slot required
     *
     * @return array
     */
    public function getValidationRules()
    {
        return [];
    }

    /**
     * Save the model
     *
     * @param  mixed  $model
     * @return void
     */
    public function handleSave(mixed $model)
    {
    }

    /**
     * Render the slot
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
    }

    /**
     * Get any errors
     *
     * @return \Illuminate\Support\Collection
     */
    public function getErrors()
    {
        return collect([]);
    }

    /**
     * Get the handle of the slot.
     *
     * @return string
     */
    public function getHandle()
    {
        return $this->handle;
    }
    
    /**
     * Get the location on the view where the slot appears
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }
    
    /**
     * Get the title of the slot.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
