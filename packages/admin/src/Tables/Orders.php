<?php

namespace GetCandy\Hub\Tables;

use GetCandy\Hub\Base\OrdersTableInterface;
use GetCandy\Hub\DataTransferObjects\TableColumn;
use GetCandy\Hub\DataTransferObjects\TableFilter;
use GetCandy\Models\Order;
use Illuminate\Support\Collection;
use MeiliSearch\Endpoints\Indexes;

class Orders implements OrdersTableInterface
{
    /**
     * A collection of columns
     *
     * @var Collection
     */
    protected Collection $columns;

    /**
     * A collection of filters
     *
     * @var Collection
     */
    protected Collection $filters;

    public function __construct()
    {
        $this->columns = collect();
        $this->filters = collect();
    }

    public function addColumn(string $header)
    {
        $this->columns->push(
            $column = new TableColumn($header)
        );

        return $column;
    }

    public function addFilter(string $header, string $column)
    {
        $this->filters->push(
            $filter = new TableFilter($header, $column)
        );
        return $filter;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getFilters()
    {
        return $this->filters;
    }
}
