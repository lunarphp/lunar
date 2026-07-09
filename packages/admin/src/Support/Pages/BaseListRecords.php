<?php

namespace Lunar\Admin\Support\Pages;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Core\Models\Concerns\Searchable;
use Lunar\Filament\Support\Concerns\CallsHooks;

abstract class BaseListRecords extends ListRecords
{
    use CallsHooks;

    protected function getDefaultFooterWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return $this->callLunarHook('footerWidgets', $this->getDefaultFooterWidgets());
    }

    protected function getDefaultHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return $this->callLunarHook('headerActions', $this->getDefaultHeaderActions());
    }

    protected function getDefaultHeaderWidgets(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return $this->callLunarHook('headerWidgets', $this->getDefaultHeaderWidgets());
    }

    public function getDefaultHeading(): string
    {
        return $this->heading ?? $this->getTitle();
    }

    public function getHeading(): string|Htmlable
    {
        return $this->callLunarHook('heading', $this->getDefaultHeading(), $this->record ?? null);
    }

    public function getDefaultSubheading(): ?string
    {
        return $this->subheading;
    }

    public function getSubheading(): string|Htmlable|null
    {
        return $this->callLunarHook('subHeading', $this->getDefaultSubheading(), $this->record ?? null);
    }

    protected function paginateTableQuery(Builder $query): Paginator|CursorPaginator
    {
        $result = $this->callLunarHook('paginateTableQuery', $query, $this->getTableRecordsPerPage());

        return $result instanceof Builder ? parent::paginateTableQuery($result) : $result;
    }

    protected function getDefaultTabs(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return $this->callLunarHook('getTabs', $this->getDefaultTabs());
    }

    protected function applySearchToTableQuery(Builder $query): Builder
    {
        $scoutEnabled = config('lunar.admin.scout_enabled', false);
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
            $scoutQuery = static::getModel()::search($search);

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
