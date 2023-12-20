<?php
namespace Lunar\Base\Traits;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lunar\Facades\PositionManifest;

trait HasPosition
{
    /**
     * Initialize the HasPosition trait and register the saving event
     * 
     * @return void
     */
    public static function initializeHasPosition(): void
    {
        static::saving(fn (Model $model) => PositionManifest::saving($model));
    }

    /**
     * Scope the query to only include given position
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $position
     * @return void
     */
    final public function scopeWherePosition(Builder $query, int $position): void
    {
        PositionManifest::queryPosition($query, $position);
    }

    /**
     * Scope the query to only include given constraints
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|\Illuminate\Support\Collection $constraints
     * @return void
     */
    final public function scopeWherePositionUniqueConstraints(Builder $query, array|Collection $constraints = []): void
    {
        PositionManifest::queryUniqueConstraints($query, $constraints);
    }

    /**
     * Scope the query to only include given position and constraints
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $position
     * @param array $constraints
     * @return void
     */
    public function scopePosition(Builder $query, int $position, ...$constraints): void
    {
        PositionManifest::query($query, $position, $constraints);
    }
}