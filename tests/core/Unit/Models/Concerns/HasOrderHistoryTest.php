<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

test('Product::hasOrderHistory is false when no variants have been ordered', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    expect($product->hasOrderHistory())->toBeFalse();
});

test('Product::hasOrderHistory is true once any variant appears on an order line', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    OrderLine::factory()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
    ]);

    expect($product->hasOrderHistory())->toBeTrue();
});

test('Product::hasOrderHistory ignores order lines for other products', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $otherVariant = ProductVariant::factory()->create();
    OrderLine::factory()->create([
        'purchasable_type' => $otherVariant->getMorphClass(),
        'purchasable_id' => $otherVariant->id,
    ]);

    expect($product->hasOrderHistory())->toBeFalse();
});

test('ProductVariant::hasOrderHistory is scoped to the one variant', function () {
    $product = Product::factory()->create();
    $ordered = ProductVariant::factory()->create(['product_id' => $product->id]);
    $sibling = ProductVariant::factory()->create(['product_id' => $product->id]);

    OrderLine::factory()->create([
        'purchasable_type' => $ordered->getMorphClass(),
        'purchasable_id' => $ordered->id,
    ]);

    expect($ordered->hasOrderHistory())->toBeTrue()
        ->and($sibling->hasOrderHistory())->toBeFalse();
});

test('Channel::hasOrderHistory is false when no orders reference the channel', function () {
    $channel = Channel::factory()->create();

    expect($channel->hasOrderHistory())->toBeFalse();
});

test('Channel::hasOrderHistory is true once an order references it', function () {
    $channel = Channel::factory()->create();
    Order::factory()->create(['channel_id' => $channel->id]);

    expect($channel->hasOrderHistory())->toBeTrue();
});
