<?php
namespace Lunar\Base\Traits;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait HasPosition
{

    final public static function initializeHasPosition(): void
    {
        static::saving(function (Model $model) {
            if (
                $model->isDirty($model->positionUniqueConstraints())
                || !(intval($model->position) > 0)
                || $model->query()
                    ->where($model->getKeyName(), '!=', $model->getKey())
                    ->wherePosition(
                        $model->position,
                        $model->getAttributes()
                    )
                    ->exists()
            ) {
                $model->position = $model->query()
                    ->where($model->getKeyName(), '!=', $model->getKey())
                    ->wherePositionUniqueConstraints(
                        $model->getAttributes()
                    )
                    ->max('position') + 1;
            }
        });
    }

    final public function positionUniqueConstraints(): array
    {
        $constraints = ['position'];

        if (!property_exists($this, 'positionUniqueConstraints') 
            || !is_array($this->positionUniqueConstraints)) 
        {
            return $constraints;
        }

        return array_merge($this->positionUniqueConstraints, $constraints);
    }

    final public function scopeWherePosition(Builder $query, int $position, array|Collection $constraints = []): void
    {
        $query
            ->where('position', $position)
            ->wherePositionUniqueConstraints($constraints);
    }

    final public function scopeWherePositionUniqueConstraints(Builder $query, array|Collection $constraints = []): void
    {
        $constraints = collect($constraints)->except('position');
        $modelConstraints = collect($this->positionUniqueConstraints())->reject('position');

        if (count($modelConstraints) && !$constraints->hasAny($modelConstraints->toArray())) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Position constraints "%s" for "%s" not defined!',
                    $modelConstraints->diff($constraints)->join('", "', '" and "'),
                    get_class($this)
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

    public function scopePosition(Builder $query, int $position, ...$constraints): void
    {
        $query->wherePosition($position, $constraints);
    }

}