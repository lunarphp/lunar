<?php

namespace Lunar\LivewireTables\Support;

use Illuminate\Support\Collection;
use Lunar\LivewireTables\Components\Actions\Action;
use Lunar\LivewireTables\Components\Actions\BulkAction;
use Lunar\LivewireTables\Components\Columns\BaseColumn;
use Lunar\LivewireTables\Components\Filters\BaseFilter;

class TableBuilder implements TableBuilderInterface
{
    /**
     * The columns available to the table.
     *
     * @var Collection
     */
    public Collection $columns;

    /**
     * The base columns set on the table.
     *
     * @var Collection
     */
    public Collection $baseColumns;

    /**
     * The filters available to the table.
     *
     * @var Collection
     */
    public Collection $filters;

    /**
     * The actions available to the table.
     *
     * @var Collection
     */
    public Collection $actions;

    /**
     * The bulk actions available.
     *
     * @var Collection
     */
    public Collection $bulkActions;

    /**
     * The search term for the table.
     *
     * @var string|null
     */
    public ?string $searchTerm = null;

    /**
     * The number of records per page.
     *
     * @var int
     */
    public int $perPage = 50;

    /**
     * The field to sort using.
     *
     * @var string|null
     */
    public ?string $sortField = 'created_at';

    /**
     * The sort direction.
     *
     * @var string|null
     */
    public ?string $sortDir = 'desc';

    /**
     * The filters from the query string
     *
     * @var array
     */
    public array $queryStringFilters = [];

    /**
     * The empty message.
     *
     * @var string|null
     */
    public ?string $emptyMessage = '';

    /**
     * Initialise the TableBuilder
     */
    public function __construct()
    {
        $this->columns = collect();
        $this->baseColumns = collect();
        $this->filters = collect();
        $this->actions = collect();
        $this->bulkActions = collect();
    }

    public function perPage(int $perPage): self
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function queryStringFilters($filters)
    {
        $this->queryStringFilters = $filters;

        return $this;
    }

    public function searchTerm($searchTerm): self
    {
        $this->searchTerm = $searchTerm;

        return $this;
    }

    public function sort($sortField, $sortDir = 'desc'): self
    {
        $this->sortField = $sortField;
        $this->sortDir = $sortDir;

        return $this;
    }

    public function addColumn(BaseColumn $column): self
    {
        $this->columns->prepend($column);

        return $this;
    }

    public function addColumns(iterable $columns): self
    {
        $this->columns = $this->columns->merge($columns);

        return $this;
    }

    public function baseColumns(iterable $columns): self
    {
        $this->baseColumns = collect($columns);

        return $this;
    }

    public function getColumns(): Collection
    {
        return $this->resolveColumnPositions(
            $this->baseColumns,
            $this->columns,
        );
    }

    public function addFilter(BaseFilter $filter): self
    {
        $this->filters->push($filter);

        return $this;
    }

    public function getFilters(): Collection
    {
        return $this->filters;
    }

    public function addAction(Action $action): self
    {
        $this->actions->push($action);

        return $this;
    }

    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function addBulkAction(BulkAction $bulkAction): self
    {
        $this->bulkActions->push($bulkAction);

        return $this;
    }

    public function getBulkActions(): Collection
    {
        return $this->bulkActions;
    }

    public function getData(): iterable
    {
        return collect();
    }

    protected function resolveColumnPositions(Collection $existing, Collection $incoming)
    {
        foreach ($incoming as $column) {
            if (! $column->after) {
                $existing->push($column);

                continue;
            }

            $position = $existing->search(function ($existing) use ($column) {
                return $existing->field == $column->after;
            });

            if (! is_null($position)) {
                $existing->splice($position + 1, 0, [$column]);
            } else {
                $existing->push($column);
            }
        }

        return $existing;
    }
}
