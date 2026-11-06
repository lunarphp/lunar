<?php

namespace Lunar\SearchRelevance\Pipelines;

use Closure;
use Lunar\Search\Data\SearchResults;
use Lunar\Search\Pipelines\SearchResponse;

/**
 * A part-number search that found nothing in the exact-match fields reruns
 * as an ordinary search, so a code the indexer does not hold as exact (or
 * one typed with a typo) still finds its product. The rerun goes through
 * both pipelines itself, minus PartNumberRetrieval, so its results come back
 * ranked, stamped and logged. This stage then returns without calling the
 * rest of the results pipeline, so the empty search is not logged as well.
 */
class PartNumberFallback
{
    public function handle(SearchResponse $response, Closure $next): SearchResponse
    {
        $request = $response->request;
        $overrides = $request->context['relevance_part_number_overrides'] ?? null;

        // No overrides (the Database engine) means the rerun would be the
        // same query, so there is nothing to fall back to.
        if (! is_array($overrides) || $overrides['keys'] === [] || $response->results->count > 0) {
            return $next($response);
        }

        $engine = clone $request->engine;

        $engine->withoutParams(...$overrides['keys'])
            ->withParams($overrides['previous'])
            ->withoutPipelineStages(PartNumberRetrieval::class)
            ->page($request->requestedPage)
            ->perPage($request->requestedPerPage);

        $results = $engine->get();

        if (! $results instanceof SearchResults) {
            return $next($response);
        }

        $response->results = $results;

        return $response;
    }
}
