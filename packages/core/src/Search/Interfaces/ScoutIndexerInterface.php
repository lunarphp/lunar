<?php

namespace Lunar\Core\Search\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface ScoutIndexerInterface
{
    public function searchableAs(Model $model): string;

    public function shouldBeSearchable(Model $model): bool;

    public function makeAllSearchableUsing(Builder $query): Builder;

    public function getScoutKey(Model $model): mixed;

    public function getScoutKeyName(Model $model): mixed;

    public function getSortableFields(): array;

    public function getFilterableFields(): array;

    /**
     * The columns Scout's database engine matches a text query against. That
     * engine reads the keys of toSearchableArray() as column names, so Lunar
     * returns these instead of the indexed document when it is the engine.
     *
     * @return list<string>
     */
    public function getDatabaseSearchColumns(Model $model): array;

    public function toSearchableArray(Model $model): array;
}
