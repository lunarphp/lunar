<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Bundles\Contracts\Actions\DefinesBundle;
use Lunar\Bundles\Enums\BundlePricing;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Bundles\Support\Catalogue;
use Lunar\Tests\Bundles\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->currency = Catalogue::store();
    $this->cart = Cart::factory()->create(['currency_id' => $this->currency->id]);

    $body = Catalogue::variant($this->currency, 3000);
    $lensA = Catalogue::variant($this->currency, 1000);
    $lensB = Catalogue::variant($this->currency, 2000);

    $this->bundleVariant = ProductVariant::factory()->create();
    $this->bundle = app(DefinesBundle::class)->execute($this->bundleVariant, BundlePricing::Components, 10);
    $this->bundle->syncGroups([['name' => ['en' => 'Lens'], 'min_selections' => 1, 'max_selections' => 1]]);
    $this->lens = $this->bundle->groups->first();
    $this->bundle->syncComponents([
        ['variant' => $body, 'quantity' => 1],
        ['variant' => $lensA, 'quantity' => 1, 'group' => $this->lens, 'default' => true],
        ['variant' => $lensB, 'quantity' => 1, 'group' => $this->lens],
    ]);
    $this->lensB = $this->bundle->components->firstWhere('product_variant_id', $lensB->id);
});

test('the default selection is priced from the materialised row', function () {
    $cart = $this->cart->add($this->bundleVariant, 2);

    expect($cart->lines->first()->unitPrice->value)->toBe(3600)
        ->and($cart->lines->first()->subTotal->value)->toBe(7200);
});

test('a non-default selection is priced from its components at cart time', function () {
    $cart = $this->cart->add($this->bundleVariant, 2, ['bundle' => ['selections' => [
        $this->lens->public_id => [$this->lensB->public_id],
    ]]]);

    expect($cart->lines->first()->unitPrice->value)->toBe(4500)
        ->and($cart->lines->first()->subTotal->value)->toBe(9000);
});

test('a fixed-priced bundle never enters the stage', function () {
    $variant = Catalogue::variant($this->currency, 9999);
    $bundle = app(DefinesBundle::class)->execute($variant, BundlePricing::Fixed);
    $bundle->syncGroups([['name' => ['en' => 'Lens'], 'min_selections' => 1, 'max_selections' => 1]]);
    $group = $bundle->groups->first();
    $bundle->syncComponents([
        ['variant' => Catalogue::variant($this->currency, 1), 'quantity' => 1, 'group' => $group, 'default' => true],
        ['variant' => Catalogue::variant($this->currency, 2), 'quantity' => 1, 'group' => $group],
    ]);
    $other = $bundle->components->last();

    $cart = $this->cart->add($variant, 1, ['bundle' => ['selections' => [$group->public_id => [$other->public_id]]]]);

    expect($cart->lines->first()->unitPrice->value)->toBe(9999);
});
