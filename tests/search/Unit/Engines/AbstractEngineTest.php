<?php

use Lunar\Search\Engines\AbstractEngine;

uses(\Lunar\Tests\Search\TestCase::class)->group('search');

it('can set engine properties', function () {
    $engine = new class extends AbstractEngine
    {
        public function get(): mixed
        {
            return null;
        }

        protected function getFieldConfig(): array
        {
            return [];
        }
    };

    $engine->filter(['foo' => 'bar']);

    expect($engine->getFilters())
        ->toHaveKey('foo')
        ->and(expect($engine->getFilters()['foo']))
        ->toBe('bar');

    $engine->addFilter('brand', 'Nike');

    expect($engine->getFilters())
        ->toHaveKey('brand')
        ->and(expect($engine->getFilters()['brand']))
        ->toBe('Nike');

    $engine->setFacets([
        'colour' => ['red', 'blue'],
    ]);

    expect($engine->getFacets())
        ->toHaveKey('colour')
        ->and(expect($engine->getFacets()['colour']))
        ->toBe(['red', 'blue']);

    $engine->sort('price:asc');

    expect($engine->getSort())
        ->toBe('price:asc');

    $engine->query('Potato');

    expect($engine->getQuery())
        ->toBe('Potato');
});

it('can set up multiple search queries correctly', function () {
    $engine = new class extends AbstractEngine
    {
        public function get(): mixed
        {
            return null;
        }

        protected function getFieldConfig(): array
        {
            return [];
        }
    };

    $facets = [
        'colour' => ['red', 'blue'],
        'size' => ['small', 'large'],
    ];

    $engine->setFacets($facets);

    $queries = $engine->getSearchQueries();

    expect($queries)->toHaveCount(3)
        ->and($queries[0]->facetFilters)->toHaveCount(2)
        ->and($queries[0]->facetFilters)->toBe($facets)
        ->and($queries[1]->facetFilters)->toHaveCount(1)
        ->and($queries[1]->facetFilters)->toBe(['size' => ['small', 'large']])
        ->and($queries[2]->facetFilters)->toHaveCount(1)
        ->and($queries[2]->facetFilters)->toBe(['colour' => ['red', 'blue']]);
});
