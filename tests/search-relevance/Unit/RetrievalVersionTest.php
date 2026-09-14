<?php

use Illuminate\Config\Repository;
use Lunar\Core\Models\Product;
use Lunar\SearchRelevance\RetrievalVersion;

uses()->group('search-relevance');

function retrievalVersion(array $config): RetrievalVersion
{
    return new RetrievalVersion(new Repository($config));
}

it('combines the normaliser version, driver and retrieval kind', function () {
    $version = retrievalVersion([
        'lunar' => ['search_relevance' => ['normaliser_version' => 2]],
        'scout' => ['driver' => 'database'],
    ]);

    expect($version->current(Product::class))->toBe('n2:database:keyword');
});

it('reads the driver from the engine map before the scout default', function () {
    $version = retrievalVersion([
        'lunar' => ['search_relevance' => ['normaliser_version' => 1], 'search' => ['engine_map' => [Product::class => 'typesense']]],
        'scout' => ['driver' => 'database'],
    ]);

    expect($version->current(Product::class))->toBe('n1:typesense:keyword');
});

it('is hybrid when the typesense schema declares an embedding field', function () {
    $version = retrievalVersion([
        'lunar' => ['search_relevance' => ['normaliser_version' => 1]],
        'scout' => [
            'driver' => 'typesense',
            'typesense' => ['model-settings' => [Product::class => ['collection-schema' => ['fields' => [
                ['name' => 'name', 'type' => 'string'],
                ['name' => 'embedding', 'type' => 'float[]'],
            ]]]]],
        ],
    ]);

    expect($version->current(Product::class))->toBe('n1:typesense:hybrid');
});

it('is hybrid when meilisearch has an embedder', function () {
    $version = retrievalVersion([
        'lunar' => ['search_relevance' => ['normaliser_version' => 1], 'search' => ['meilisearch' => ['embedder' => 'default']]],
        'scout' => ['driver' => 'meilisearch'],
    ]);

    expect($version->current(Product::class))->toBe('n1:meilisearch:hybrid');
});
