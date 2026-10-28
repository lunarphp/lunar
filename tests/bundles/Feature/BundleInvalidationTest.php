<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Bundles\Contracts\Actions\DefinesBundle;
use Lunar\Bundles\Enums\BundlePricing;
use Lunar\Bundles\Models\Bundle;
use Lunar\Core\Contracts\CacheInvalidator;
use Lunar\Core\Enums\CacheInvalidationReason;
use Lunar\Core\Events\Catalog\ProductInvalidated;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Bundles\Support\Catalogue;
use Lunar\Tests\Bundles\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->currency = Catalogue::store();
});

test('a component product invalidation reprices every components bundle that includes it', function () {
    $body = Catalogue::variant($this->currency, 3000);
    $bag = Catalogue::variant($this->currency, 500);

    $bundle = app(DefinesBundle::class)->execute(ProductVariant::factory()->create(), BundlePricing::Components);
    $bundle->syncComponents([
        ['variant' => $body, 'quantity' => 1],
        ['variant' => $bag, 'quantity' => 1],
    ]);

    // A price change the price observer cannot see: written without model events.
    Price::withoutEvents(fn () => Price::query()
        ->where('priceable_type', $body->getMorphClass())
        ->where('priceable_id', $body->id)
        ->update(['price' => 2000]));

    event(new ProductInvalidated($body->product, CacheInvalidationReason::Updated));

    expect(Price::query()
        ->where('priceable_type', ProductVariant::morphName())
        ->where('priceable_id', $bundle->product_variant_id)
        ->value('price'))->toBe(2500);
});

test('a component product invalidation fans out to the bundles that include it', function () {
    $body = Catalogue::variant($this->currency, 3000);
    $other = Catalogue::variant($this->currency, 100);

    $bundle = app(DefinesBundle::class)->execute(ProductVariant::factory()->create(), BundlePricing::Fixed);
    $bundle->syncComponents([['variant' => $body, 'quantity' => 1]]);

    $recorded = [];

    $this->mock(CacheInvalidator::class, function ($mock) use (&$recorded) {
        $mock->shouldReceive('record')->andReturnUsing(function ($model, $reason) use (&$recorded) {
            $recorded[] = [$model::class, $model->getKey(), $reason];
        });
        $mock->shouldReceive('flush')->andReturnNull();
    });

    event(new ProductInvalidated($body->product, CacheInvalidationReason::Updated));
    event(new ProductInvalidated($other->product, CacheInvalidationReason::Updated));

    $bundles = array_values(array_filter($recorded, fn (array $row) => $row[0] === Bundle::class));

    expect($bundles)->toHaveCount(1)
        ->and($bundles[0][1])->toBe($bundle->id)
        ->and($bundles[0][2])->toBe(CacheInvalidationReason::RelatedChanged);
});
