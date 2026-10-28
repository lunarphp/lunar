<?php

use Lunar\Core\Models\Product;

return [
    /*
    |--------------------------------------------------------------------------
    | Pipelines
    |--------------------------------------------------------------------------
    |
    | `request` stages run before the engine queries and receive a
    | Lunar\Search\Pipelines\SearchRequest; they may change page, per page,
    | sort, filters or engine parameters. `results` stages run after
    | SearchResults is built and receive a Lunar\Search\Pipelines\SearchResponse;
    | they may reorder, annotate or replace hits. Stages run top to bottom.
    |
    */
    'pipelines' => [
        'request' => [],
        'results' => [],
    ],

    'typesense' => [
        /*
         * Maximum vector distance for hybrid (semantic) matches. Without a
         * threshold the k: 200 vector query pads every result set with the
         * nearest neighbours of tokens that mean nothing. 0 disables it.
         */
        'vector_distance_threshold' => 0.6,
    ],

    'meilisearch' => [
        /*
         * Name of a configured embedder to enable hybrid search, or null for
         * keyword-only retrieval. `ranking_score_threshold` (0..1) drops weak
         * matches when hybrid search is on; it is the counterpart of the
         * Typesense distance threshold.
         */
        'embedder' => null,
        'semantic_ratio' => 0.5,
        'ranking_score_threshold' => null,
    ],

    'facets' => [
        Product::class => [
            'brand' => [],
            //            'size' => [],
            //            'colour' => [
            //                'Red' => [
            //                    'hex_value' => '#FF0000',
            //                ],
            //            ],
        ],
    ],
];
