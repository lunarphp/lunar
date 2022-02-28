<?php

namespace GetCandy\Hub\Tables;

use Closure;
use GetCandy\Hub\Base\OrdersTableInterface;
use GetCandy\Hub\DataTransferObjects\TableColumn;
use GetCandy\Hub\DataTransferObjects\TableFilter;
use GetCandy\Hub\Exporters\OrderExporter;
use Illuminate\Support\Collection;

class Orders implements OrdersTableInterface
{
    /**
     * A collection of columns.
     *
     * @var Collection
     */
    protected Collection $columns;

    /**
     * A collection of filters.
     *
     * @var Collection
     */
    protected Collection $filters;

    /**
     * The class reference to the exporter.
     *
     * @var string
     */
    protected $exporter = OrderExporter::class;

    public function __construct()
    {
        $this->columns = collect();
        $this->filters = collect();
    }

    /**
     * Add a table column.
     *
     * @param  string  $header
     * @return \GetCandy\Hub\DataTransferObjects\TableColumn
     */
    public function addColumn(string $header)
    {
        $this->columns->push(
            $column = new TableColumn($header)
        );

        return $column;
    }

    /**
     * Add a filter.
     *
     * @param  string  $header
     * @param  string  $column
     * @param  Closure|null  $formatter
     * @return \GetCandy\Hub\DataTransferObjects\TableFilter
     */
    public function addFilter(string $header, string $column, Closure $formatter = null)
    {
        $this->filters->push(
            $filter = new TableFilter($header, $column, $formatter)
        );

        return $filter;
    }

    /**
     * Return the table columns.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Return the table filters.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Export the orders.
     *
     * @param  array  $orderIds
     * @return void
     */
    public function export($orderIds)
    {
        return app($this->exporter)->export($orderIds);
    }

    /**
     * Set the exporter class.
     *
     * @param  string  $exporter
     * @return self
     */
    public function exportUsing($exporter)
    {
        $this->exporter = $exporter;

        return $this;
    }
}
