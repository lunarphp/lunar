<?php

namespace Lunar\LivewireTables\Support;

use Illuminate\Support\Collection;
use Lunar\LivewireTables\Components\Actions\Action;
use Lunar\LivewireTables\Components\Actions\BulkAction;
use Lunar\LivewireTables\Components\Columns\BaseColumn;
use Lunar\LivewireTables\Components\Filters\BaseFilter;

interface TableBuilderInterface
{
    /**
     * Apply the sort field and direction to the builder.
     *
     * @param  string  $sortField
     * @param  string  $sortDir
     */
    public function sort($sortField, $sortDir = 'desc'): self;

    /**
     * Set the search term on the table builder.
     *
     * @param  string  $searchTerm
     */
    public function searchTerm($searchTerm): self;

    /**
     * Set the results limit on the table builder.
     *
     * @param  int  $limit
     */
    public function perPage(int $perPage): self;

    /**
     * Add a column to the table builder.
     */
    public function addColumn(BaseColumn $column): self;

    /**
     * Add multiple columns to the table builder.
     */
    public function addColumns(iterable $columns): self;

    /**
     * Set the base columns that the table builder needs.
     */
    public function baseColumns(iterable $columns): self;

    /**
     * Return the columns for the table builder.
     */
    public function getColumns(): Collection;

    /**
     * Add a filter to the table builder.
     */
    public function addFilter(BaseFilter $filter): self;

    /**
     * Return the filters for the table builder.
     */
    public function getFilters(): Collection;

    /**
     * Add an action to the table builder.
     */
    public function addAction(Action $action): self;

    /**
     * Return the actions for the table builder.
     */
    public function getActions(): Collection;

    /**
     * Add a bulk action to the table builder.
     */
    public function addBulkAction(BulkAction $bulkAction): self;

    /**
     * Return the bulk actions for the table builder.
     */
    public function getBulkActions(): Collection;

    /**
     * Get the data from the table builder.
     */
    public function getData(): iterable;
}
