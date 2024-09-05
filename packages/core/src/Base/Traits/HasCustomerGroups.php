<?php

namespace Lunar\Base\Traits;

use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Lunar\Models\Contracts\CustomerGroup as CustomerGroupContract;
use Lunar\Models\CustomerGroup;

trait HasCustomerGroups
{
    use CanScheduleAvailability;

    /**
     * Get the relationship for the customer groups.
     */
    abstract public function customerGroups(): Relation;

    public static function getExtraCustomerGroupPivotValues(CustomerGroupContract $customerGroup): array
    {
        return [
        ];
    }

    public static function bootHasCustomerGroups(): void
    {
        static::created(function (Model $model) {
            $model->customerGroups()->sync(
                CustomerGroup::modelClass()::get()->mapWithKeys(
                    fn ($customerGroup): array => [$customerGroup->id => [
                        'enabled' => $customerGroup->default,
                        'starts_at' => now(),
                        'ends_at' => null,
                        'visible' => $customerGroup->default,
                        ...static::getExtraCustomerGroupPivotValues($customerGroup),
                    ]]
                )
            );
        });
    }

    /**
     * Schedule models against customer groups.
     *
     * @param  mixed  $models
     * @return void
     */
    public function scheduleCustomerGroup(
        $models,
        ?DateTime $starts = null,
        ?DateTime $ends = null,
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

    /**
     * Apply customer group scope.
     *
     * @param  Collection  $customerGroups
     * @return Builder
     */
    public function applyCustomerGroupScope(Builder $query, Collection $groupIds, DateTime $startsAt, DateTime $endsAt)
    {
        return $query->whereHas('customerGroups', function ($relation) use ($groupIds, $startsAt, $endsAt) {
            $relation->whereIn(
                $this->customerGroups()->getTable().'.customer_group_id',
                $groupIds
            )->where(function ($query) use ($startsAt) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $startsAt);
            })->where(function ($query) use ($endsAt) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $endsAt);
            })->where(function ($query) {
                $query->where('enabled', '=', true)
                    ->orWhere('visible', '=', true);
            });
        });
    }

    /**
     * Apply the customer group scope
     *
     * @param  Builder  $query
     * @param  CustomerGroupContract|string  $customerGroup
     * @return Builder
     */
    public function scopeCustomerGroup($query, CustomerGroupContract|iterable|null $customerGroup = null, ?DateTime $startsAt = null, ?DateTime $endsAt = null)
    {
        if (blank($customerGroup)) {
            return $query;
        }

        $groupIds = collect();

        if (is_a($customerGroup, CustomerGroup::modelClass())) {
            $groupIds = collect([$customerGroup->id]);
        }

        if (is_a($customerGroup, Collection::class)) {
            $groupIds = $customerGroup->pluck('id');
        }

        if (is_array($customerGroup)) {
            $groupIds = collect($customerGroup)->pluck('id');
        }

        if (! $startsAt) {
            $startsAt = now();
        }

        if (! $endsAt) {
            $endsAt = now()->addSecond();
        }

        return $this->applyCustomerGroupScope($query, $groupIds, $startsAt, $endsAt);
    }
}
