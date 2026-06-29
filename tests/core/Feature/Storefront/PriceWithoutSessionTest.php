<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\DataObjects\PricingResponse;
use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('a purchasable can be priced from a context built without a session', function () {
    $channel = Channel::factory()->create(['default' => true]);
    $currency = Currency::factory()->create(['default' => true, 'exchange_rate' => 1]);
    $group = CustomerGroup::factory()->create(['default' => true]);

    $product = Product::factory()->create(['status' => 'published']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    Price::factory()->create([
        'price' => 100,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'min_quantity' => 1,
    ]);

    $groupPrice = Price::factory()->create([
        'price' => 80,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'min_quantity' => 1,
        'customer_group_id' => $group->id,
    ]);

    // Construct the context by hand — no session, no request, no auth, not
    // even the resolver. This is the seam an API request or queued job uses.
    $context = new StorefrontContext(
        channel: $channel,
        currency: $currency,
        language: null,
        customer: null,
        customerGroups: collect([$group]),
    );

    $pricing = $variant->pricing($context)->get();

    expect($pricing)->toBeInstanceOf(PricingResponse::class);
    expect($pricing->matched->id)->toBe($groupPrice->id);
    expect((int) $pricing->matched->price)->toBe(80);
});
