<?php

namespace Lunar\SearchRelevance\Http\Controllers;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Lunar\SearchRelevance\Events\Attribution;
use Lunar\SearchRelevance\Events\RecordEvent;
use Lunar\SearchRelevance\Logging\SearchLogger;
use Lunar\SearchRelevance\Models\SearchQuery;

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

        if (! $search || ! RecordEvent::accepts($search, $productId, $position)) {
            return response()->noContent();
        }

        $source = $data['source'] ?? 'organic';
        $sessionId = $data['session_id'] ?? $this->logger->sessionId();

        $this->bus->dispatch(new RecordEvent($data['search_id'], $productId, $position, 'click', $source, $sessionId));

        $this->attribution->remember($productId, $data['search_id'], $position, $source, $sessionId);

        return response()->noContent();
    }
}
