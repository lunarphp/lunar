<?php

namespace Lunar\Base\Traits;

use DateTime;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Lunar\Models\CustomerGroup;

trait HasCustomerGroups
{
    use CanScheduleAvailability;

    /**
     * Get the relationship for the customer groups.
     */
    abstract public function customerGroups(): Relation;

    /**
     * Schedule models against customer groups.
     *
     * @param  mixed  $models
     * @return void
     */
    public function scheduleCustomerGroup(
        $models,
        DateTime $starts = null,
        DateTime $ends = null,
        array $pivotData = []
    ) {
        $this->schedule(
            $this->customerGroups(),
            $models,
            $starts,
            $ends,
            $pivotData
        );
    }

    /**
     * Unschedule models against a customer group.
     *
     * @param  mixed  $models
     * @return void
     */
    public function unscheduleCustomerGroup(
        $models,
        array $pivotData = []
    ) {
        $this->unschedule(
            $this->customerGroups(),
            $models,
            $pivotData
        );
    }

    protected function validateScheduling(Collection $models)
    {
        foreach ($models as $model) {
            if (is_object($model) && ! ($model instanceof CustomerGroup)) {
                return false;
            }
        }
    }
}
