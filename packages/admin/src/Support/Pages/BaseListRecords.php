<?php

namespace Lunar\Admin\Support\Pages;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Base\Traits\Searchable;

abstract class BaseListRecords extends ListRecords
{
    use Concerns\ExtendsHeaderActions;
    use \Lunar\Admin\Support\Concerns\CallsHooks;

    protected function applySearchToTableQuery(Builder $query): Builder
    {
        $scoutEnabled = config('lunar.search.scout_enabled', false);
        $isScoutSearchable = in_array(Searchable::class, class_uses_recursive(static::getModel()));

        $this->applyColumnSearchesToTableQuery($query);

        if (! $scoutEnabled || ! $isScoutSearchable) {
            $this->applyGlobalSearchToTableQuery($query);
        }

        if (
            filled($search = $this->getTableSearch()) &&
            $scoutEnabled &&
            $isScoutSearchable
        ) {
            $query->whereIn(
                'id',
                collect(static::getModel()::search($search)->keys())->map(
                    fn ($result) => str_replace(static::getModel().'::', '', $result)
                )
            );
        }

        return $query;
    }
}
