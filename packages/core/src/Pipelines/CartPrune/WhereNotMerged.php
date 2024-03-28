<?php

namespace Lunar\Pipelines\CartPrune;

use Closure;
use Illuminate\Database\Eloquent\Builder;

final class WhereNotMerged
{
    public function handle(Builder $query, Closure $next)
    {
        $query->unmerged();

        return $next($query);
    }
}
