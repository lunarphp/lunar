<?php

namespace Lunar\LivewireTables\Support;

use Lunar\LivewireTables\Components\Actions\Action;
use Lunar\LivewireTables\Components\Actions\BulkAction;
use Lunar\LivewireTables\Components\Columns\BaseColumn;
use Lunar\LivewireTables\Components\Filters\BaseFilter;
use Illuminate\Support\Collection;

interface TableBuilderInterface
{
    /**
     * Apply the sort field and direction to the builder.
     *
     * @param  string  $sortField
     * @param  string  $sortDir
     * @return self
     */
    public function sort($sortField, $sortDir = 'desc'): self;

    /**
     * Set the search term on the table builder.
     *
     * @param  string  $searchTerm
     * @return self
     */
    public function searchTerm($searchTerm): self;

    /**
     * Set the results limit on the table builder.
     *
     * @param  int  $limit
     * @return self
     */
    public function perPage(int $perPage): self;

    /**
     * Add a column to the table builder.
     *
     * @param  BaseColumn  $column
     * @return self
     */
    public function addColumn(BaseColumn $column): self;

    /**
     * Add multiple columns to the table builder.
     *
     * @param  iterable  $columns
     * @return self
     */
    public function addColumns(iterable $columns): self;

    /**
     * Set the base columns that the table builder needs.
     *
     * @param  iterable  $columns
     * @return self
     */
    public function baseColumns(iterable $columns): self;

    /**
     * Return the columns for the table builder.
     *
     * @return Collection
     */
    public function getColumns(): Collection;

    /**
     * Add a filter to the table builder.
     *
     * @param  BaseFilter  $filter
     * @return self
     */
    public function addFilter(BaseFilter $filter): self;

    /**
     * Return the filters for the table builder.
     *
     * @return Collection
     */
    public function getFilters(): Collection;

    /**
     * Add an action to the table builder.
     *
     * @param  Action  $action
     * @return self
     */
    public function addAction(Action $action): self;

    /**
     * Return the actions for the table builder.
     *
     * @return Collection
     */
    public function getActions(): Collection;

    /**
     * Add a bulk action to the table builder.
     *
     * @param  BulkAction  $bulkAction
     * @return self
     */
    public function addBulkAction(BulkAction $bulkAction): self;

    /**
     * Return the bulk actions for the table builder.
     *
     * @return Collection
     */
    public function getBulkActions(): Collection;

    /**
     * Get the data from the table builder.
     *
     * @return iterable
     */
    public function getData(): iterable;
}
