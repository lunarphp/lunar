<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

use GetCandy\Hub\Facades\Slot;
use Illuminate\Support\Str;

trait HasSlots
{
    protected $slotsForOutput;
    public $slotStore = [];

    public function getHasSlotsListeners()
    {
        return [
            'saveSlotData' => 'saveSlotData',
            'raiseSlotErrors' => 'raiseSlotErrors',
        ];
    }

    /**
     * Get slots to be output in a given position on the page.
     *
     * @return array
     */
    public function getSlotsByPosition($position)
    {
        if (! isset($this->slotsForOutput)) {
            $this->slotsForOutput = $this->getSlots()
            ->map(function ($slot) {
                $slotComponentName = (string) Str::of(get_class($slot))->afterLast('\\')->snake()->replace('_', '-');

                return (object) [
                    'handle' => $slot->getSlotHandle(),
                    'title' => $slot->getSlotTitle(),
                    'position' => $slot->getSlotPosition(),
                    'component' => $slotComponentName,
                ];
            })
            ->groupBy('position')
            ->toArray();
        }

        return $this->slotsForOutput[$position] ?? [];
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
     * Update all slots.
     *
     * @param  string  $context
     * @return void
     */
    public function updateSlots()
    {
        $model = $this->getSlotModel();

        $this->getSlots()
            ->each(function ($slot) use ($model) {
                $store = array_get($this->slotStore, $slot->getSlotHandle(), []);
                $slot->handleSlotSave($model, $store['data'] ?? []);
            });
    }

    private function ensureSlotStoreHandleExists($handle)
    {
        if (! array_get($this->slotStore, $handle)) {
            $this->slotStore[$handle] = [
                'errors' => [],
                'data' => [],
            ];
        }
    }

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

    public function raiseSlotErrors($handle, $errors)
    {
        $this->ensureSlotStoreHandleExists($handle);
        $this->slotStore[$handle]['errors'] = $errors;
    }

    public function saveSlotData($handle, $data)
    {
        $this->ensureSlotStoreHandleExists($handle);
        $this->slotStore[$handle]['data'] = $data;
    }
}
