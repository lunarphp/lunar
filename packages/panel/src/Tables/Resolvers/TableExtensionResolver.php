<?php

namespace Lunar\Panel\Tables\Resolvers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Lunar\Panel\Tables\TableAction;
use Lunar\Panel\Tables\TableBulkAction;
use Lunar\Panel\Tables\TableColumn;
use Lunar\Panel\Tables\TableExtension;
use Lunar\Panel\Tables\TableFilter;

class TableExtensionResolver
{
    /** @var TableColumn[] */
    protected array $columns = [];

    /** @var TableFilter[] */
    protected array $filters = [];

    /** @var TableAction[] */
    protected array $actions = [];

    /** @var TableBulkAction[] */
    protected array $bulkActions = [];

    /** @var TableExtension[] */
    protected array $extensions = [];

    /** @param class-string<TableExtension>[] $extensionClasses */
    public function __construct(array $extensionClasses)
    {
        foreach ($extensionClasses as $class) {
            /** @var TableExtension $extension */
            $extension = app($class);

            $this->extensions[] = $extension;

            foreach ($extension->columns() as $columnClass) {
                $this->columns[] = app($columnClass);
            }

            foreach ($extension->filters() as $filterClass) {
                $this->filters[] = app($filterClass);
            }

            foreach ($extension->actions() as $actionClass) {
                $this->actions[] = app($actionClass);
            }

            foreach ($extension->bulkActions() as $bulkActionClass) {
                $this->bulkActions[] = app($bulkActionClass);
            }
        }
    }

    public function applySearchQueries(Builder $query, string $term): void
    {
        foreach ($this->extensions as $extension) {
            $extension->searchQuery($query, $term);
        }
    }

    public function applyColumnQueries(Builder $query): void
    {
        foreach ($this->columns as $column) {
            if ($column->visible()) {
                $column->query($query);
            }
        }
    }

    public function applyFilters(Builder $query, Request $request): void
    {
        foreach ($this->filters as $filter) {
            if (! $filter->visible()) {
                continue;
            }

            $value = $request->input("filter.{$filter->key()}");

            if ($value !== null && $value !== '') {
                $filter->query($query, $value);
            }
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function getColumns(): array
    {
        return collect($this->columns)
            ->filter(fn (TableColumn $column) => $column->visible())
            ->map(fn (TableColumn $column) => $column->toArray())
            ->values()
            ->all();
    }

    /** @return string[] */
    public function getColumnKeys(): array
    {
        return collect($this->columns)
            ->filter(fn (TableColumn $column) => $column->visible())
            ->map(fn (TableColumn $column) => $column->key())
            ->values()
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function getFilters(): array
    {
        return collect($this->filters)
            ->filter(fn (TableFilter $filter) => $filter->visible())
            ->map(fn (TableFilter $filter) => $filter->toArray())
            ->values()
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function getActions(): array
    {
        return collect($this->actions)
            ->filter(fn (TableAction $action) => $action->visible())
            ->map(fn (TableAction $action) => $action->toArray())
            ->values()
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function getBulkActions(): array
    {
        return collect($this->bulkActions)
            ->filter(fn (TableBulkAction $action) => $action->visible())
            ->map(fn (TableBulkAction $action) => $action->toArray())
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    public function getFilterValues(Request $request): array
    {
        $values = [];

        foreach ($this->filters as $filter) {
            if ($filter->visible()) {
                $values[$filter->key()] = $request->input("filter.{$filter->key()}", '');
            }
        }

        return $values;
    }
}
