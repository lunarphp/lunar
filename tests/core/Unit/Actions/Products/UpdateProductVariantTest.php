<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Products\UpdateProductVariant;
use Lunar\Core\Enums\SellingPolicy;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('updates variant fields', function () {
    $variant = ProductVariant::factory()->create();

    app(UpdateProductVariant::class)->execute($variant, [
        'sku' => 'NEW-SKU',
        'enabled' => false,
        'selling_policy' => SellingPolicy::InStock,
        'min_quantity' => 6,
        'shippable' => false,
    ]);

    $variant->refresh();

    expect($variant->sku)->toBe('NEW-SKU')
        ->and($variant->enabled)->toBeFalse()
        ->and($variant->selling_policy)->toBe(SellingPolicy::InStock)
        ->and($variant->min_quantity)->toBe(6)
        ->and($variant->shippable)->toBeFalse();
});
