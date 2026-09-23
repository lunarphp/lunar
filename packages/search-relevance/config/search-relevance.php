<?php

use Lunar\Core\Models\Product;
use Lunar\SearchRelevance\Normalisers\DefaultQueryNormaliser;
use Lunar\SearchRelevance\Rankers\BucketedRanker;
use Lunar\SearchRelevance\Signals\QueryAffinitySignal;

return [
    /*
    |--------------------------------------------------------------------------
    | Mode
    |--------------------------------------------------------------------------
    |
    | off:    nothing is logged or ranked.
    | shadow: everything is logged and the reranked order is stored alongside
    |         the shown order, but the engine order is displayed. The default
    |         after install, so training data accrues from day one and the
    |         replay command can prove uplift before switching on.
    | on:     the reranked order is displayed.
    |
    | Set in config or LUNAR_SEARCH_RELEVANCE_MODE, like every other store
    | setting; the admin panel shows the current value but cannot change it.
    |
    */
    'mode' => env('LUNAR_SEARCH_RELEVANCE_MODE', 'shadow'),

    // Searchable models whose results are ranked and logged.
    'models' => [Product::class],

    // Sort fields that mean the engine's own relevance order. A search sorted
    // by one of these is ranked like an unsorted one; any other sort is the
    // shopper's choice and is left alone. Lunar's storefront sends
    // `relevance:asc` by default.
    'relevance_sorts' => ['relevance', '_text_match'],

    // Candidate window fetched from the engine and reordered.
    'window' => 250,

    // Reorder only within buckets of this many hits, so a weak keyword match
    // can never leap above a strong one.
    'bucket_size' => 10,

    // Seconds the ranked window is cached for.
    'cache_ttl' => 300,

    // Learned products the engine did not return are inserted at the head of
    // the second bucket, at most `max` of them, each at least `min_relative`.
    'learned_union' => ['max' => 5, 'min_relative' => 0.1],

    // How many shown product ids a logged search records.
    'impressions_logged' => 50,

    // How long a click attributes a later basket or purchase.
    'attribution_ttl_minutes' => 30,

    // What identifies a shopper: the Lunar cart (survives login) or the
    // Laravel session id.
    'session_key' => 'cart',

    'normaliser' => DefaultQueryNormaliser::class,

    // Bump when normalisation rules change so learned scores relearn cleanly.
    'normaliser_version' => 1,

    'ranker' => BucketedRanker::class,

    // Signal class => weight. Combined scores are clamped to 0..1.
    'signals' => [
        QueryAffinitySignal::class => 1.0,
    ],

    'scoring' => [
        'weights' => ['click' => 1, 'basket' => 3, 'purchase' => 5],
        // Position correction: an event at position p counts p^eta times,
        // capped at max_position_weight, so the ranking learns preference
        // rather than position bias. 0 disables it.
        'position_eta' => 0.7,
        'max_position_weight' => 5,
        'half_life_days' => 30,
        'window_days' => 180,
        'min_sessions' => 3,
        'max_products_per_query' => 50,
        // Daily run time for lunar:search-relevance:score.
        'schedule' => '02:00',
    ],

    // Raw queries and events older than this are pruned weekly.
    'retention_days' => 400,

    'guards' => [
        // Events endpoint rate limit, "attempts,minutes", per shopper and per IP.
        'events_rate_limit' => '60,1',
        // Sessions searching faster than this are ignored by scoring.
        'max_searches_per_minute' => 30,
        // Events are only accepted this long after the search they belong to.
        'event_window_minutes' => 120,
        // Only sessions that hold a cart or belong to a known customer count
        // towards learning, so a bot minting fresh sessions gains nothing.
        'trusted_sessions_only' => true,
        // Searches from these user agents (case-insensitive substrings) are
        // neither logged nor ranked.
        'ignored_user_agents' => [
            'bot', 'crawl', 'spider', 'slurp', 'curl', 'wget', 'python-requests',
            'headlesschrome', 'phantomjs', 'lighthouse', 'facebookexternalhit',
        ],
    ],
];
