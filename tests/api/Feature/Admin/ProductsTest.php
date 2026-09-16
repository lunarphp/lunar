<?php

use Lunar\Core\Models\Product;
use Lunar\Tests\Api\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    $this->store = $this->setUpStore();
    $this->reader = $this->apiKey(['catalog:read']);
});

test('the admin surface lists every product with translations as locale maps', function (): void {
    $published = Product::factory()->create(['name' => collect(['en' => 'Widget', 'fr' => 'Bidule'])]);
    $draft = Product::factory()->create(['status' => 'draft']);

    $response = $this->withHeaders($this->reader['headers'])->getJson('/api/admin/v1/products?sort=created_at')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
    expect($response->json('data.0.id'))->toBe($published->public_id);
    expect($response->json('data.0.name'))->toBe(['en' => 'Widget', 'fr' => 'Bidule']);
    expect($response->json('data.0.status'))->toBe('published');
    expect($response->json('data.0.brand_id'))->toBe($published->brand->public_id);
    expect($response->json('data.1.status'))->toBe('draft');
    expect($response->json('meta'))->not->toHaveKey('channel');

    expect($this->withHeaders($this->reader['headers'])->getJson('/api/admin/v1/products?filter[status]=draft')->json('data.*.id'))
        ->toBe([$draft->public_id]);
});

test('variants and prices embed with full price detail', function (): void {
    $product = Product::factory()->create();
    $variant = $this->pricedVariant($product, $this->store['currency'], 1999, ['sku' => 'ADM-1']);

    $response = $this->withHeaders($this->reader['headers'])
        ->getJson("/api/admin/v1/products/{$product->public_id}?include=variants.prices,brand")
        ->assertOk();

    expect($response->json('data.variants.0.sku'))->toBe('ADM-1');
    expect($response->json('data.variants.0.enabled'))->toBeTrue();
    expect($response->json('data.variants.0.prices.0.price.amount'))->toBe(1999);
    expect($response->json('data.variants.0.prices.0.currency'))->toBe('GBP');
    expect($response->json('data.variants.0.prices.0.min_quantity'))->toBe(1);
    expect($response->json('data.brand.type'))->toBe('brands');

    expect($this->withHeaders($this->reader['headers'])->getJson('/api/admin/v1/products?filter[sku][like]=ADM')->json('data.*.id'))
        ->toBe([$product->public_id]);
});
