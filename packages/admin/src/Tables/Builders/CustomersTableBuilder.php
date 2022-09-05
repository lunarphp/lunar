<?php

namespace GetCandy\Hub\Tables\Builders;

use GetCandy\Hub\Tables\TableBuilder;
use GetCandy\Models\Customer;

class CustomersTableBuilder extends TableBuilder
{
    /**
     * Return the query data.
     *
     * @param string|null $searchTerm
     * @param Array $filters
     * @param string $sortField
     * @param string $sortDir
     *
     * @return LengthAwarePaginator
     */
    public function getData(): iterable
    {
        return Customer::paginate($this->perPage);
    }
}
