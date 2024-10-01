<?php

namespace Lunar\Search\Engines;

use Lunar\Search\Data\SearchFacet;
use Lunar\Search\Data\SearchHit;
use Lunar\Search\Data\SearchResults;
use Typesense\Documents;

class TypesenseEngine extends AbstractEngine
{
    public function __construct()
    {
        $this->searchBuilder = function (Documents $documents, string $query, array $options) {
            return $documents->search([
                'q' => $query,
                ...$options,
                'facet_by' => 'colour,size',
                'per_page' => 2,
                //                'page' => ,
            ]);
        };
    }

    public function get(): mixed
    {
        $results = $this->getRawResults();
        //
        //        dd($results);
        $documents = collect($results['hits'])->map(fn ($hit) => SearchHit::from([
            'highlights' => collect($hit['highlights'])->map(
                fn ($highlight) => SearchHit\Highlight::from([
                    'field' => $highlight['field'],
                    'matches' => $highlight['matched_tokens'],
                    'snippet' => $highlight['snippet'],
                ])
            ),
            'document' => $hit['document'],
        ]));

        $totalPages = (int) round($results['found'] / count($results['hits']));

        $facets = collect($results['facet_counts'])->map(
            fn ($facet) => SearchFacet::from([
                'field' => $facet['field_name'],
                'values' => collect($facet['counts'])->map(
                    fn ($value) => SearchFacet\FacetValue::from([
                        'value' => $value['value'],
                        'count' => $value['count'],
                    ])
                ),
            ])
        );

        return SearchResults::from([
            'query' => $results['request_params']['q'],
            'total_pages' => $totalPages,
            'page' => $results['page'],
            'count' => $results['found'],
            'per_page' => $results['request_params']['per_page'],
            'hits' => $documents,
            'facets' => $facets,
        ]);
    }
}
