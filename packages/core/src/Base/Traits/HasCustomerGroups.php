<?php

namespace Lunar\Base\Traits;

use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Lunar\Models\CustomerGroup;

trait HasCustomerGroups
{
    use CanScheduleAvailability;

    /**
     * Get the relationship for the customer groups.
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    abstract public function customerGroups(): Relation;

    /**
     * Schedule models against customer groups.
     *
     * @param  mixed  $models
     * @param  DateTime|null  $starts
     * @param  DateTime|null  $ends
     * @param  array  $pivotData
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
     * @param  array  $pivotData
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

    /**
     * Apply customer group scope.
     *
     * @param Builder $query
     * @param Collection $customerGroups
     *
     * @return void
     */
    public function applyCustomerGroupScope(Builder $query, $customerGroups)
    {
        $query->whereHas('customerGroups', function ($relation) use ($customerGroups) {
            $relation->whereIn(
                $this->customerGroups()->getTable() . '.customer_group_id',
                $customerGroups->pluck('id')
            )->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })->where(function ($query) {
                $query->whereNull('ends_at')
                ->orWhere('ends_at', '>=', now());
            })->whereEnabled(true)->whereVisible(true);
        });
    }

    /**
     * Apply the customer group scope
     *
     * @param Builder $query
     * @param CustomerGroup|string $customerGroup
     *
     * @return Builder
     */
    public function scopeCustomerGroup($query, $customerGroup = null)
    {
        if (!$customerGroup) {
            return $query;
        }

        if (is_a($customerGroup, CustomerGroup::class)) {
            $customerGroup = collect([$customerGroup]);
        }

        return $this->applyCustomerGroupScope($query, $customerGroup);
    }
}
