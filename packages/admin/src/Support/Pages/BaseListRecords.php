<?php

namespace Lunar\Admin\Support\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Admin\Support\Concerns\CallsHooks;
use Lunar\Admin\Support\Pages\Concerns\ExtendsFooterWidgets;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeaderActions;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeaderWidgets;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeadings;
use Lunar\Admin\Support\Pages\Concerns\ExtendsTablePagination;
use Lunar\Admin\Support\Pages\Concerns\ExtendsTabs;
use Lunar\Base\Traits\Searchable;

abstract class BaseListRecords extends ListRecords
{
    use CallsHooks;
    use ExtendsFooterWidgets;
    use ExtendsHeaderActions;
    use ExtendsHeaderWidgets;
    use ExtendsHeadings;
    use ExtendsTablePagination;
    use ExtendsTabs;

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
                ->firstWhere(fn ($filter) => $filter instanceof TrashedFilter);

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
