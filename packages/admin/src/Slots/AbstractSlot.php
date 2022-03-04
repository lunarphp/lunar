<?php

namespace GetCandy\Hub\Slots;

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
     * The menu slot title.
     *
     * @var string
     */
    protected $title;

    /**
     * Initialise the class.
     *
     * @param  string  $model
     */
    public function __construct($model)
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
     * @return string
     */
    public function render()
    {
        return '';
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
     * Get the title of the slot.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
