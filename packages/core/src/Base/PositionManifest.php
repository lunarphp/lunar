<?php

namespace Lunar\Base;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;

class PositionManifest implements PositionManifestInterface
{
    /**
     * {@inheritDoc}
     */
    public function saving(Model $model): void 
    {
        if (
            $model->isDirty($this->constraints($model)) 
            || !(intval($model->position) > 0)
            || $model->query()
                ->where($model->getKeyName(), '!=', $model->getKey())
                ->wherePosition($model->position)
                ->wherePositionUniqueConstraints($model->getAttributes())
                ->exists()
        ) {
            $model->position = $model->query()
                ->where($model->getKeyName(), '!=', $model->getKey())
                ->wherePositionUniqueConstraints($model->getAttributes())
                ->max('position') + 1;
        }
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function constraints(Model $model): array
    {
        return array_merge(
            !property_exists($model, 'positionUniqueConstraints')
                || !is_array($model->positionUniqueConstraints)
                ? [] : $model->positionUniqueConstraints,
            ['position']
        );
    }

    /**
     * {@inheritDoc}
     */
    public function query(Builder $query, int $position,  array $constraints = []): void
    {
        $query
            ->wherePosition($position)
            ->wherePositionUniqueConstraints($constraints);
    }

    /**
     * {@inheritDoc}
     */
    public function queryPosition(Builder $query, int $position): void
    {
        $query->where('position', $position);
    }

    /**
     * {@inheritDoc}
     */
    public function queryUniqueConstraints(Builder $query, array $constraints): void
    {
        $constraints = collect($constraints);
        $modelConstraints = collect($this->constraints($query->getModel()))->reject('position');

        if (count($modelConstraints) && !$constraints->hasAny($modelConstraints->toArray())) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Position constraints "%s" for "%s" not defined!',
                    $modelConstraints->diff($constraints)->join('", "', '" and "'),
                    get_class($query->getModel())
                )
            );
        }

        $modelConstraints->each(
            function ($attribute) use ($query, $constraints) {
                if (method_exists($query, Str::camel('scope_' . $attribute))) {
                    $method = Str::camel($attribute);
                } else {
                    $method = Str::camel('where_' . $attribute);
                }
                $query->{$method}($constraints[$attribute]);
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function registerBlueprintMacros(): void
    {
        /**
         * Add a `position` column to the table and define a unique positions index
         * with the given constraints
         * 
         * The constraints can defined as array or assigned from the corsponding 
         * model property `positionUniqueConstraints` if model object or  
         * model classname is given. 
         * 
         * @param array|string|\Illuminate\Database\Eloquent\Model $constraints
         * @return void
         */
        Blueprint::macro('position', function (array|string|Model $constraints = []) {
            /** @var Blueprint $this */
            if (is_string($constraints)) {
                $constraints = app($constraints);
            }
            if (!is_array($constraints)) {
                $constraints = \Lunar\Facades\PositionManifest::constraints($constraints);
            } else {
                $constraints = collect($constraints)
                    ->push('position')
                    ->unique()
                    ->all();
            }
            $this->unsignedBigInteger('position');
            $index = strtolower($this->prefix . $this->table . '_position_unique');
            $this->unique($constraints, $index);
        });

        /**
         * Remove the `position` column to the table and drop the unique positions index
         * 
         * @param array|string|\Illuminate\Database\Eloquent\Model $constraints
         * @return void
         */
        Blueprint::macro('dropPosition', function () {
            /** @var Blueprint $this */
            $index = strtolower($this->prefix . $this->table . '_position_unique');
            $this->dropUnique($index);
            $this->dropColumn('position');
        });
    }
}