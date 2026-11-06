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
                'session_id' => ['nullable', 'string', 'max:64'],
            ]);
        } catch (ValidationException) {
            return response()->noContent();
        }

        $productId = (int) $data['product_id'];
        $position = (int) $data['position'];
        $search = SearchQuery::query()->find($data['search_id']);

        $window = (int) $this->config->get('lunar.search_relevance.guards.event_window_minutes', 120);

        if (! $search || ! RecordEvent::accepts($search, $productId, $position, $window)) {
            return response()->noContent();
        }

        $source = $data['source'] ?? 'organic';
        $sessionId = $data['session_id'] ?? $this->logger->sessionId();

        $this->bus->dispatch(new RecordEvent($data['search_id'], $productId, $position, 'click', $source, $sessionId));

        $this->attribution->remember($productId, $data['search_id'], $position, $source, $sessionId);

        return response()->noContent();
    }
}
