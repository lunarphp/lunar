<?php

namespace Lunar\SearchRelevance\Pipelines;

use Closure;
use Illuminate\Contracts\Config\Repository;
use Lunar\Search\Pipelines\SearchRequest;
use Lunar\SearchRelevance\Contracts\QueryNormaliser;
use Lunar\SearchRelevance\Data\RankingContext;
use Lunar\SearchRelevance\Logging\SearchLogger;
use Lunar\SearchRelevance\RetrievalVersion;
use Lunar\SearchRelevance\Settings;

/**
 * Builds the RankingContext for the results stage and, when ranking applies,
 * swaps the requested page for the full candidate window.
 */
class WidenRequest
{
    public function __construct(
        protected QueryNormaliser $normaliser,
        protected Settings $settings,
        protected SearchLogger $logger,
        protected RetrievalVersion $version,
        protected Repository $config,
    ) {}

    public function handle(SearchRequest $request, Closure $next): SearchRequest
    {
        $engine = $request->engine;
        $modelType = $engine->getModelType();
        $mode = $this->settings->mode();

        if ($mode === 'off' || ! in_array($modelType, $this->config->get('lunar.search_relevance.models', []), true)) {
            return $next($request);
        }

        $rawQuery = (string) $engine->getQuery();
        $normalised = $this->normaliser->normalise($rawQuery);

        // Nothing to learn from a browse listing; this also keeps the learned
        // union's own fetch-by-id request out of the log.
        if ($normalised === '' || $normalised === '*') {
            return $next($request);
        }

        $context = new RankingContext(
            modelType: $modelType,
            normalisedQuery: $normalised,
            sessionId: $this->logger->sessionId(),
            customerId: $this->logger->customerId(),
            mode: $mode,
            sort: $engine->getSort() ?: null,
            filtersHash: md5(json_encode([$engine->getFilters(), $engine->getFacets()])),
            version: $this->version->current($modelType),
        );

        $request->context['relevance'] = $context;

        $window = (int) $this->config->get('lunar.search_relevance.window', 250);
        $isPartNumber = (bool) ($request->context['relevance_part_number'] ?? false);

        if ($context->shouldRank() && ! $isPartNumber && $request->requestedPage * $request->requestedPerPage <= $window) {
            $request->context['relevance_widened'] = true;
            $engine->page(1)->perPage($window);
        }

        return $next($request);
    }
}
