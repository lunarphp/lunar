<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

use GetCandy\Hub\Facades\Slot;

trait HasSlots
{
    /**
     * Mount the component trait.
     *
     * @return void
     */
    public function mountHasSlots()
    {
        $model = $this->getSlotModel();

        $this->slots = collect($this->getSlotContexts())
            ->map(function ($context) use ($model) {
                return $this->getSlotsForContext($context, $model);
            });

        $this->slotsByLocation = $this->slots->flatten()
            ->groupBy(function ($slot) {
                return $slot->getLocation();
            });
    }

    /**
     * Get validation rules for slots.
     *
     * @param  string  $context
     * @return array
     */
    protected function hasSlotsValidationRules($context)
    {
        return $this->slots->get($context, collect([]))
            ->map(function($slot) {
                return $slot->getValidationRules() ?? [];
            })
            ->flatten(1)
            ->filter()
            ->toArray();
    }

    /**
     * Get the contexts for slots
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
     * Update all slots for a given context
     *
     * @param  string  $context
     * @return void
     */
    public function updateSlots($context)
    {
        $model = $this->getSlotModel();

        $this->slots->get($context, collect([]))
            ->each(function ($slot) use ($model) {
                $slot->handleSave($model);
            });
    }

    /**
     * Utility function to get slots for a given context, e.g. 'product.create'
     */
    private function getSlotsForContext($context)
    {
        return Slot::for($context)
            ->get($this->getSlotModel());
    }
}
