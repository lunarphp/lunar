<?php

use Illuminate\Http\Request;
use Lunar\Api\Contracts\ApiManager;
use Lunar\Api\Http\Exceptions\InvalidQueryException;
use Lunar\Api\Query\QueryParser;
use Lunar\Api\Resources\SerializationContext;
use Lunar\Api\Storefront\Resources\V1\ProductResource;
use Lunar\Tests\Api\TestCase;

uses(TestCase::class);

function parseProducts(array $query, bool $collection = true)
{
    $registry = app(ApiManager::class)->storefront('v1');
    $definition = $registry->definition(ProductResource::class);

    return app(QueryParser::class)->parse(
        Request::create('/products', 'GET', $query),
        $definition,
        new SerializationContext($registry),
        $collection,
    );
}

function queryErrors(callable $callback): array
{
    try {
        $callback();
    } catch (InvalidQueryException $e) {
        return array_column($e->toErrors(), 'code');
    }

    return [];
}

test('a full query parses into includes, fields, filters, sorts and pagination', function (): void {
    $query = parseProducts([
        'include' => 'brand,variants.values',
        'fields' => ['products' => 'name,price', 'brands' => ['name']],
        'filter' => ['brand' => 'acme', 'price' => ['gte' => '100', 'lte' => '500'], 'id' => ['a', 'b']],
        'sort' => '-created_at,name',
        'page' => ['number' => '2', 'size' => '20'],
    ]);

    expect($query->includes->paths())->toBe(['brand', 'variants', 'variants.values']);
    expect($query->fields)->toBe(['products' => ['name', 'price'], 'brands' => ['name']]);
    expect(array_map(fn ($f) => [$f['filter']->name, $f['operator']], $query->filters))
        ->toBe([['brand', 'eq'], ['price', 'gte'], ['price', 'lte'], ['id', 'in']]);
    expect(array_map(fn ($s) => [$s['sort']->name, $s['direction']], $query->sorts))
        ->toBe([['created_at', 'desc'], ['name', 'asc']]);
    expect($query->pageNumber)->toBe(2);
    expect($query->pageSize)->toBe(20);
    expect($query->cursor)->toBeNull();
});

test('every offending parameter is reported at once', function (): void {
    $codes = queryErrors(fn () => parseProducts([
        'include' => 'brand.nope,variants.product.variants.product',
        'fields' => ['widgets' => 'x', 'products' => 'nope'],
        'filter' => ['colour' => 'red', 'brand' => ['like' => 'a']],
        'sort' => 'price',
        'page' => ['number' => '0', 'size' => '999', 'offset' => '3'],
    ]));

    expect($codes)->toBe([
        'unknown_include',
        'include_too_deep',
        'unknown_type',
        'unknown_field',
        'unknown_filter',
        'unknown_operator',
        'unknown_sort',
        'invalid_page_size',
        'invalid_page_number',
        'unknown_page_key',
    ]);
});

test('show requests validate includes and fields but ignore collection grammar', function (): void {
    $query = parseProducts(['filter' => ['colour' => 'red'], 'sort' => 'nope', 'include' => 'brand'], collection: false);

    expect($query->filters)->toBe([]);
    expect($query->sorts)->toBe([]);
    expect($query->includes->names())->toBe(['brand']);

    expect(queryErrors(fn () => parseProducts(['include' => 'nope'], collection: false)))->toBe(['unknown_include']);
});

test('cursor pagination is only accepted where the resource opts in', function (): void {
    expect(queryErrors(fn () => parseProducts(['page' => ['cursor' => 'abc']])))->toBe(['cursor_unsupported']);
});
