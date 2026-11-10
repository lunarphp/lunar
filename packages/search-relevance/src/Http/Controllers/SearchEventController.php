<?php

namespace Lunar\SearchRelevance\Http\Controllers;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Lunar\SearchRelevance\Jobs\RecordEvent;
use Lunar\SearchRelevance\Logging\SearchLogger;
use Lunar\SearchRelevance\Models\SearchQuery;
use Lunar\SearchRelevance\Support\Attribution;

/**
 * Records a click on a search result. Always answers 204, including on bad
 * input, so a bot cannot probe which searches or products exist.
 */
class SearchEventController
{
    public function __construct(
        protected Dispatcher $bus,
        protected Attribution $attribution,
        protected SearchLogger $logger,
        protected Repository $config,
    ) {}

    public function __invoke(Request $request): Response
    {
        try {
            $data = $request->validate([
                'search_id' => ['required', 'string', 'size:26'],
                'product_id' => ['required', 'integer'],
                'position' => ['required', 'integer', 'min:1'],
                'source' => ['nullable', 'in:organic,learned,explore'],
            ]);
        } catch (ValidationException) {
            return response()->noContent();
        }

        $productId = (int) $data['product_id'];
        $position = (int) $data['position'];
        $search = SearchQuery::query()->find($data['search_id']);

        $window = (int) $this->config->get('lunar.search_relevance.guards.event_window_minutes', 120);

        // A search not written yet (its row is logged by a queued job) is
        // checked by the event job, which retries until the row exists.
        if ($search && ! RecordEvent::accepts($search, $productId, $position, $window)) {
            return response()->noContent();
        }

        $source = $data['source'] ?? 'organic';
        // The shopper who made the search, never one the client names: a
        // client choosing its own session could pose as several shoppers.
        $sessionId = $search?->session_id ?? $this->logger->sessionId();

        $this->bus->dispatch(new RecordEvent($data['search_id'], $productId, $position, 'click', $source, $sessionId));

        $this->attribution->remember($productId, $data['search_id'], $position, $source, $sessionId);

        return response()->noContent();
    }
}
