<?php

use Lunar\Api\Contracts\ApiManager;
use Lunar\Api\Exceptions\ResourceDefinitionException;
use Lunar\Api\Storefront\Resources\V1\ProductResource;
use Lunar\Core\Models\Builders\Builder;
use Lunar\Core\Models\Product;
use Lunar\Tests\Api\Fixtures\CustomProductResource;
use Lunar\Tests\Api\Fixtures\DuplicateFieldExtension;
use Lunar\Tests\Api\Fixtures\ExtensionTestCase;

uses(ExtensionTestCase::class);

beforeEach(function (): void {
    $this->store = $this->setUpStore();

    Product::addLocalScope('featured', fn ($query) => $query->whereHas('variants', fn ($variant) => $variant->where('sku', 'like', 'FEAT%')));
});

afterEach(function (): void {
    Builder::flushScopes();
});

test('an extension adds fields, filters, sorts and routes to a built-in resource', function (): void {
    $featured = $this->visibleProduct($this->store);
    $this->pricedVariant($featured, $this->store['currency'], 100, ['sku' => 'FEAT-1']);

    $plain = $this->visibleProduct($this->store);
    $this->pricedVariant($plain, $this->store['currency'], 100, ['sku' => 'PLAIN-1']);

    $response = $this->getJson('/api/storefront/v1/products?sort=rating')->assertOk();

    expect($response->json('data.0.average_rating'))->toBe(4.5);
    expect($response->json('data.0.review_count'))->toBe(1);
    expect($response->json('data.0'))->not->toHaveKey('cost_price');

    expect($this->getJson('/api/storefront/v1/products?filter[featured]=1')->json('data.*.id'))->toBe([$featured->public_id]);

    $this->getJson("/api/storefront/v1/products/{$featured->public_id}/reviews")
        ->assertOk()
        ->assertJsonPath('data.product', $featured->public_id);

    $schema = collect($this->getJson('/api/storefront/v1/_schema')->json('data.resources'))->firstWhere('type', 'products');

    expect(collect($schema['fields'])->pluck('name'))->toContain('average_rating');
    expect(collect($schema['filters'])->pluck('name'))->toContain('featured');
    expect(collect($schema['routes'])->pluck('name'))->toContain('lunar.api.storefront.v1.products.reviews');
});

test('a replacement resource keeps the extensions registered against the built-in', function (): void {
    app(ApiManager::class)->storefront('v1')->replace(ProductResource::class, CustomProductResource::class);

    $product = $this->visibleProduct($this->store);

    $response = $this->getJson("/api/storefront/v1/products/{$product->public_id}")->assertOk();

    expect($response->json('data.custom'))->toBe('yes');
    expect($response->json('data.average_rating'))->toBe(4.5);
});

test('an extension cannot redeclare a field the resource already has', function (): void {
    app(ApiManager::class)->storefront('v1')->extend(ProductResource::class, DuplicateFieldExtension::class);

    app(ApiManager::class)->storefront('v1')->definition(ProductResource::class);
})->throws(ResourceDefinitionException::class, 'field [name]');
