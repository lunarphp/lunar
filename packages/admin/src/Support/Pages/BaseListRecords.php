<?php

namespace Lunar\Admin\Support\Pages;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Base\Traits\Searchable;

abstract class BaseListRecords extends ListRecords
{
    use Concerns\ExtendsFooterWidgets;
    use Concerns\ExtendsHeaderActions;
    use Concerns\ExtendsHeaderWidgets;
    use Concerns\ExtendsHeadings;
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
            $ids = collect(static::getModel()::search($search)->keys())->map(
                fn ($result) => str_replace(static::getModel().'::', '', $result)
            );

            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            $query->whereIn(
                'id',
                $ids
            );

            $query->when(
                ! $ids->isEmpty(),
                fn ($query) => $query->orderByRaw("field(id, {$placeholders})", $ids->toArray()) // TODO: Only supports MySQL
            );
        }

        return $query;
    }

    protected function paginateTableQuery(Builder $query): Paginator
    {
        return $query->paginate($this->getTableRecordsPerPage());
    }
}
