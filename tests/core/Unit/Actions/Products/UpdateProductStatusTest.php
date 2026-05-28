<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\Core\Actions\Products\UpdateProductStatus;
use Lunar\Core\Events\Products\ProductStatusUpdated;
use Lunar\Core\Exceptions\ProductActionException;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
});

test('updates the product status and fires an event', function () {
    Event::fake();

    $product = Product::factory()->create(['status' => 'draft']);

    app(UpdateProductStatus::class)->execute($product, 'published');

    expect((string) $product->fresh()->status)->toBe('published');

    Event::assertDispatched(ProductStatusUpdated::class);
});

test('does not fire an event when status is unchanged', function () {
    Event::fake();

    $product = Product::factory()->create(['status' => 'published']);

    app(UpdateProductStatus::class)->execute($product, 'published');

    Event::assertNotDispatched(ProductStatusUpdated::class);
});

test('throws on unknown status', function () {
    $product = Product::factory()->create(['status' => 'draft']);

    app(UpdateProductStatus::class)->execute($product, 'not-a-real-status');
})->throws(ProductActionException::class);
