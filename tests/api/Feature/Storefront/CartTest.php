<?php

use Lunar\Api\Contracts\CartTokenCodec;
use Lunar\Core\Models\Cart;
use Lunar\Tests\Api\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    $this->store = $this->setUpStore();
});

test('a request without a cart token sees no cart and receives no token', function (): void {
    $response = $this->getJson('/api/storefront/v1/cart')->assertOk();

    expect($response->json('data'))->toBeNull();
    expect($response->headers->has('X-Lunar-Cart'))->toBeFalse();
});

test('adding a line creates the cart and returns its signed token', function (): void {
    $product = $this->visibleProduct($this->store);
    $variant = $this->pricedVariant($product, $this->store['currency'], 1999);

    $response = $this->postJson('/api/storefront/v1/cart/lines', [
        'purchasable_id' => $variant->public_id,
        'quantity' => 2,
    ])->assertCreated();

    $token = $response->headers->get('X-Lunar-Cart');
    $cart = Cart::query()->firstOrFail();

    expect($token)->not->toBeEmpty();
    expect(app(CartTokenCodec::class)->decode($token))->toBe($cart->public_id);

    expect($response->json('data.id'))->toBe($cart->public_id);
    expect($response->json('data.type'))->toBe('carts');
    expect($response->json('data.currency'))->toBe('GBP');
    expect($response->json('data.lines'))->toHaveCount(1);
    expect($response->json('data.lines.0'))->toMatchArray([
        'type' => 'cart-lines',
        'quantity' => 2,
        'purchasable_type' => 'product_variant',
        'purchasable_id' => $variant->public_id,
    ]);
    expect($response->json('data.lines.0.unit_price.amount'))->toBe(1999);
    expect($response->json('data.sub_total.amount'))->toBe(3998);
    expect($response->json('data.total.currency'))->toBe('GBP');

    // The token resumes the same cart on the next request.
    $again = $this->withHeader('X-Lunar-Cart', $token)->getJson('/api/storefront/v1/cart')->assertOk();

    expect($again->json('data.id'))->toBe($cart->public_id);
    expect($again->json('data.lines'))->toHaveCount(1);

    // Adding the same variant again increments the existing line.
    $this->withHeader('X-Lunar-Cart', $token)->postJson('/api/storefront/v1/cart/lines', [
        'purchasable_id' => $variant->public_id,
        'quantity' => 1,
    ])->assertCreated()->assertJsonPath('data.lines.0.quantity', 3);

    expect(Cart::query()->count())->toBe(1);
});

test('a forged or expired token is rejected and a vanished cart is not found', function (): void {
    $this->withHeader('X-Lunar-Cart', 'not-a-token')
        ->getJson('/api/storefront/v1/cart')
        ->assertStatus(401)
        ->assertJsonPath('errors.0.code', 'invalid_cart_token')
        ->assertJsonPath('errors.0.source.header', 'X-Lunar-Cart');

    $cart = Cart::factory()->create(['currency_id' => $this->store['currency']->id, 'channel_id' => $this->store['channel']->id]);
    $token = app(CartTokenCodec::class)->encode($cart);

    $this->travel(31)->days();

    $this->withHeader('X-Lunar-Cart', $token)
        ->getJson('/api/storefront/v1/cart')
        ->assertStatus(401)
        ->assertJsonPath('errors.0.code', 'invalid_cart_token');

    $this->travelBack();

    $cart->delete();

    $this->withHeader('X-Lunar-Cart', $token)
        ->getJson('/api/storefront/v1/cart')
        ->assertNotFound()
        ->assertJsonPath('errors.0.code', 'cart_not_found');
});

test('an unknown or hidden purchasable fails validation with a pointer', function (): void {
    $response = $this->postJson('/api/storefront/v1/cart/lines', [
        'purchasable_id' => '01J0000000000000000000000X',
    ])->assertStatus(422);

    expect($response->json('errors.0'))->toMatchArray([
        'code' => 'validation_failed',
        'source' => ['pointer' => '/purchasable_id'],
    ]);

    $this->postJson('/api/storefront/v1/cart/lines', ['quantity' => 0])
        ->assertStatus(422)
        ->assertJsonPath('errors.0.source.pointer', '/purchasable_id')
        ->assertJsonPath('errors.1.source.pointer', '/quantity');
});
