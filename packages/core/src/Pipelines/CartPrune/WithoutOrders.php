<?php

namespace Lunar\Pipelines\CartPrune;

use Closure;
use Illuminate\Database\Eloquent\Builder;

final class WithoutOrders
{
    public function handle(Builder $query, Closure $next)
    {
        $query->whereDoesntHave('orders');

        return $next($query);
    }
}
