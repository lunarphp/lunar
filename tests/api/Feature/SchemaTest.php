<?php

use Lunar\Tests\Api\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    $this->store = $this->setUpStore();
});

test('the storefront schema describes every registered resource and its routes', function (): void {
    $response = $this->getJson('/api/storefront/v1/_schema')->assertOk();

    expect($response->json('data.surface'))->toBe('storefront');
    expect($response->json('data.version'))->toBe('v1');

    $products = collect($response->json('data.resources'))->firstWhere('type', 'products');

    expect(collect($products['fields'])->pluck('name'))->toContain('name', 'price', 'slug');
    expect(collect($products['includes'])->pluck('name'))->toContain('brand', 'variants');
    expect(collect($products['filters'])->firstWhere('name', 'price')['operators'])->toBe(['eq', 'gt', 'gte', 'lt', 'lte']);
    expect(collect($products['sorts'])->pluck('name'))->toContain('created_at', 'name');
    expect($products['pagination'])->toMatchArray(['default_size' => 15, 'max_size' => 100, 'cursor' => false]);
    expect(collect($products['routes'])->pluck('uri'))->toContain('/api/storefront/v1/products', '/api/storefront/v1/products/{id}');

    expect(collect($response->json('data.resources'))->pluck('type'))->toContain('brands', 'collections', 'carts', 'customers');
});

test('the admin schema honours the caller abilities', function (): void {
    $this->getJson('/api/admin/v1/_schema')->assertUnauthorized();

    $key = $this->apiKey(['catalog:read']);

    $response = $this->withHeaders($key['headers'])->getJson('/api/admin/v1/_schema')->assertOk();

    expect($response->json('data.surface'))->toBe('admin');
    expect(collect($response->json('data.resources'))->pluck('type'))->toContain('products', 'api-keys');
});

test('the schema command prints the same description', function (): void {
    $this->artisan('lunar:api:schema', ['surface' => 'storefront', '--json' => true])
        ->expectsOutputToContain('"type": "products"')
        ->assertSuccessful();

    $this->artisan('lunar:api:schema', ['surface' => 'admin'])
        ->expectsOutputToContain('api-keys')
        ->assertSuccessful();
});
