<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

/**
 * The order summary card renders each line with a thumbnail and SKU when the
 * purchasable has them. The projection carries the small conversion URL of
 * the variant's thumbnail (falling back to the product's) and null otherwise,
 * so the card can show its placeholder icon instead of a broken image.
 */
it('projects the product thumbnail and sku on each item', function () {
    config()->set('media-library.queue_conversions_by_default', false);

    $cart = CheckoutCart::orderable();
    /** @var ProductVariant $variant */
    $variant = $cart->lines->first()->purchasable;
    $variant->product->addMedia(UploadedFile::fake()->image('switch.jpg'))
        ->toMediaCollection(config('lunar.media.collection'));

    $session = CheckoutCart::session($cart->refresh());

    $response = $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk();

    expect($response->json('props.checkout.items.0.sku'))->toBe($variant->sku);
    expect($response->json('props.checkout.items.0.image'))
        ->toBeString()
        ->toContain('switch')
        ->toContain('small');
});

it('projects a null image when the product has no media', function () {
    $session = CheckoutCart::session(CheckoutCart::orderable());

    $response = $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk();

    expect($response->json('props.checkout.items.0'))->toHaveKeys(['id', 'title', 'sku', 'image', 'qty', 'price']);
    expect($response->json('props.checkout.items.0.image'))->toBeNull();
});
