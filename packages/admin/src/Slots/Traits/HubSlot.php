<?php

namespace Lunar\Hub\Slots\Traits;

trait HubSlot
{
    /**
     * The model the slot is handling.
     *
     * @var string
     */
    public $slotModel;

    public function initializeHubSlot()
    {
        $this->listeners = array_merge($this->listeners, [
            'updateSlotModel' => 'updateSlotModel',
        ]);
    }

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
     * Update the model we are affecting.
     *
     * @param  string  $modelClass
     * @param  mixed  $modelKey
     * @return self
     */
    public function updateSlotModel($modelClass, $modelKey)
    {
        $this->slotModel = $modelClass::find($modelKey);

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
