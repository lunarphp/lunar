<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Models\BundleComponent;
use Lunar\Bundles\Models\BundleGroup;
use Lunar\Core\Enums\SellingPolicy;
use Lunar\Core\Exceptions\Carts\CartException;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Bundles\Support\Catalogue;
use Lunar\Tests\Bundles\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->currency = Catalogue::store();
    $this->cart = Cart::factory()->create(['currency_id' => $this->currency->id]);

    $this->bundleVariant = Catalogue::variant($this->currency, 5000);
    $this->bundle = Bundle::factory()->create(['product_variant_id' => $this->bundleVariant->id]);
    $this->body = Catalogue::variant($this->currency, 3000, stock: 10, attributes: ['selling_policy' => SellingPolicy::InStock]);
    BundleComponent::factory()->create(['bundle_id' => $this->bundle->id, 'product_variant_id' => $this->body->id]);

    $this->lens = BundleGroup::factory()->create(['bundle_id' => $this->bundle->id, 'min_selections' => 1, 'max_selections' => 1]);
    $this->lensA = Catalogue::variant($this->currency, 1000, stock: 10, attributes: ['selling_policy' => SellingPolicy::InStock]);
    $this->lensB = Catalogue::variant($this->currency, 2000, stock: 1, attributes: ['selling_policy' => SellingPolicy::InStock]);
    $this->lensAComponent = BundleComponent::factory()->create(['bundle_id' => $this->bundle->id, 'bundle_group_id' => $this->lens->id, 'product_variant_id' => $this->lensA->id, 'default' => true]);
    $this->lensBComponent = BundleComponent::factory()->create(['bundle_id' => $this->bundle->id, 'bundle_group_id' => $this->lens->id, 'product_variant_id' => $this->lensB->id]);
});

test('a configurable bundle is added with its selection in meta', function () {
    $meta = ['bundle' => ['selections' => [$this->lens->public_id => [$this->lensBComponent->public_id]]]];

    $cart = $this->cart->add($this->bundleVariant, 1, $meta);

    expect($cart->lines)->toHaveCount(1)
        ->and($cart->lines->first()->meta['bundle']['selections'][$this->lens->public_id])->toBe([$this->lensBComponent->public_id]);
});

test('a fixed bundle and the default selection add without meta', function () {
    $cart = $this->cart->add($this->bundleVariant, 2);

    expect($cart->lines->first()->quantity)->toBe(2);
});

test('an invalid selection is rejected on add', function () {
    $this->cart->add($this->bundleVariant, 1, ['bundle' => ['selections' => [$this->lens->public_id => ['nope']]]]);
})->throws(CartException::class);

test('the selected component\'s stock is checked, not the best case', function () {
    $meta = ['bundle' => ['selections' => [$this->lens->public_id => [$this->lensBComponent->public_id]]]];

    expect(ProductVariant::query()->find($this->bundleVariant->id)->getTotalInventory())->toBe(10);

    $this->cart->add($this->bundleVariant, 2, $meta);
})->throws(CartException::class);

test('an update is validated against the meta the line will carry', function () {
    $cart = $this->cart->add($this->bundleVariant, 1);
    $line = $cart->lines->first();

    $cart->updateLine($line->id, 3);

    expect($cart->lines->first()->quantity)->toBe(3);

    $cart->updateLine($line->id, 2, ['bundle' => ['selections' => [$this->lens->public_id => [$this->lensBComponent->public_id]]]]);
})->throws(CartException::class);

test('an ordinary variant passes through the validator', function () {
    $variant = Catalogue::variant($this->currency, 100);

    expect($this->cart->add($variant, 1, ['gift' => true])->lines)->toHaveCount(1);
});
