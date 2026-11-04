<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductOption;
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

it('matches products by a text query', function () {
    Product::factory()->create(['name' => ['en' => 'Blue Shirt']]);
    Product::factory()->create(['name' => ['en' => 'Red Hat']]);

    $results = Search::model(Product::class)->query('Shirt')->get();

    expect($results->count)
        ->toBe(1)
        ->and($results->hits[0]->document['name_en'])
        ->toBe('Blue Shirt');
});

it('matches products by a text query with the collection driver', function () {
    Config::set('scout.driver', 'collection');
    Config::set('lunar.search.engine_map', []);

    Product::factory()->create(['name' => ['en' => 'Blue Shirt']]);
    Product::factory()->create(['name' => ['en' => 'Red Hat']]);

    $results = Search::model(Product::class)->query('Shirt')->get();

    expect($results->count)
        ->toBe(1)
        ->and($results->hits[0]->document['name_en'])
        ->toBe('Blue Shirt');
});

it('matches searchable models by a text query through scout', function (Closure $create, string $query) {
    Config::set('lunar.search.engine_map', []);

    $match = $create();

    expect($match::search($query)->get()->modelKeys())->toBe([$match->getKey()]);
})->with([
    'brand' => [fn () => tap(Brand::factory()->create(['name' => 'Acme']), fn () => Brand::factory()->create(['name' => 'Globex'])), 'Acme'],
    'collection' => [fn () => tap(Collection::factory()->create(['name' => ['en' => 'Summer Sale']]), fn () => Collection::factory()->create(['name' => ['en' => 'Winter']])), 'Summer'],
    'customer' => [fn () => tap(Customer::factory()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace']), fn () => Customer::factory()->create(['first_name' => 'Alan', 'last_name' => 'Turing'])), 'Lovelace'],
    'order' => [fn () => tap(Order::factory()->create(['reference' => 'REF-SHIRT']), fn () => Order::factory()->create(['reference' => 'REF-HAT'])), 'SHIRT'],
    'product option' => [fn () => tap(ProductOption::factory()->create(['name' => ['en' => 'Colour']]), fn () => ProductOption::factory()->create(['name' => ['en' => 'Size']])), 'Colour'],
]);
