<?php

namespace GetCandy\Hub\Slots\Traits;

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
     * Save slot data in parent component.
     *
     * @param  mixed  $data
     * @return string
     */
    protected function saveSlotData($data)
    {
        $this->emitUp('saveSlotData', $this->getSlotHandle(), $data);
    }

    /**
     * Raise slot errors in parent.
     *
     * @param  mixed  $data
     * @return string
     */
    protected function raiseSlotErrors($errors)
    {
        $this->emitUp('raiseSlotErrors', $this->getSlotHandle(), $errors);
    }
}
