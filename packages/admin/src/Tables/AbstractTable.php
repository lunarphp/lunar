<?php

namespace Lunar\Hub\Tables;

use Closure;
use Illuminate\Support\Collection;
use Lunar\Hub\Base\OrdersTableInterface;
use Lunar\Hub\DataTransferObjects\TableColumn;
use Lunar\Hub\DataTransferObjects\TableFilter;
use Lunar\Hub\Exporters\OrderExporter;

abstract class AbstractTable implements OrdersTableInterface
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
     * {@inheritDoc}
     */
    public function addColumn(string $header, bool $sortable = false, Closure $callback = null): TableColumn
    {
        $this->columns->push(
            $column = new TableColumn($header, $sortable, $callback)
        );

        return $column;
    }

    /**
     * {@inheritDoc}
     */
    public function addFilter(string $header, string $column, Closure $formatter = null): TableFilter
    {
        $this->filters->push(
            $filter = new TableFilter($header, $column, $formatter)
        );

        return $filter;
    }

    /**
     * {@inheritDoc}
     */
    public function getColumns(): Collection
    {
        return $this->columns;
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters(): Collection
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
     * {@inheritDoc}
     */
    public function exportUsing($exporter): self
    {
        $this->exporter = $exporter;

        return $this;
    }
}
