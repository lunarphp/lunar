<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

use GetCandy\Hub\Facades\Slot;

trait HasSlots
{    
    /**
     * Get validation rules for slots.
     *
     * @param  string  $context
     * @return array
     */
    protected function hasSlotsValidationRules($context)
    {
        return $this->getSlotsForContext($context)
            ->map(function($slot) {
                return $slot->getValidationRules() ?? [];    
            })
            ->flatten(1)
            ->filter();
    }
    
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

        $this->getSlotsForContext($context)
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
