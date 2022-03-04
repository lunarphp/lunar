<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

trait HasSlots
{
    /**
     * List of registered slot classes
     *
     * @var array
     */
    public static $registeredSlots = [];

    public static function registerSlot($klass)
    {
        self::$registeredSlots[] = $klass;
    }
    
    /**
     * Get validation rules for slots.
     *
     * @return array
     */
    protected function hasSlotsValidationRules()
    {
        return $this->slots->map(function($slot) {
            return $slot->getValidationRules() ?? [];    
        })
            ->flatten(1)
            ->filter();
    }

    /**
     * Mount the component trait.
     *
     * @return void
     */
    public function mountHasSlots()
    {
        $model = $this->getSlotModel();
        
        $this->slots = collect(self::$registeredSlots)->map(function ($slot) use ($model) {
            return new $slot($model);
        });
    }
    
    /**
     * Abstract method to get the slot model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    abstract protected function getSlotModel();
    
    /**
     * Update all slots based on 
     *
     * @return void
     */
    public function updateSlots($model)
    {
        $this->slots->each(function ($slot) {
            $slot->handleSave($model);    
        });     
    }
}
