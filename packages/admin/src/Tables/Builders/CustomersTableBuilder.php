<?php

namespace Lunar\Hub\Tables\Builders;

use Lunar\Hub\Tables\TableBuilder;
use Lunar\Models\Customer;

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
            $query->whereIn('id', Customer::search($this->searchTerm)
                ->query(fn ($query) => $query->select('id'))
                ->options([
                    'hitsPerPage' => 500,
                ])->keys());
        }

        return $query->paginate($this->perPage);
    }
}
