<?php

namespace GetCandy\Hub\Tables\Builders;

use GetCandy\Hub\Tables\TableBuilder;
use GetCandy\Models\Customer;

class CustomersTableBuilder extends TableBuilder
{
    /**
     * Return the query data.
     *
     * @param  string|null  $searchTerm
     * @param  array  $filters
     * @param  string  $sortField
     * @param  string  $sortDir
     * @return LengthAwarePaginator
     */
    public function getData(): iterable
    {
        $query = Customer::query();

        if ($this->searchTerm) {
            $query->whereIn('id', Customer::search($this->searchTerm)->keys());
        }

        return $query->paginate($this->perPage);
    }
}
