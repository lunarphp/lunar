<?php

namespace Lunar\SearchRelevance\Logging;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Lunar\Core\Contracts\CartSession;
use Lunar\Core\Contracts\StorefrontSession;
use Lunar\SearchRelevance\Data\RankingContext;

/** Resolves who is searching and hands the logged search to the queue. */
class SearchLogger
{
    public function __construct(
        protected CartSession $cartSession,
        protected StorefrontSession $storefrontSession,
        protected Repository $config,
        protected Dispatcher $bus,
        protected ?Session $session = null,
        protected ?Request $request = null,
    ) {}

    /**
     * Whether this request's searches are worth learning from. Crawlers are
     * neither logged nor ranked. Sessionless (headless API) clients are kept:
     * they identify the shopper explicitly on the events endpoint.
     */
    public function shouldLog(): bool
    {
        if (! $this->request) {
            return true;
        }

        $agent = mb_strtolower((string) $this->request->userAgent());

        foreach ($this->config->get('lunar.search_relevance.guards.ignored_user_agents', []) as $needle) {
            if ($needle !== '' && str_contains($agent, mb_strtolower($needle))) {
                return false;
            }
        }

        return true;
    }

    /**
     * The shopper identifier: `cart:{id}` when session_key is `cart` and a
     * cart exists, otherwise `session:{id}`.
     */
    public function sessionId(): string
    {
        if ($this->config->get('lunar.search_relevance.session_key', 'cart') === 'cart') {
            $cart = $this->cartSession->current(calculate: false);

            if ($cart) {
                return 'cart:'.$cart->id;
            }
        }

        return 'session:'.($this->session?->getId() ?: 'none');
    }

    public function customerId(): ?int
    {
        return $this->storefrontSession->getCustomer()?->id;
    }

    /**
     * @param  array<int, int>  $shown
     * @param  array<int, int>|null  $ranked
     * @param  array<int, array<string, mixed>>|null  $features
     */
    public function log(
        string $searchId,
        RankingContext $context,
        string $rawQuery,
        int $resultCount,
        array $shown,
        ?array $ranked,
        ?array $features = null,
    ): void {
        $this->bus->dispatch(new LogSearch([
            'id' => $searchId,
            'model_type' => $context->modelType,
            'raw_query' => $rawQuery,
            'normalised_query' => mb_substr($context->normalisedQuery, 0, 255),
            'filters_hash' => $context->filtersHash,
            'session_id' => mb_substr($context->sessionId, 0, 64),
            'customer_id' => $context->customerId,
            'version' => $context->version,
            'mode' => $context->mode,
            'result_count' => $resultCount,
            'shown' => array_values($shown),
            'ranked' => $ranked === null ? null : array_values($ranked),
            'features' => $features,
            'created_at' => now(),
        ]));
    }
}
