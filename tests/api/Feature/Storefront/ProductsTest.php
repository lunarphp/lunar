<?php

use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;
use Lunar\Tests\Api\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    $this->store = $this->setUpStore();
});

test('the index lists only products visible to the storefront context', function (): void {
    $visible = $this->visibleProduct($this->store);
    $this->pricedVariant($visible, $this->store['currency'], 1999);

    Product::factory()->create(['status' => 'draft']);

    $otherChannel = Channel::factory()->create(['default' => false, 'handle' => 'other']);
    $hidden = Product::factory()->create();
    $hidden->scheduleChannel($otherChannel);

    $response = $this->getJson('/api/storefront/v1/products')->assertOk();

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.id'))->toBe($visible->public_id);
    expect($response->json('data.0.type'))->toBe('products');
    expect($response->json('data.0'))->not->toHaveKey('brand');
    expect($response->json('meta'))->toMatchArray(['channel' => 'webstore', 'currency' => 'GBP', 'locale' => 'en']);
    expect($response->json('meta.pagination'))->toMatchArray(['page' => 1, 'per_page' => 15, 'total' => 1, 'last_page' => 1]);
});

test('translatable fields resolve to the request locale and money serialises as minor units', function (): void {
    $product = $this->visibleProduct($this->store, ['name' => collect(['en' => 'Widget', 'fr' => 'Bidule'])]);
    $this->pricedVariant($product, $this->store['currency'], 2500);
    $this->pricedVariant($product, $this->store['currency'], 1999);

    $response = $this->getJson("/api/storefront/v1/products/{$product->public_id}")->assertOk();

    expect($response->json('data.name'))->toBe('Widget');
    expect($response->json('data.price'))->toMatchArray([
        'amount' => 1999,
        'currency' => 'GBP',
        'decimal_places' => 2,
    ]);
    expect($response->json('data.price.formatted'))->toContain('19.99');
    expect($response->json('data.created_at'))->toEndWith('Z');
    expect($response->json('links.self'))->toContain($product->public_id);
});

test('requested includes embed under the parent, nested includes descend', function (): void {
    $product = $this->visibleProduct($this->store);
    $variant = $this->pricedVariant($product, $this->store['currency'], 1000);

    $option = ProductOption::factory()->create();
    $value = ProductOptionValue::factory()->create(['product_option_id' => $option->id, 'name' => ['en' => 'Blue']]);
    $variant->values()->attach($value);

    $response = $this->getJson("/api/storefront/v1/products/{$product->public_id}?include=brand,variants.values")->assertOk();

    expect($response->json('data.brand.id'))->toBe($product->brand->public_id);
    expect($response->json('data.brand.type'))->toBe('brands');
    expect($response->json('data.variants.0.id'))->toBe($variant->public_id);
    expect($response->json('data.variants.0.price.amount'))->toBe(1000);
    expect($response->json('data.variants.0.values.0.name'))->toBe('Blue');
    expect($response->json('data.variants.0.values.0.type'))->toBe('product-option-values');
});

test('an unknown include is rejected with the allowed values', function (): void {
    $response = $this->getJson('/api/storefront/v1/products?include=reviews')->assertStatus(422);

    expect($response->json('errors.0'))->toMatchArray([
        'status' => '422',
        'code' => 'unknown_include',
        'source' => ['parameter' => 'include'],
    ]);
    expect($response->json('errors.0.detail'))->toContain('brand')->toContain('variants');
});

test('sparse fieldsets limit the payload to the requested fields', function (): void {
    $product = $this->visibleProduct($this->store);

    $response = $this->getJson('/api/storefront/v1/products?fields[products]=name,slug')->assertOk();

    expect(array_keys($response->json('data.0')))->toBe(['id', 'type', 'name', 'slug']);

    $this->getJson('/api/storefront/v1/products?fields[products]=nope')
        ->assertStatus(422)
        ->assertJsonPath('errors.0.code', 'unknown_field');

    $this->getJson('/api/storefront/v1/products?fields[widgets]=name')
        ->assertStatus(422)
        ->assertJsonPath('errors.0.code', 'unknown_type');
});

test('registered filters narrow the index and unknown operators are rejected', function (): void {
    $acme = Brand::factory()->create(['handle' => 'acme']);
    $other = Brand::factory()->create(['handle' => 'other']);

    $acmeProduct = $this->visibleProduct($this->store, ['brand_id' => $acme->id]);
    $this->pricedVariant($acmeProduct, $this->store['currency'], 5000, ['sku' => 'ACME-1']);

    $otherProduct = $this->visibleProduct($this->store, ['brand_id' => $other->id]);
    $this->pricedVariant($otherProduct, $this->store['currency'], 500, ['sku' => 'OTHER-1']);

    expect($this->getJson('/api/storefront/v1/products?filter[brand]=acme')->assertOk()->json('data.*.id'))
        ->toBe([$acmeProduct->public_id]);

    expect($this->getJson('/api/storefront/v1/products?filter[sku]=OTHER-1')->assertOk()->json('data.*.id'))
        ->toBe([$otherProduct->public_id]);

    expect($this->getJson('/api/storefront/v1/products?filter[price][gte]=1000')->assertOk()->json('data.*.id'))
        ->toBe([$acmeProduct->public_id]);

    $this->getJson('/api/storefront/v1/products?filter[brand][like]=ac')
        ->assertStatus(422)
        ->assertJsonPath('errors.0.code', 'unknown_operator')
        ->assertJsonPath('errors.0.source.parameter', 'filter[brand][like]');

    $this->getJson('/api/storefront/v1/products?filter[colour]=red')
        ->assertStatus(422)
        ->assertJsonPath('errors.0.code', 'unknown_filter');
});

test('sorts apply in the requested direction and pagination links carry the grammar', function (): void {
    $first = $this->visibleProduct($this->store, ['name' => collect(['en' => 'Alpha'])]);
    $this->travel(1)->minute();
    $second = $this->visibleProduct($this->store, ['name' => collect(['en' => 'Beta'])]);

    expect($this->getJson('/api/storefront/v1/products?sort=-created_at')->json('data.*.id'))
        ->toBe([$second->public_id, $first->public_id]);

    expect($this->getJson('/api/storefront/v1/products?sort=name')->json('data.*.name'))
        ->toBe(['Alpha', 'Beta']);

    $this->getJson('/api/storefront/v1/products?sort=price')
        ->assertStatus(422)
        ->assertJsonPath('errors.0.code', 'unknown_sort');

    $page = $this->getJson('/api/storefront/v1/products?page[size]=1&sort=name')->assertOk();

    expect($page->json('data'))->toHaveCount(1);
    expect($page->json('meta.pagination'))->toMatchArray(['page' => 1, 'per_page' => 1, 'total' => 2, 'last_page' => 2]);
    expect($page->json('links.next'))->toContain('page%5Bnumber%5D=2')->toContain('sort=name');
    expect($page->json('links.prev'))->toBeNull();

    $this->getJson('/api/storefront/v1/products?page[size]=500')
        ->assertStatus(422)
        ->assertJsonPath('errors.0.code', 'invalid_page_size');

    $this->getJson('/api/storefront/v1/products?page[cursor]=abc')
        ->assertStatus(422)
        ->assertJsonPath('errors.0.code', 'cursor_unsupported');
});

test('an unknown id returns a 404 error object', function (): void {
    $this->getJson('/api/storefront/v1/products/01J0000000000000000000000X')
        ->assertNotFound()
        ->assertJsonPath('errors.0.code', 'resource_not_found')
        ->assertJsonPath('errors.0.status', '404');
});

test('hidden products are not addressable by id', function (): void {
    $draft = Product::factory()->create(['status' => 'draft']);

    $this->getJson("/api/storefront/v1/products/{$draft->public_id}")->assertNotFound();
});

test('unknown endpoints and methods return error objects', function (): void {
    $this->getJson('/api/storefront/v1/nope')
        ->assertNotFound()
        ->assertJsonPath('errors.0.code', 'not_found');

    $this->postJson('/api/storefront/v1/products')
        ->assertStatus(405)
        ->assertJsonPath('errors.0.code', 'method_not_allowed');
});
