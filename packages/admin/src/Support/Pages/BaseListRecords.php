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
            $trashedFilter = collect($this->getTable()->getFilters())
                ->firstWhere(fn ($filter) => $filter instanceof \Filament\Tables\Filters\TrashedFilter);

            $scoutQuery = static::getModel()::search($search);

            if (filled($state = $trashedFilter?->getState()['value'] ?? null)) {
                $state ? $scoutQuery->withTrashed() : $scoutQuery->onlyTrashed();
            }

            $ids = collect($scoutQuery->take(100)->keys())->map(
                fn ($result) => str_replace(static::getModel().'::', '', $result)
            );

            $query
                ->whereIn('id', $ids)
                ->orderBySequence($ids);
        }

        return $query;
    }
}
