<?php

namespace GetCandy\Hub\Base;

use Closure;
use GetCandy\Hub\DataTransferObjects\TableColumn;
use GetCandy\Hub\DataTransferObjects\TableFilter;
use Illuminate\Support\Collection;

interface OrdersTableInterface
{
    /**
     * Add a table column.
     *
     * @param  string  $header
     * @return \GetCandy\Hub\DataTransferObjects\TableColumn
     */
    public function addColumn(string $header, bool $sortable = false, Closure $callback = null): TableColumn;

    /**
     * Add a filter.
     *
     * @param  string  $header
     * @param  string  $column
     * @param  Closure|null  $formatter
     * @return \GetCandy\Hub\DataTransferObjects\TableFilter
     */
    public function addFilter(string $header, string $column, Closure $formatter = null): TableFilter;

    /**
     * Return the table columns.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getColumns(): Collection;

    /**
     * Return the table filters.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFilters(): Collection;

    /**
     * Export the orders.
     *
     * @param  array  $orderIds
     * @return void
     */
    public function export($orderIds);

    /**
     * Set the exporter class.
     *
     * @param  string  $exporter
     * @return self
     */
    public function exportUsing($exporter): self;
}
