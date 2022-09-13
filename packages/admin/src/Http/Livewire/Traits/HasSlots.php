<?php

namespace Lunar\Hub\Http\Livewire\Traits;

use Illuminate\Support\Arr;
use Lunar\Hub\Facades\Slot;

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
                if (! isset($this->slotStore[$slot->getSlotHandle()])) {
                    $this->saveSlotData($slot->getSlotHandle(), $slot->getSlotInitialValue());
                }

                return (object) [
                    'handle' => $slot->getSlotHandle(),
                    'title' => $slot->getSlotTitle(),
                    'position' => $slot->getSlotPosition(),
                    'component' => $slot->getName(),
                ];
            })
            ->groupBy('position')
            ->sortBy('handle')
            ->toArray();
        }

        return $this->slotsForOutput[$position] ?? [];
    }

    /**
     * Get slots errors by handle.
     *
     * @return array
     */
    public function getSlotErrorsByHandle($handle)
    {
        $handleSlotStore = Arr::get($this->slotStore, $handle, []);

        return Arr::get($handleSlotStore, 'errors', []);
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
                $store = Arr::get($this->slotStore, $slot->getSlotHandle(), []);
                $result = $slot->handleSlotSave($model, $store['data'] ?? []);
                if ($result) { // errors
                    $this->raiseSlotErrors($slot->getSlotHandle(), $result);
                }
            });

        $this->updateSlotModel();
    }

    private function ensureSlotStoreHandleExists($handle)
    {
        if (! Arr::get($this->slotStore, $handle)) {
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

    public function updateSlotModel()
    {
        $model = $this->getSlotModel();

        $this->emit('updateSlotModel', get_class($model), $model->getKey());
    }

    public function saveSlotData($handle, $data)
    {
        $this->ensureSlotStoreHandleExists($handle);
        $this->slotStore[$handle]['data'] = $data;
    }
}
