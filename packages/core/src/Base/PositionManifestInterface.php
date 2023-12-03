<?php
namespace Lunar\Base;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface PositionManifestInterface
{
    /**
     * Before model gets saved, check if relevant model attributes has changed 
     * and position value is greate than zero and unique and if not, set position 
     * to next greatest value
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function saving(Model $model): void;

    /**
     * Return the defined constraints from model's positionUniqueConstraints 
     * property array 
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return array
     */
    public function constraints(Model $model): array;

    /**
     * Scope the query to only include given position and constraints
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $position
     * @param array $constraints
     * @return void
     */
    public function query(Builder $query, int $position, array $constraints = []): void;

    /**
     * Scope the query to only include given position
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $position
     * @return void
     */
    public function queryPosition(Builder $query, int $position): void;

    /**
     * Scope the query to only include given constraints
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|\Illuminate\Support\Collection $constraints
     * @return void
     */
    public function queryUniqueConstraints(Builder $query, array $constraints): void;

    /**
     * Regster blueprint macros to allow ease 
     * ddefinition and removement from `position` columne
     * @return void
     */
    public static function registerBlueprintMacros(): void;
}