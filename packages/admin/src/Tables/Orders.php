<?php

namespace GetCandy\Hub\Tables;

use GetCandy\Hub\DataTransferObjects\TableColumn;
use GetCandy\Hub\Base\OrdersTableInterface;
use Illuminate\Support\Collection;

class Orders implements OrdersTableInterface
{
    /**
     * A collection of columns
     *
     * @var Collection
     */
    protected Collection $columns;

    public function __construct()
    {
        $this->columns = collect();
    }

    public function addColumn(string $header)
    {
        $this->columns->push(
            $column = new TableColumn($header)
        );

        return $column;
    }

    public function getColumns()
    {
        return $this->columns;
    }
}
