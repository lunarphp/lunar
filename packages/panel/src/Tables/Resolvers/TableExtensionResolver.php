<?php

namespace Lunar\Panel\Tables\Resolvers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Lunar\Panel\Support\OrderResolver;
use Lunar\Panel\Support\Position;
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

    /**
     * @param  class-string<TableExtension>[]  $extensionClasses
     * @param  Authenticatable|null  $user  The panel user visibility checks run against.
     */
    public function __construct(array $extensionClasses, protected ?Authenticatable $user = null)
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
            if ($column->visible($this->user)) {
                $column->query($query);
            }
        }
    }

    public function applyFilters(Builder $query, Request $request): void
    {
        foreach ($this->filters as $filter) {
            if (! $filter->visible($this->user)) {
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
            ->filter(fn (TableColumn $column) => $column->visible($this->user))
            ->map(fn (TableColumn $column) => $column->toArray())
            ->values()
            ->all();
    }

    /**
     * Merge the page's first-party columns with visible add-on columns and order
     * the combined set by Position. First-party columns keep their declared order
     * (assigned ascending priority); add-on columns can anchor before/after a
     * first-party key, which only resolves because both sets are ordered together.
     *
     * @param  array<int, array{key: string, label: string, width?: string, align?: string}>  $firstParty
     * @return array<int, array<string, mixed>>
     */
    public function mergeAndOrderColumns(array $firstParty): array
    {
        $entries = [];

        foreach (array_values($firstParty) as $index => $column) {
            $entries[] = [
                'key' => $column['key'],
                'position' => Position::priority(($index + 1) * 10),
                'payload' => $column,
            ];
        }

        foreach ($this->columns as $column) {
            if (! $column->visible($this->user)) {
                continue;
            }

            $entries[] = [
                'key' => $column->key(),
                'position' => $column->position(),
                'payload' => array_filter([
                    'key' => $column->key(),
                    'label' => $column->header(),
                    'component' => $column->component(),
                    'type' => $column->type()?->toArray(),
                ], fn (mixed $value) => $value !== null),
            ];
        }

        $ordered = (new OrderResolver)->sort(
            $entries,
            fn (array $entry): string => $entry['key'],
            fn (array $entry): Position => $entry['position'],
        );

        return array_map(fn (array $entry) => $entry['payload'], $ordered);
    }

    /** @return string[] */
    public function getColumnKeys(): array
    {
        return collect($this->columns)
            ->filter(fn (TableColumn $column) => $column->visible($this->user))
            ->map(fn (TableColumn $column) => $column->key())
            ->values()
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function getFilters(): array
    {
        $visible = array_values(array_filter(
            $this->filters,
            fn (TableFilter $filter) => $filter->visible($this->user),
        ));

        $ordered = (new OrderResolver)->sort(
            $visible,
            fn (TableFilter $filter): string => $filter->key(),
            fn (TableFilter $filter): Position => $filter->position(),
        );

        return array_map(fn (TableFilter $filter) => $filter->toArray(), $ordered);
    }

    /**
     * Static row-action descriptors (key, label, icon, method, confirmation,
     * primary, position), ordered by Position. Per-row target URLs are resolved
     * separately by {@see resolveRowActionUrls()}.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getActions(): array
    {
        $visible = array_values(array_filter(
            $this->actions,
            fn (TableAction $action) => $action->visible($this->user),
        ));

        $ordered = (new OrderResolver)->sort(
            $visible,
            fn (TableAction $action): string => $action->key(),
            fn (TableAction $action): Position => $action->position(),
        );

        return array_map(fn (TableAction $action) => $action->toArray(), $ordered);
    }

    /**
     * Resolve each visible row action's target URL for a single record. Actions
     * with no URL (e.g. component-rendered) are omitted.
     *
     * @return array<string, string>
     */
    public function resolveRowActionUrls(mixed $record): array
    {
        $urls = [];

        foreach ($this->actions as $action) {
            if (! $action->visible($this->user)) {
                continue;
            }

            $url = $action->url($record);

            if ($url !== null) {
                $urls[$action->key()] = $url;
            }
        }

        return $urls;
    }

    /** @return array<int, array<string, mixed>> */
    public function getBulkActions(): array
    {
        $visible = array_values(array_filter(
            $this->bulkActions,
            fn (TableBulkAction $action) => $action->visible($this->user),
        ));

        $ordered = (new OrderResolver)->sort(
            $visible,
            fn (TableBulkAction $action): string => $action->key(),
            fn (TableBulkAction $action): Position => $action->position(),
        );

        return array_map(fn (TableBulkAction $action) => $action->toArray(), $ordered);
    }

    /** @return array<string, mixed> */
    public function getFilterValues(Request $request): array
    {
        $values = [];

        foreach ($this->filters as $filter) {
            if ($filter->visible($this->user)) {
                $values[$filter->key()] = $request->input("filter.{$filter->key()}", '');
            }
        }

        return $values;
    }
}
