<?php

namespace Lunar\SearchRelevance\Pipelines;

use Closure;
use Illuminate\Contracts\Config\Repository;
use Lunar\Search\Engines\MeilisearchEngine;
use Lunar\Search\Engines\TypesenseEngine;
use Lunar\Search\Pipelines\SearchRequest;
use Lunar\SearchRelevance\Contracts\QueryNormaliser;

/**
 * Part-number queries retrieve from the SKU fields only, with no typo
 * tolerance and no semantic padding. The Database engine has no parameters
 * to override, so it passes through untouched.
 */
class PartNumberRetrieval
{
    public function __construct(
        protected QueryNormaliser $normaliser,
        protected Repository $config,
    ) {}

    public function handle(SearchRequest $request, Closure $next): SearchRequest
    {
        $query = (string) $request->engine->getQuery();

        if ($query === '' || ! $this->normaliser->isPartNumber($query)) {
            return $next($request);
        }

        $request->context['relevance_part_number'] = true;

        if ($request->engine instanceof TypesenseEngine) {
            $request->engine->withParams([
                'query_by' => 'skus,skus_normalised',
                'query_by_weights' => null,
                'prefix' => true,
                'infix' => 'always,always',
                'num_typos' => '0,0',
                'drop_tokens_threshold' => 0,
                'vector_query' => null,
            ]);
        }

        if ($request->engine instanceof MeilisearchEngine) {
            $params = [
                'attributesToSearchOn' => ['skus', 'skus_normalised'],
                'matchingStrategy' => 'all',
            ];

            if ($this->config->get('lunar.search.meilisearch.embedder')) {
                $params['hybrid'] = ['semanticRatio' => 0];
            }

            $request->engine->withParams($params);
        }

        return $next($request);
    }
}
