<?php

namespace Lunar\Core\Pipelines\CartPrune;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class WhereNotMerged
{
    public function handle(Builder $query, Closure $next)
    {
        $query->unmerged();

        return $next($query);
    }
}
