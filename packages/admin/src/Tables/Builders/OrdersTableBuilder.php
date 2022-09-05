<?php

namespace GetCandy\Hub\Tables\Builders;

use GetCandy\Hub\Tables\TableBuilder;
use GetCandy\Models\Order;

class OrdersTableBuilder extends TableBuilder
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
    public function getData($searchTerm = null, $filters = [], $sortField = 'placed_at', $sortDir = 'desc')
    {
        $query = Order::with([
            'shippingLines',
            'billingAddress',
            'currency',
            'customer'
        ])->orderBy($sortField, $sortDir);

        if ($searchTerm) {
            $query->whereIn('id', Order::search($searchTerm)->keys());
        }

        $filters = collect($filters)->filter(function ($value) {
            return !!$value;
        });

        foreach ($this->queryExtenders as $qe) {
            call_user_func($qe, $query, $searchTerm, $filters);
        }

        // Get the table filters we want to apply.
        $tableFilters = $this->getFilters()->filter(function ($filter) use ($filters) {
            return $filters->has($filter->field);
        });


        foreach ($tableFilters as $filter) {
            call_user_func($filter->getQuery(), $filters, $query);
        }

        return $query->paginate(25);
    }
}
