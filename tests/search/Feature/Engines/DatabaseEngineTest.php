<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Search\Data\SearchResults;
use Lunar\Search\Facades\Search;
use Lunar\Tests\Search\TestCase;

uses(TestCase::class)->group('search');
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);

    Config::set('scout.driver', 'database');
    Config::set('lunar.search.engine_map', [
        Product::class => 'database',
    ]);
});

it('returns results via scout without the admin search builder helper', function () {
    Product::factory()->count(3)->create();

    $results = Search::model(Product::class)->get();

    expect($results)
        ->toBeInstanceOf(SearchResults::class)
        ->and($results->count)
        ->toBe(3)
        ->and($results->hits)
        ->toHaveCount(3)
        ->and($results->facets)
        ->toHaveCount(0);
});

it('respects the requested page size', function () {
    Product::factory()->count(3)->create();

    $results = Search::model(Product::class)->perPage(2)->get();

    expect($results->perPage)
        ->toBe(2)
        ->and($results->hits)
        ->toHaveCount(2)
        ->and($results->totalPages)
        ->toBe(2);
});
