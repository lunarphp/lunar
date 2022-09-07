<?php

namespace GetCandy\Hub\Tables\Builders;

use GetCandy\Hub\Tables\TableBuilder;
use GetCandy\Models\Order;

class OrdersTableBuilder extends TableBuilder
{
    /**
     * The field to sort using.
     *
     * @var string|null
     */
    public ?string $sortField = 'placed_at';

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
        $query = Order::with([
            'shippingLines',
            'billingAddress',
            'currency',
            'customer',
        ])->orderBy($this->sortField, $this->sortDir);

        if ($this->searchTerm) {
            $query->whereIn('id', Order::search($this->searchTerm)->keys());
        }

        $filters = collect($this->queryStringFilters)->filter(function ($value) {
            return (bool) $value;
        });

        foreach ($this->queryExtenders as $qe) {
            call_user_func($qe, $query, $this->searchTerm, $filters);
        }

        // Get the table filters we want to apply.
        $tableFilters = $this->getFilters()->filter(function ($filter) use ($filters) {
            return $filters->has($filter->field);
        });

        foreach ($tableFilters as $filter) {
            if ($closure = $filter->getQuery()) {
                call_user_func($filter->getQuery(), $filters, $query);
            }
        }

        return $query->paginate($this->perPage);
    }
}
