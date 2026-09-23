<?php

namespace Lunar\SearchRelevance\Pipelines;

use Closure;
use Illuminate\Contracts\Config\Repository;
use Lunar\Search\Engines\MeilisearchEngine;
use Lunar\Search\Engines\TypesenseEngine;
use Lunar\Search\Pipelines\SearchRequest;
use Lunar\SearchRelevance\Contracts\QueryNormaliser;

/**
 * Part-number queries retrieve from the model indexer's exact-match fields
 * only, with no typo tolerance and no semantic padding. The indexer decides
 * which fields hold codes (a store that searches supplier references adds
 * them there), so a model whose indexer declares none is searched as usual.
 * The Database engine has no parameters to override, so it passes through
 * untouched. PartNumberFallback reruns the search without these overrides
 * when they match nothing.
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

        if ($this->config->get('lunar.search_relevance.mode', 'shadow') === 'off'
            || $query === ''
            || ! $this->normaliser->isPartNumber($query)) {
            return $next($request);
        }

        $fields = $this->exactMatchFields($request->engine->getModelType());

        if ($fields === []) {
            return $next($request);
        }

        $params = [];

        if ($request->engine instanceof TypesenseEngine) {
            $params = [
                'query_by' => implode(',', $fields),
                'query_by_weights' => null,
                'prefix' => true,
                'infix' => implode(',', array_fill(0, count($fields), 'always')),
                'num_typos' => implode(',', array_fill(0, count($fields), '0')),
                'drop_tokens_threshold' => 0,
                'vector_query' => null,
            ];
        }

        if ($request->engine instanceof MeilisearchEngine) {
            $params = [
                'attributesToSearchOn' => $fields,
                'matchingStrategy' => 'all',
            ];

            if ($this->config->get('lunar.search.meilisearch.embedder')) {
                $params['hybrid'] = ['semanticRatio' => 0];
            }
        }

        $request->context['relevance_part_number'] = true;
        $request->context['relevance_part_number_overrides'] = [
            'keys' => array_keys($params),
            'previous' => array_intersect_key($request->engine->getParams(), $params),
        ];

        if ($params !== []) {
            $request->engine->withParams($params);
        }

        return $next($request);
    }

    /** @return array<int, string> */
    protected function exactMatchFields(string $modelType): array
    {
        $model = new $modelType;

        if (! method_exists($model, 'indexer')) {
            return [];
        }

        $indexer = $model->indexer();

        return method_exists($indexer, 'getExactMatchFields')
            ? array_values($indexer->getExactMatchFields())
            : [];
    }
}
