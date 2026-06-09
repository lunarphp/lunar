<?php

namespace Lunar\Core\Pipelines\CartPrune;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class WithoutOrders
{
    public function handle(Builder $query, Closure $next)
    {
        $query->whereDoesntHave('orders');

        return $next($query);
    }
}
