<?php

namespace Lunar\Base\Traits;

use DateTime;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Lunar\Exceptions\SchedulingException;

trait CanScheduleAvailability
{
    /**
     * Return whether or not the models provided are suitable for scheduling.
     *
     * @return void
     */
    abstract protected function validateScheduling(Collection $models);

    /**
     * Schedule models for a given relation.
     */
    protected function schedule(
        Relation $relation,
        mixed $models,
        ?DateTime $starts = null,
        ?DateTime $ends = null,
        array $pivotData = []
    ): void {
        // Convert to collection if it's an array
        $models = is_array($models) ? collect($models) : $models;

        if (! is_iterable($models)) {
            $models = collect([$models]);
        }

        if (! $this->validateScheduling($models)) {
            throw new SchedulingException;
        }

        $relation->syncWithoutDetaching(
            $this->getScheduleMapping($models, array_merge([
                'starts_at' => $starts,
                'ends_at' => $ends,
                'enabled' => true,
            ], $pivotData))
        );
    }

    /**
     * Unschedule models for a relation.
     */
    protected function unschedule(Relation $relation, mixed $models, array $pivotData = []): void
    {
        // Convert to collection if it's an array
        $models = is_array($models) ? collect($models) : $models;

        if (! is_iterable($models)) {
            $models = collect([$models]);
        }

        if (! $this->validateScheduling($models)) {
            throw new SchedulingException;
        }

        $relation->syncWithoutDetaching(
            $this->getScheduleMapping($models, array_merge([
                'starts_at' => null,
                'ends_at' => null,
                'enabled' => false,
            ], $pivotData))
        );
    }

    /**
     * Returns the data for the sync update.
     */
    private function getScheduleMapping(Collection $models, ?array $pivotData = null): Collection
    {
        return $models->mapWithKeys(function ($model) use ($pivotData) {
            return [
                (is_int($model) ? $model : $model->id) => $pivotData,
            ];
        });
    }
}
