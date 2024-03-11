<?php

namespace Lunar\Pipelines\CartPrune;

use Closure;
use Illuminate\Database\Eloquent\Builder;

final class PruneAfter
{
    public function handle(Builder $query, Closure $next)
    {
        $days = config('lunar.cart.prune_tables.prune_interval', 90);

        $query->where('updated_at', '<=', now()->subDays($days))
            ->whereDoesntHave('lines', function ($query) use ($days) {
                $query->where('updated_at', '>', now()->subDays($days));
            });

        return $next($query);
    }
}
