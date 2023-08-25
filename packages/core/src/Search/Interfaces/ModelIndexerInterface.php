<?php

namespace Lunar\Search\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface ModelIndexerInterface
{
    public function searchableAs(Model $model): string;

    public function shouldBeSearchable(Model $model): bool;

    public function makeAllSearchableUsing(Builder $query): Builder;

    public function getScoutKey(Model $model): mixed;

    public function getScoutKeyName(Model $model): mixed;

    public function getSortableFields(): array;

    public function getFilterableFields(): array;

    public function toSearchableArray(Model $model, string $engine): array;
}