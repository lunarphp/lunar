<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

use GetCandy\Hub\Facades\Slot;

trait HasSlots
{
    protected $slotsForOutput;

    /**
     * Get slots to be output in a given position on the page
     *
     * @return array
     */
    public function getSlotsByLocation($location)
    {
        if (! isset($this->slotsForOutput)) {

            $this->slotsForOutput = $this->getSlots()
            ->map(function ($slot) {
                return (object) [
                    'handle' => $slot->getHandle(),
                    'errors' => $slot->getErrors(),
                    'title' => $slot->getTitle(),
                    'location' => $slot->getLocation(),
                    'render' => $slot->render(),
                ];
            })
            ->groupBy('location')
            ->toArray();

        }

        return $this->slotsForOutput[$location] ?? [];
    }

    /**
     * Get validation rules for slots.
     *
     * @return array
     */
    protected function hasSlotsValidationRules()
    {
        return $this->getSlots()
            ->map(function ($slot) {
                return $slot->getValidationRules() ?? [];
            })
            ->filter()
            ->toArray();
    }

    /**
     * Get the contexts for slots.
     *
     * @return array
     */
    abstract protected function getSlotContexts();

    /**
     * Abstract method to get the slot model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    abstract protected function getSlotModel();

    /**
     * Update all slots
     *
     * @param  string  $context
     * @return void
     */
    public function updateSlots()
    {
        $model = $this->getSlotModel();

        $this->getSlots()
            ->each(function ($slot) use ($model) {
                $slot->handleSave($model);
            });
    }

    /**
     * Utility function to get slots for a given context, e.g. 'product.create'.
     */
    private function getSlots()
    {
        return $this->getSlotsGroupedByContext()
            ->flatten();
    }

    private function getSlotsGroupedByContext()
    {
        $model = $this->getSlotModel();

        return collect($this->getSlotContexts())
            ->map(function ($context) use ($model) {
                return $this->getSlotsForContext($context, $model);
            });
    }

    private function getSlotsForContext($context)
    {
        return Slot::for($context)
            ->get($this->getSlotModel());
    }
}
