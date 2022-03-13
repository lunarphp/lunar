<?php

namespace GetCandy\Hub\Slots\Traits;

use Illuminate\View\View;

trait HubSlot
{
    /**
     * The model the slot is handling.
     *
     * @var string
     */
    protected $slotModel;

    /**
     * The menu slot handle.
     *
     * @var string
     */
    protected $slotHandle;

    /**
     * The menu slot position.
     *
     * @var string
     */
    protected $slotPosition;

    /**
     * The menu slot title.
     *
     * @var string
     */
    protected $slotTitle;

    /**
     * Set the model we are affecting.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return self
     */
    public function setSlotModel($model)
    {
        $this->slotModel = $model;

        return $this;
    }

    /**
     * Save the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  mixed  $data
     * @return void
     */
    public function handleSlotSave($model, $data)
    {
    }

    /**
     * Render the slot.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
    }

    /**
     * Get the handle of the slot.
     *
     * @return string
     */
    public function getSlotHandle()
    {
        return $this->slotHandle;
    }

    /**
     * Get the position in the view where the slot appears.
     *
     * @return string
     */
    public function getSlotPosition()
    {
        return $this->slotPosition;
    }

    /**
     * Get the title of the slot.
     *
     * @return string
     */
    public function getSlotTitle()
    {
        return $this->slotTitle;
    }

    /**
     * Save slot data in parent component
     *
     * @param  mixed $data
     * @return string
     */
    protected function saveSlotData($data)
    {
        $this->emit('saveSlotData', $this->getSlotHandle(), $data);
    }

    /**
     * Raise slot errors in parent
     *
     * @param  mixed $data
     * @return string
     */
    protected function raiseSlotErrors($errors)
    {
        $this->emit('raiseSlotErrors', $this->getSlotHandle(), $errors);
    }
}
