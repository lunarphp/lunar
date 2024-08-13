<?php

namespace Lunar\Admin\Support\Pages;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Base\Traits\Searchable;

abstract class BaseListRecords extends ListRecords
{
    use Concerns\ExtendsFooterWidgets;
    use Concerns\ExtendsHeaderActions;
    use Concerns\ExtendsHeaderWidgets;
    use Concerns\ExtendsHeadings;
    use Concerns\ExtendsTablePagination;
    use Concerns\ExtendsTabs;
    use \Lunar\Admin\Support\Concerns\CallsHooks;

    protected function applySearchToTableQuery(Builder $query): Builder
    {
        $scoutEnabled = config('lunar.panel.scout_enabled', false);
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
            $ids = collect(static::getModel()::search($search)->take(100)->keys())->map(
                fn ($result) => str_replace(static::getModel().'::', '', $result)
            );

            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            $query->whereIn(
                'id',
                $ids
            );

            $query->when(
                ! $ids->isEmpty(),
                fn ($query) => $query->orderBySequence($ids->toArray())
            );
        }

        return $query;
    }
}
