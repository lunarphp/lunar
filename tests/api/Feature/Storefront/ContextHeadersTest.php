<?php

use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Tests\Api\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    $this->store = $this->setUpStore();
});

test('the resolved context is echoed in meta and headers', function (): void {
    $this->getJson('/api/storefront/v1/products')
        ->assertOk()
        ->assertHeader('X-Lunar-Channel', 'webstore')
        ->assertHeader('X-Lunar-Currency', 'GBP')
        ->assertHeader('Content-Language', 'en')
        ->assertJsonPath('meta.channel', 'webstore')
        ->assertJsonPath('meta.currency', 'GBP');
});

test('X-Lunar-Currency switches the pricing currency', function (): void {
    $eur = Currency::factory()->create(['default' => false, 'code' => 'EUR']);

    $product = $this->visibleProduct($this->store);
    $variant = $this->pricedVariant($product, $this->store['currency'], 1000);
    $variant->prices()->create(['currency_id' => $eur->id, 'price' => 1200, 'min_quantity' => 1]);

    $response = $this->withHeader('X-Lunar-Currency', 'eur')
        ->getJson("/api/storefront/v1/products/{$product->public_id}")
        ->assertOk()
        ->assertHeader('X-Lunar-Currency', 'EUR');

    expect($response->json('data.price'))->toMatchArray(['amount' => 1200, 'currency' => 'EUR']);
    expect($response->json('meta.currency'))->toBe('EUR');
});

test('X-Lunar-Channel scopes the catalogue', function (): void {
    $trade = Channel::factory()->create(['default' => false, 'handle' => 'trade']);

    $webProduct = $this->visibleProduct($this->store);
    $tradeProduct = $this->visibleProduct(['channel' => $trade, 'group' => $this->store['group']]);

    expect($this->withHeader('X-Lunar-Channel', 'trade')->getJson('/api/storefront/v1/products')->json('data.*.id'))
        ->toBe([$tradeProduct->public_id]);

    expect($this->freshRequest()->getJson('/api/storefront/v1/products')->json('data.*.id'))
        ->toBe([$webProduct->public_id]);
});

test('unknown channel and currency codes are rejected', function (): void {
    $this->withHeader('X-Lunar-Channel', 'nope')
        ->getJson('/api/storefront/v1/products')
        ->assertStatus(422)
        ->assertJsonPath('errors.0.code', 'invalid_header')
        ->assertJsonPath('errors.0.source.header', 'X-Lunar-Channel');

    $this->freshRequest()->withHeader('X-Lunar-Currency', 'XXX')
        ->getJson('/api/storefront/v1/products')
        ->assertStatus(422)
        ->assertJsonPath('errors.0.source.header', 'X-Lunar-Currency');
});

test('Accept-Language picks a store language and falls back to the default', function (): void {
    Language::factory()->create(['default' => false, 'code' => 'fr']);

    $product = $this->visibleProduct($this->store, ['name' => collect(['en' => 'Widget', 'fr' => 'Bidule'])]);

    $this->withHeader('Accept-Language', 'fr-FR,fr;q=0.9,en;q=0.8')
        ->getJson("/api/storefront/v1/products/{$product->public_id}")
        ->assertOk()
        ->assertHeader('Content-Language', 'fr')
        ->assertJsonPath('data.name', 'Bidule')
        ->assertJsonPath('meta.locale', 'fr');

    $this->freshRequest()->withHeader('Accept-Language', 'de')
        ->getJson("/api/storefront/v1/products/{$product->public_id}")
        ->assertOk()
        ->assertHeader('Content-Language', 'en')
        ->assertJsonPath('data.name', 'Widget');
});
